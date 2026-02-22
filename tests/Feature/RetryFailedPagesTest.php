<?php

use App\Models\Generation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['credits' => 20]);

    $this->project = Project::create([
        'user_id' => $this->user->id,
        'name' => 'Retry Test Project',
        'blueprint' => ['category' => 'landing-page'],
        'status' => 'failed',
    ]);

    $this->generation = Generation::create([
        'user_id' => $this->user->id,
        'project_id' => $this->project->id,
        'model_used' => 'satset',
        'credits_used' => 5,
        'status' => 'failed',
        'current_page_index' => 2,
        'total_pages' => 3,
        'current_status' => 'failed',
        'error_message' => '503 Service Unavailable',
        'progress_data' => [
            'home' => ['status' => 'completed', 'content' => '<html>home</html>', 'error' => null, 'processing_time' => 1200],
            'about' => ['status' => 'completed', 'content' => '<html>about</html>', 'error' => null, 'processing_time' => 1100],
            'pricing' => ['status' => 'failed', 'content' => null, 'error' => '503 Service Unavailable', 'processing_time' => 0],
        ],
        'started_at' => now()->subMinutes(5),
    ]);
});

test('user can retry failed pages', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/generation/{$this->generation->id}/retry-failed");

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'retry_from_index' => 2,
    ]);

    $this->generation->refresh();

    expect($this->generation->status)->toBe('generating');
    expect($this->generation->current_page_index)->toBe(2);
    expect($this->generation->error_message)->toBeNull();
    expect($this->generation->progress_data['pricing']['status'])->toBe('pending');
    expect($this->generation->progress_data['pricing']['error'])->toBeNull();
    // Already-completed pages stay completed
    expect($this->generation->progress_data['home']['status'])->toBe('completed');
    expect($this->generation->progress_data['about']['status'])->toBe('completed');
});

test('retry resets project status to generating', function () {
    $this->actingAs($this->user)
        ->postJson("/generation/{$this->generation->id}/retry-failed");

    $this->project->refresh();
    expect($this->project->status)->toBe('generating');
});

test('retry returns failed page names', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/generation/{$this->generation->id}/retry-failed");

    $response->assertJson([
        'success' => true,
        'failed_pages' => ['pricing'],
    ]);
});

test('retry returns error when no failed pages exist', function () {
    $this->generation->update([
        'progress_data' => [
            'home' => ['status' => 'completed', 'content' => '<html></html>', 'error' => null, 'processing_time' => 1000],
        ],
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/generation/{$this->generation->id}/retry-failed");

    $response->assertStatus(400);
    $response->assertJson([
        'success' => false,
        'error' => 'No failed pages found to retry.',
    ]);
});

test('unauthenticated user cannot retry generation', function () {
    $response = $this->postJson("/generation/{$this->generation->id}/retry-failed");

    $response->assertStatus(401);
});

test('user cannot retry another users generation', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)
        ->postJson("/generation/{$this->generation->id}/retry-failed");

    $response->assertForbidden();
});

test('retry handles multiple failed pages and resets from earliest', function () {
    $this->generation->update([
        'progress_data' => [
            'home' => ['status' => 'completed', 'content' => '<html></html>', 'error' => null, 'processing_time' => 1000],
            'about' => ['status' => 'failed', 'content' => null, 'error' => '503', 'processing_time' => 0],
            'pricing' => ['status' => 'failed', 'content' => null, 'error' => '429', 'processing_time' => 0],
        ],
        'current_page_index' => 3,
        'total_pages' => 3,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/generation/{$this->generation->id}/retry-failed");

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'retry_from_index' => 1, // 'about' is at index 1 (earliest failed)
    ]);

    $this->generation->refresh();
    expect($this->generation->progress_data['about']['status'])->toBe('pending');
    expect($this->generation->progress_data['pricing']['status'])->toBe('pending');
    expect($this->generation->progress_data['home']['status'])->toBe('completed');
});
