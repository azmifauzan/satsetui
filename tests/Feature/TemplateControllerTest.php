<?php

use App\Models\Generation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['credits' => 10]);

    $this->project = Project::create([
        'user_id' => $this->user->id,
        'name' => 'My Template',
        'blueprint' => [],
        'status' => 'completed',
    ]);

    $this->generation = Generation::create([
        'user_id' => $this->user->id,
        'project_id' => $this->project->id,
        'model_used' => 'gemini-pro',
        'credits_used' => 1,
        'status' => 'completed',
        'mcp_prompt' => 'test prompt',
        'started_at' => now(),
        'completed_at' => now(),
    ]);
});

// ── Index ──────────────────────────────────────────────────────────────────

test('authenticated user can view templates list', function () {
    $response = $this->actingAs($this->user)->get('/templates');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Templates/Index'));
});

test('guest is redirected from templates list', function () {
    $this->get('/templates')->assertRedirect('/login');
});

test('templates list only shows templates owned by the logged-in user', function () {
    $otherUser = User::factory()->create(['credits' => 0]);
    $otherProject = Project::create([
        'user_id' => $otherUser->id,
        'name' => 'Other Template',
        'blueprint' => [],
        'status' => 'completed',
    ]);
    Generation::create([
        'user_id' => $otherUser->id,
        'project_id' => $otherProject->id,
        'model_used' => 'gemini-pro',
        'credits_used' => 1,
        'status' => 'completed',
        'mcp_prompt' => 'test prompt',
        'started_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get('/templates');

    $response->assertInertia(fn ($page) => $page
        ->component('Templates/Index')
        ->where('templates.total', 1)
    );
});

// ── Rename ──────────────────────────────────────────────────────────────────

test('owner can rename a template', function () {
    $response = $this->actingAs($this->user)
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->put("/templates/{$this->generation->id}/rename", ['name' => 'Renamed Template']);

    $response->assertRedirect();
    expect($this->project->fresh()->name)->toBe('Renamed Template');
});

test('rename requires a name', function () {
    $this->actingAs($this->user)
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->put("/templates/{$this->generation->id}/rename", ['name' => ''])
        ->assertSessionHasErrors('name');
});

test('another user cannot rename someone else\'s template', function () {
    $other = User::factory()->create(['credits' => 0]);

    $this->actingAs($other)
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->put("/templates/{$this->generation->id}/rename", ['name' => 'Hacked'])
        ->assertForbidden();

    expect($this->project->fresh()->name)->toBe('My Template');
});

// ── Delete ──────────────────────────────────────────────────────────────────

test('owner can delete a template', function () {
    $this->actingAs($this->user)
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->delete("/templates/{$this->generation->id}")
        ->assertRedirect('/templates');

    expect(Generation::find($this->generation->id))->toBeNull();
    expect(Project::find($this->project->id))->toBeNull();
});

test('another user cannot delete someone else\'s template', function () {
    $other = User::factory()->create(['credits' => 0]);

    $this->actingAs($other)
        ->withoutMiddleware(ValidateCsrfToken::class)
        ->delete("/templates/{$this->generation->id}")
        ->assertForbidden();

    expect(Generation::find($this->generation->id))->not->toBeNull();
});
