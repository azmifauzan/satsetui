<?php

use App\Models\CreditEstimation;
use App\Models\Generation;
use App\Models\LlmModel;
use App\Models\User;
use App\Services\CreditEstimationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new CreditEstimationService();
    
    // Create test LLM model
    $this->model = LlmModel::create([
        'name' => 'gemini/gemini-2.5-flash-lite',
        'display_name' => 'Gemini 2.5 Flash Lite',
        'description' => 'Fast model',
        'input_price_per_million' => 0.10,
        'output_price_per_million' => 0.40,
        'estimated_credits_per_generation' => 1,
        'is_free' => false,
        'is_active' => true,
        'sort_order' => 1,
        'context_length' => 8192,
    ]);
});

test('returns model default when no historical data exists', function () {
    $result = $this->service->estimateCredits(
        $this->model->name,
        'admin-dashboard',
        'tailwind',
        5,
        3
    );
    
    expect($result)->toHaveKeys(['base_credits', 'confidence', 'sample_count', 'source']);
    expect($result['base_credits'])->toBe(1.0);
    expect($result['confidence'])->toBe('low');
    expect($result['sample_count'])->toBe(0);
    expect($result['source'])->toBe('model_default');
});

test('uses historical average when sufficient samples exist', function () {
    $user = User::factory()->create();
    
    // Create 10 successful generations with actual credits
    for ($i = 0; $i < 10; $i++) {
        $generation = Generation::create([
            'user_id' => $user->id,
            'name' => "Test Generation $i",
            'category' => 'admin-dashboard',
            'framework' => 'tailwind',
            'output_format' => 'vue',
            'model_used' => $this->model->name,
            'status' => 'completed',
            'total_pages' => 5,
            'credits_used' => 1,
            'base_credits' => 1,
            'extra_page_credits' => 0,
            'extra_component_credits' => 0,
            'subtotal_credits' => 1,
            'error_margin' => 0.10,
            'profit_margin' => 0.05,
        ]);
        
        CreditEstimation::create([
            'generation_id' => $generation->id,
            'model_id' => $this->model->name,
            'page_type' => 'predefined',
            'page_name' => 'dashboard',
            'category' => 'admin-dashboard',
            'framework' => 'tailwind',
            'output_format' => 'vue',
            'page_count' => 5,
            'component_count' => 3,
            'estimated_credits' => 1.0,
            'actual_credits' => 0.8, // Historical data shows it's cheaper
            'error_percentage' => -20,
            'was_successful' => true,
        ]);
    }
    
    // Clear cache before test
    $this->service->clearCache($this->model->name);
    
    $result = $this->service->estimateCredits(
        $this->model->name,
        'admin-dashboard',
        'tailwind',
        5,
        3
    );
    
    expect($result['base_credits'])->toBe(0.8);
    expect($result['confidence'])->toBe('medium-low');
    expect($result['sample_count'])->toBe(10);
    expect($result['source'])->toBe('historical_data');
});

test('uses category level average when specific combo not available', function () {
    $user = User::factory()->create();
    
    // Create 8 successful generations with same category but different framework
    for ($i = 0; $i < 8; $i++) {
        $generation = Generation::create([
            'user_id' => $user->id,
            'name' => "Test Generation $i",
            'category' => 'admin-dashboard',
            'framework' => 'bootstrap', // Different framework
            'output_format' => 'vue',
            'model_used' => $this->model->name,
            'status' => 'completed',
            'total_pages' => 5,
            'credits_used' => 1,
            'base_credits' => 1,
            'extra_page_credits' => 0,
            'extra_component_credits' => 0,
            'subtotal_credits' => 1,
            'error_margin' => 0.10,
            'profit_margin' => 0.05,
        ]);
        
        CreditEstimation::create([
            'generation_id' => $generation->id,
            'model_id' => $this->model->name,
            'page_type' => 'predefined',
            'page_name' => 'dashboard',
            'category' => 'admin-dashboard',
            'framework' => 'bootstrap',
            'output_format' => 'vue',
            'page_count' => 5,
            'component_count' => 3,
            'estimated_credits' => 1.0,
            'actual_credits' => 0.9,
            'error_percentage' => -10,
            'was_successful' => true,
        ]);
    }
    
    $this->service->clearCache($this->model->name);
    
    // Request with tailwind (not in historical data)
    $result = $this->service->estimateCredits(
        $this->model->name,
        'admin-dashboard',
        'tailwind', // Different from historical data
        5,
        3
    );
    
    expect($result['base_credits'])->toBe(0.9);
    expect($result['confidence'])->toBe('medium-low');
    expect($result['sample_count'])->toBe(8);
    expect($result['source'])->toBe('historical_data_category');
});

