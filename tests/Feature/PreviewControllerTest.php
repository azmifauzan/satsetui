<?php

use App\Models\Generation;
use App\Models\GenerationFile;
use App\Models\Project;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['credits' => 100]);
    $this->project = Project::create([
        'user_id' => $this->user->id,
        'name' => 'Preview Test Project',
        'blueprint' => ['outputFormat' => 'html-css'],
        'status' => 'completed',
    ]);
    $this->generation = Generation::create([
        'user_id' => $this->user->id,
        'project_id' => $this->project->id,
        'model_used' => 'gemini-2.5-flash',
        'credits_used' => 6,
        'status' => 'completed',
        'blueprint_json' => ['outputFormat' => 'html-css'],
        'total_pages' => 1,
        'progress_data' => [
            'dashboard' => [
                'status' => 'completed',
                'content' => '<html><body><h1>Dashboard</h1></body></html>',
                'error' => null,
                'processing_time' => 5,
            ],
        ],
    ]);
});

test('unauthenticated user cannot access preview routes', function () {
    $this->postJson("/generation/{$this->generation->id}/preview/setup")
        ->assertUnauthorized();

    $this->getJson("/generation/{$this->generation->id}/preview/status")
        ->assertUnauthorized();

    $this->postJson("/generation/{$this->generation->id}/preview/stop")
        ->assertUnauthorized();
});

test('user cannot access another users generation preview', function () {
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->postJson("/generation/{$this->generation->id}/preview/setup")
        ->assertForbidden();
});

test('preview status returns no session when none exists', function () {
    $this->actingAs($this->user)
        ->getJson("/generation/{$this->generation->id}/preview/status")
        ->assertOk()
        ->assertJson([
            'status' => 'none',
        ]);
});

test('serve static returns HTML content for completed pages', function () {
    $response = $this->actingAs($this->user)
        ->get("/generation/{$this->generation->id}/preview/static/dashboard");

    $response->assertOk();
    $response->assertHeader('content-type', 'text/html; charset=utf-8');
    $response->assertSee('<h1>Dashboard</h1>', false);
});

test('serve static returns 404 for empty generation', function () {
    $emptyGeneration = Generation::create([
        'user_id' => $this->user->id,
        'project_id' => $this->project->id,
        'model_used' => 'gemini-2.5-flash',
        'credits_used' => 6,
        'status' => 'completed',
        'blueprint_json' => ['outputFormat' => 'html-css'],
        'total_pages' => 1,
        'progress_data' => [],
    ]);

    $this->actingAs($this->user)
        ->get("/generation/{$emptyGeneration->id}/preview/static/anything")
        ->assertNotFound();
});

test('file tree returns files grouped by type', function () {
    // Create some generation files
    GenerationFile::create([
        'generation_id' => $this->generation->id,
        'file_path' => 'package.json',
        'file_content' => '{}',
        'file_type' => 'json',
        'is_scaffold' => true,
    ]);
    GenerationFile::create([
        'generation_id' => $this->generation->id,
        'file_path' => 'src/pages/Dashboard.tsx',
        'file_content' => 'export default function Dashboard() {}',
        'file_type' => 'tsx',
        'is_scaffold' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/generation/{$this->generation->id}/files");

    $response->assertOk();
    $response->assertJsonStructure([
        'files' => [
            '*' => ['id', 'path', 'type', 'is_scaffold', 'name', 'directory'],
        ],
        'total',
        'scaffold_count',
        'component_count',
    ]);
    $response->assertJsonCount(2, 'files');
    $response->assertJson([
        'scaffold_count' => 1,
        'component_count' => 1,
    ]);
});

test('file content returns individual file content', function () {
    $file = GenerationFile::create([
        'generation_id' => $this->generation->id,
        'file_path' => 'src/pages/Dashboard.tsx',
        'file_content' => 'export default function Dashboard() { return <div>Hello</div>; }',
        'file_type' => 'tsx',
        'is_scaffold' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/generation/{$this->generation->id}/files/{$file->id}");

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'file' => [
            'path' => 'src/pages/Dashboard.tsx',
            'type' => 'tsx',
            'is_scaffold' => false,
        ],
    ]);
    expect($response->json('file.content'))->toContain('Dashboard');
});

test('file content returns 404 for file from different generation', function () {
    $otherGeneration = Generation::create([
        'user_id' => $this->user->id,
        'project_id' => $this->project->id,
        'model_used' => 'gemini-2.5-flash',
        'credits_used' => 6,
        'status' => 'completed',
        'blueprint_json' => [],
        'total_pages' => 1,
        'progress_data' => [],
    ]);

    $file = GenerationFile::create([
        'generation_id' => $otherGeneration->id,
        'file_path' => 'src/App.tsx',
        'file_content' => 'content',
        'file_type' => 'tsx',
        'is_scaffold' => false,
    ]);

    // Try to access file from wrong generation
    $this->actingAs($this->user)
        ->getJson("/generation/{$this->generation->id}/files/{$file->id}")
        ->assertNotFound();
});

test('preview setup fails for incomplete generation', function () {
    $incompleteGeneration = Generation::create([
        'user_id' => $this->user->id,
        'project_id' => $this->project->id,
        'model_used' => 'gemini-2.5-flash',
        'credits_used' => 6,
        'status' => 'processing',
        'blueprint_json' => ['outputFormat' => 'react'],
        'total_pages' => 3,
        'progress_data' => [],
    ]);

    $this->actingAs($this->user)
        ->postJson("/generation/{$incompleteGeneration->id}/preview/setup")
        ->assertStatus(422);
});
