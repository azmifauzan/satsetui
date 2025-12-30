<?php

use App\Models\LlmModel;
use App\Services\OpenAICompatibleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(OpenAICompatibleService::class);
    
    // Seed LLM models
    $this->artisan('db:seed', ['--class' => 'LlmModelSeeder']);
});

test('generate template returns success with valid response', function () {
    Http::fake([
        'ai.sumopod.com/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => '<template>Test Content</template>',
                    ],
                ],
            ],
            'usage' => [
                'prompt_tokens' => 100,
                'completion_tokens' => 500,
                'total_tokens' => 600,
            ],
        ], 200),
    ]);

    $result = $this->service->generateTemplate('Test prompt', 'gemini-2.5-flash');

    expect($result['success'])->toBeTrue();
    expect($result['content'])->toBe('<template>Test Content</template>');
    expect($result['model'])->toBe('gemini-2.5-flash');
    expect($result['usage']['input_tokens'])->toBe(100);
    expect($result['usage']['output_tokens'])->toBe(500);
});

test('generate template handles api error', function () {
    Http::fake([
        'ai.sumopod.com/*' => Http::response(['error' => 'API Error'], 500),
    ]);

    $result = $this->service->generateTemplate('Test prompt', 'gemini-2.5-flash');

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Failed to generate template');
});

test('get available models returns only free models for free users', function () {
    $models = $this->service->getAvailableModels(false);

    expect($models)->toHaveCount(1);
    expect($models[0]['id'])->toBe('gemini-2.5-flash');
    expect($models[0]['is_free'])->toBeTrue();
});

test('get available models returns all models for premium users', function () {
    $models = $this->service->getAvailableModels(true);

    expect(count($models))->toBeGreaterThan(1);
    
    $modelNames = array_column($models, 'id');
    expect($modelNames)->toContain('gemini-2.5-flash');
    expect($modelNames)->toContain('claude-haiku-4-5');
    expect($modelNames)->toContain('claude-sonnet-4-5');
});

test('get model by name returns correct model', function () {
    $model = $this->service->getModel('claude-haiku-4-5');

    expect($model)->toBeInstanceOf(LlmModel::class);
    expect($model->name)->toBe('claude-haiku-4-5');
    expect($model->display_name)->toBe('Claude Haiku 4.5');
});

test('calculate actual credits is accurate', function () {
    $credits = $this->service->calculateActualCredits(
        'claude-haiku-4-5',
        10000, // 10K input tokens
        50000  // 50K output tokens
    );

    expect($credits)->toBe(5.0);
});

test('calculate actual credits for cheap model', function () {
    $credits = $this->service->calculateActualCredits(
        'gemini-2.5-flash',
        10000,
        50000
    );

    expect($credits)->toBeGreaterThanOrEqual(2);
    expect($credits)->toBeLessThanOrEqual(3);
});