test('uses model level average when category not available', function () {
    $user = User::factory()->create();
    
    // Create 15 successful generations with different categories
    for ($i = 0; $i < 15; $i++) {
        $generation = Generation::create([
            'user_id' => $user->id,
            'name' => "Test Generation $i",
            'category' => 'landing-page', // Different category
            'framework' => 'tailwind',
            'output_format' => 'vue',
            'model_used' => $this->model->name,
            'status' => 'completed',
            'total_pages' => 3,
            'credits_used' => 1,
            'base_credits' => 1,
            'extra_page_credits' => 0,
            'extra_component_credits' => 0,
            'subtotal_credits' => 1,
            'error_margin' => 0.10,
            'profit_margin' => 0.05,
        ]);
        
        CreditEstimation::create([
            'generation_id' => $generation->id,
            'model_id' => $this->model->name,
            'page_type' => 'predefined',
            'page_name' => 'hero',
            'category' => 'landing-page',
            'framework' => 'tailwind',
            'output_format' => 'vue',
            'page_count' => 3,
            'component_count' => 2,
            'estimated_credits' => 1.0,
            'actual_credits' => 0.7,
            'error_percentage' => -30,
            'was_successful' => true,
        ]);
    }
    
    $this->service->clearCache($this->model->name);
    
    // Request with admin-dashboard (not in historical data)
    $result = $this->service->estimateCredits(
        $this->model->name,
        'admin-dashboard', // Different from historical data
        'tailwind',
        5,
        3
    );
    
    expect($result['base_credits'])->toBe(0.7);
    expect($result['confidence'])->toBe('medium'); // 15 samples = medium
    expect($result['sample_count'])->toBe(15);
    expect($result['source'])->toBe('historical_data_model');
});

test('confidence increases with more samples', function () {
    $user = User::factory()->create();
    
    // Test with 5 samples (minimum)
    for ($i = 0; $i < 5; $i++) {
        $generation = Generation::create([
            'user_id' => $user->id,
            'name' => "Test $i",
            'category' => 'admin-dashboard',
            'framework' => 'tailwind',
            'output_format' => 'vue',
            'model_used' => $this->model->name,
            'status' => 'completed',
            'total_pages' => 5,
            'credits_used' => 1,
            'base_credits' => 1,
        ]);
        
        CreditEstimation::create([
            'generation_id' => $generation->id,
            'model_id' => $this->model->name,
            'page_type' => 'predefined',
            'category' => 'admin-dashboard',
            'framework' => 'tailwind',
            'output_format' => 'vue',
            'page_count' => 5,
            'component_count' => 3,
            'estimated_credits' => 1.0,
            'actual_credits' => 0.8,
            'was_successful' => true,
        ]);
    }
    
    $this->service->clearCache($this->model->name);
    $result = $this->service->estimateCredits($this->model->name, 'admin-dashboard', 'tailwind');
    expect($result['confidence'])->toBe('medium-low'); // 5 samples
    expect($result['sample_count'])->toBe(5);
    
    // Add more samples to reach medium confidence (total 15+)
    for ($i = 5; $i < 16; $i++) { // 5 to 15 inclusive = 11 more, total 16
        $generation = Generation::create([
            'user_id' => $user->id,
            'name' => "Test $i",
            'category' => 'admin-dashboard',
            'framework' => 'tailwind',
            'output_format' => 'vue',
            'model_used' => $this->model->name,
            'status' => 'completed',
            'total_pages' => 5,
            'credits_used' => 1,
            'base_credits' => 1,
        ]);
        
        CreditEstimation::create([
            'generation_id' => $generation->id,
            'model_id' => $this->model->name,
            'page_type' => 'predefined',
            'category' => 'admin-dashboard',
            'framework' => 'tailwind',
            'output_format' => 'vue',
            'page_count' => 5,
            'component_count' => 3,
            'estimated_credits' => 1.0,
            'actual_credits' => 0.8,
            'was_successful' => true,
        ]);
    }
    
    $this->service->clearCache($this->model->name);
    $result = $this->service->estimateCredits($this->model->name, 'admin-dashboard', 'tailwind');
    // With 16 samples, should be medium confidence (>= 15)
    expect($result['sample_count'])->toBeGreaterThanOrEqual(15);
    expect($result['confidence'])->toBeIn(['medium', 'high']);
});

test('only uses successful generations for estimation', function () {
    $user = User::factory()->create();
    
    // Create 5 failed generations
    for ($i = 0; $i < 5; $i++) {
        $generation = Generation::create([
            'user_id' => $user->id,
            'name' => "Failed $i",
            'category' => 'admin-dashboard',
            'framework' => 'tailwind',
            'output_format' => 'vue',
            'model_used' => $this->model->name,
            'status' => 'failed',
            'total_pages' => 5,
            'credits_used' => 0,
            'base_credits' => 1,
        ]);
        
        CreditEstimation::create([
            'generation_id' => $generation->id,
            'model_id' => $this->model->name,
            'page_type' => 'predefined',
            'category' => 'admin-dashboard',
            'framework' => 'tailwind',
            'output_format' => 'vue',
            'page_count' => 5,
            'component_count' => 3,
            'estimated_credits' => 1.0,
            'actual_credits' => null, // Failed, no actual credits
            'was_successful' => false,
        ]);
    }
    
    $this->service->clearCache($this->model->name);
    
    // Should fall back to model default since no successful generations
    $result = $this->service->estimateCredits($this->model->name, 'admin-dashboard', 'tailwind');
    
    expect($result['source'])->toBe('model_default');
    expect($result['base_credits'])->toBe(1.0);
});
