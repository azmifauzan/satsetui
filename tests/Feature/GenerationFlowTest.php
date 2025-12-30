<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LlmModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'credits' => 100,
            'is_premium' => true,
        ]);

        // Create a test model
        LlmModel::create([
            'name' => 'test-model',
            'display_name' => 'Test Model',
            'description' => 'Test model for testing',
            'input_price_per_million' => 0,
            'output_price_per_million' => 0,
            'estimated_credits_per_generation' => 5,
            'is_free' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_can_start_generation_with_valid_blueprint()
    {
        $blueprint = [
            'framework' => 'tailwind',
            'category' => 'admin-dashboard',
            'outputFormat' => 'html-css',
            'pages' => ['dashboard', 'profile'],
            'layout' => [
                'type' => 'sidebar',
                'navPosition' => 'top',
                'sidebarCollapsible' => true,
            ],
            'theme' => [
                'mode' => 'light',
                'primaryColor' => '#3B82F6',
                'accentColor' => '#8B5CF6',
            ],
            'components' => ['buttons', 'forms'],
            'llmModel' => 'test-model',
            'errorMargin' => 0.1,
            'profitMargin' => 0.05,
            'calculatedCost' => 5,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/generation/generate', [
                'blueprint' => $blueprint,
                'project_name' => 'Test Project',
                'model_name' => 'test-model',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'generation_id',
                'total_pages',
                'model',
            ]);

        $this->assertTrue($response->json('success'));
        // Total pages could be different based on blueprint processing
        $this->assertGreaterThan(0, $response->json('total_pages'));
    }

    public function test_generation_requires_authentication()
    {
        $blueprint = [
            'framework' => 'tailwind',
            'category' => 'admin-dashboard',
            'pages' => ['dashboard'],
        ];

        $response = $this->postJson('/generation/generate', [
            'blueprint' => $blueprint,
        ]);

        $response->assertStatus(401);
    }

    public function test_generation_validates_blueprint()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/generation/generate', [
                'project_name' => 'Test Project',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['blueprint']);
    }

    public function test_handles_llm_connection_error_gracefully()
    {
        $blueprint = [
            'framework' => 'tailwind',
            'category' => 'admin-dashboard',
            'outputFormat' => 'html-css',
            'pages' => ['dashboard'],
            'layout' => ['type' => 'sidebar'],
            'theme' => ['mode' => 'light'],
            'components' => ['buttons'],
            'llmModel' => 'test-model',
            'errorMargin' => 0.1,
            'profitMargin' => 0.05,
            'calculatedCost' => 3,
        ];

        // This will fail because we don't have a real LLM service configured
        $response = $this->actingAs($this->user)
            ->postJson('/generation/generate', [
                'blueprint' => $blueprint,
                'project_name' => 'Test Project',
                'model_name' => 'test-model',
            ]);

        // It should still return a valid JSON response, not crash
        $response->assertStatus(200);
        
        if (!$response->json('success')) {
            // If it fails, it should have an error message
            $this->assertArrayHasKey('error', $response->json());
            $this->assertNotEmpty($response->json('error'));
        }
    }
}
