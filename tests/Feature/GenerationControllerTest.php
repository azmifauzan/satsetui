<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Generation;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'credits' => 10,
    ]);
});

test('mass assignment works for project with user_id', function () {
    // Direct test for mass assignment issue - this is the core fix
    $project = Project::create([
        'user_id' => $this->user->id,
        'name' => 'Mass Assignment Test',
        'blueprint' => ['test' => 'data'],
        'status' => 'generating',
    ]);

    expect($project->user_id)->toBe($this->user->id);
    expect($project->name)->toBe('Mass Assignment Test');
    expect($project->status)->toBe('generating');
    expect($project->blueprint)->toBe(['test' => 'data']);
});

test('project has relationships defined', function () {
    $project = Project::create([
        'user_id' => $this->user->id,
        'name' => 'Test Project',
        'blueprint' => [],
        'status' => 'generating',
    ]);

    expect($project->user)->toBeInstanceOf(User::class);
    expect($project->user->id)->toBe($this->user->id);
});

test('project can have multiple generations', function () {
    $project = Project::create([
        'user_id' => $this->user->id,
        'name' => 'Test Project',
        'blueprint' => [],
        'status' => 'completed',
    ]);

    Generation::create([
        'user_id' => $this->user->id,
        'project_id' => $project->id,
        'model_used' => 'gemini-pro',
        'credits_used' => 1,
        'status' => 'completed',
        'mcp_prompt' => 'test prompt',
        'started_at' => now(),
    ]);

    Generation::create([
        'user_id' => $this->user->id,
        'project_id' => $project->id,
        'model_used' => 'gemini-flash',
        'credits_used' => 0,
        'status' => 'completed',
        'mcp_prompt' => 'test prompt 2',
        'started_at' => now(),
    ]);

    expect($project->generations()->count())->toBe(2);
});

test('project blueprint is cast to array', function () {
    $blueprint = [
        'framework' => 'tailwind',
        'category' => 'dashboard',
        'pages' => ['home', 'about'],
    ];

    $project = Project::create([
        'user_id' => $this->user->id,
        'name' => 'Test',
        'blueprint' => $blueprint,
        'status' => 'generating',
    ]);

    // Reload from database
    $project->refresh();

    expect($project->blueprint)->toBeArray();
    expect($project->blueprint)->toBe($blueprint);
});
