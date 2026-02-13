<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Generation;
use App\Models\RefinementMessage;
use App\Services\GenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['credits' => 100]);
    
    $this->project = Project::create([
        'user_id' => $this->user->id,
        'name' => 'Test Project',
        'blueprint' => ['pages' => ['login', 'dashboard']],
        'status' => 'completed',
    ]);

    $this->generation = Generation::create([
        'user_id' => $this->user->id,
        'project_id' => $this->project->id,
        'model_used' => 'satset',
        'credits_used' => 5,
        'status' => 'completed',
        'current_page_index' => 2,
        'total_pages' => 2,
        'progress_data' => [
            'login' => [
                'status' => 'completed',
                'content' => '<html><body>Login Page</body></html>',
                'error' => null,
                'processing_time' => 1000,
            ],
            'dashboard' => [
                'status' => 'completed',
                'content' => '<html><body>Dashboard Page</body></html>',
                'error' => null,
                'processing_time' => 1200,
            ],
        ],
        'generated_content' => 'legacy content',
        'processing_time' => 2200,
    ]);
});

test('refinement messages can be stored in database', function () {
    $message = RefinementMessage::create([
        'generation_id' => $this->generation->id,
        'role' => 'user',
        'content' => 'Make the button blue',
        'type' => 'refine',
        'page_name' => 'login',
    ]);

    expect($message)->toBeInstanceOf(RefinementMessage::class);
    expect($message->generation_id)->toBe($this->generation->id);
    expect($message->role)->toBe('user');
    expect($message->content)->toBe('Make the button blue');
});

test('generation has refinement messages relationship', function () {
    $this->generation->refinementMessages()->create([
        'role' => 'user',
        'content' => 'Test message',
        'type' => 'refine',
    ]);

    expect($this->generation->refinementMessages)->toHaveCount(1);
    expect($this->generation->refinementMessages->first()->content)->toBe('Test message');
});

test('refine endpoint requires authentication', function () {
    $response = $this->postJson("/generation/{$this->generation->id}/refine", [
        'prompt' => 'Make it blue',
    ]);

    $response->assertStatus(401);
});

test('refine endpoint validates required fields', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/generation/{$this->generation->id}/refine", []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['prompt']);
});

test('refine endpoint updates specific page in progress_data', function () {
    // Mock the LLM service
    $this->mock(\App\Services\OpenAICompatibleService::class, function ($mock) {
        $mock->shouldReceive('generateTemplate')
            ->once()
            ->andReturn([
                'success' => true,
                'content' => '<html><body>Updated Login Page with blue button</body></html>',
            ]);
    });

    $response = $this->actingAs($this->user)
        ->postJson("/generation/{$this->generation->id}/refine", [
            'prompt' => 'Make the button blue',
            'page_name' => 'login',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'page_name' => 'login',
    ]);

    // Check that the specific page was updated
    $this->generation->refresh();
    $progressData = $this->generation->progress_data;
    
    expect($progressData['login']['content'])->toContain('blue button');
    expect($progressData['dashboard']['content'])->toBe('<html><body>Dashboard Page</body></html>');
});

test('refine endpoint stores messages in database', function () {
    // Mock the LLM service
    $this->mock(\App\Services\OpenAICompatibleService::class, function ($mock) {
        $mock->shouldReceive('generateTemplate')
            ->once()
            ->andReturn([
                'success' => true,
                'content' => '<html><body>Updated</body></html>',
            ]);
    });

    $response = $this->actingAs($this->user)
        ->postJson("/generation/{$this->generation->id}/refine", [
            'prompt' => 'Change color',
            'page_name' => 'login',
        ]);

    $response->assertStatus(200);

    // Check that messages were stored
    $messages = $this->generation->refinementMessages()->get();
    
    expect($messages)->toHaveCount(3); // user message, status, success message
    expect($messages->where('role', 'user')->first()->content)->toBe('Change color');
    expect($messages->where('role', 'system')->where('type', 'status')->first()->content)->toContain('Refining');
    expect($messages->where('role', 'assistant')->first()->content)->toContain('Refinement applied');
});

test('show endpoint loads refinement messages', function () {
    // Create some refinement messages
    $this->generation->refinementMessages()->createMany([
        [
            'role' => 'user',
            'content' => 'Make button blue',
            'type' => 'refine',
            'page_name' => 'login',
        ],
        [
            'role' => 'assistant',
            'content' => 'Applied changes',
            'type' => 'refine',
            'page_name' => 'login',
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->get("/generation/{$this->generation->id}");

    $response->assertStatus(200);
    
    $props = $response->viewData('page')['props'];
    expect($props['generation']['refinement_messages'])->toHaveCount(2);
});

test('refinement fails for non-completed generation', function () {
    $pendingGeneration = Generation::create([
        'user_id' => $this->user->id,
        'project_id' => $this->project->id,
        'model_used' => 'satset',
        'credits_used' => 5,
        'status' => 'generating',
        'current_page_index' => 0,
        'total_pages' => 2,
        'progress_data' => [],
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/generation/{$pendingGeneration->id}/refine", [
            'prompt' => 'Make it blue',
            'page_name' => 'login',
        ]);

    $response->assertStatus(400);
    $response->assertJson([
        'success' => false,
        'error' => 'Can only refine completed generations',
    ]);
});

test('refinement handles non-existent page', function () {
    // Mock the LLM service - should not be called
    $this->mock(\App\Services\OpenAICompatibleService::class, function ($mock) {
        $mock->shouldReceive('generateTemplate')->never();
    });

    $response = $this->actingAs($this->user)
        ->postJson("/generation/{$this->generation->id}/refine", [
            'prompt' => 'Make it blue',
            'page_name' => 'nonexistent',
        ]);

    $response->assertStatus(400);
    $response->assertJson([
        'success' => false,
    ]);
    
    // Check error message was stored
    $errorMsg = $this->generation->refinementMessages()
        ->where('type', 'error')
        ->first();
    
    expect($errorMsg)->not->toBeNull();
    expect($errorMsg->content)->toContain('not found');
});

