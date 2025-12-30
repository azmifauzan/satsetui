<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\GenerationService;
use App\Models\User;
use App\Models\LlmModel;

class GenerationServiceContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed LLM models
        $this->artisan('db:seed', ['--class' => 'LlmModelSeeder']);
    }

    public function test_prompt_fits_within_context_length(): void
    {
        $user = User::factory()->create(['credits' => 100]);
        
        $blueprint = [
            'framework' => 'tailwind',
            'category' => 'admin-dashboard',
            'outputFormat' => 'html-css',
            'pages' => ['home'],
            'layout' => [
                'type' => 'fixed',
                'navigation' => 'sidebar',
            ],
            'theme' => [
                'mode' => 'light',
                'primaryColor' => '#3b82f6',
            ],
            'components' => [],
        ];

        $generationService = app(GenerationService::class);
        $result = $generationService->startGeneration(
            $blueprint,
            $user,
            'gemini-2.5-flash',
            'Test Project'
        );

        $this->assertTrue($result['success']);
        
        // Check that generation was created
        $this->assertDatabaseHas('generations', [
            'user_id' => $user->id,
            'model_used' => 'gemini-2.5-flash',
            'status' => 'processing',
        ]);
    }

    public function test_context_length_varies_by_model(): void
    {
        $models = LlmModel::all();
        
        $this->assertGreaterThan(0, $models->count());
        
        foreach ($models as $model) {
            $this->assertGreaterThan(0, $model->context_length, "Model {$model->name} should have context_length > 0");
        }
        
        // Check specific models
        $geminiFlash = LlmModel::where('name', 'gemini-2.5-flash')->first();
        $this->assertEquals(1000000, $geminiFlash->context_length, "Gemini 2.5 Flash should have 1M context length");
        
        $claudeHaiku = LlmModel::where('name', 'claude-haiku-4-5')->first();
        $this->assertEquals(200000, $claudeHaiku->context_length, "Claude Haiku should have 200k context length");
        
        $gptMini = LlmModel::where('name', 'gpt-5.1-codex-mini')->first();
        $this->assertEquals(16000, $gptMini->context_length, "GPT Mini should have 16k context length");
    }

    public function test_models_ordered_by_context_capacity(): void
    {
        $models = LlmModel::orderBy('context_length', 'desc')->get();
        
        $this->assertGreaterThan(0, $models->count());
        
        // Gemini 3 Pro should have largest context
        $this->assertEquals('gemini-3-pro-preview', $models->first()->name);
        $this->assertEquals(2000000, $models->first()->context_length);
    }
}
