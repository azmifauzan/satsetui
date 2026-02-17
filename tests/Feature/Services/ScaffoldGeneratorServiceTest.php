<?php

use App\Models\Generation;
use App\Models\GenerationFile;
use App\Models\Project;
use App\Models\User;
use App\Services\ScaffoldGeneratorService;

beforeEach(function () {
    $this->user = User::factory()->create(['credits' => 100]);
    $this->project = Project::create([
        'user_id' => $this->user->id,
        'name' => 'Test Framework Project',
        'blueprint' => ['outputFormat' => 'react'],
        'status' => 'generating',
    ]);
    $this->generation = Generation::create([
        'user_id' => $this->user->id,
        'project_id' => $this->project->id,
        'model_used' => 'gemini-2.5-flash',
        'credits_used' => 6,
        'status' => 'processing',
        'blueprint_json' => ['outputFormat' => 'react'],
        'total_pages' => 3,
        'progress_data' => [],
    ]);
    $this->service = app(ScaffoldGeneratorService::class);
});

test('requiresScaffold returns true for framework formats', function () {
    expect($this->service->requiresScaffold('react'))->toBeTrue();
    expect($this->service->requiresScaffold('vue'))->toBeTrue();
    expect($this->service->requiresScaffold('angular'))->toBeTrue();
    expect($this->service->requiresScaffold('svelte'))->toBeTrue();
});

test('requiresScaffold returns false for non-framework formats', function () {
    expect($this->service->requiresScaffold('html-css'))->toBeFalse();
    expect($this->service->requiresScaffold('custom'))->toBeFalse();
    expect($this->service->requiresScaffold(''))->toBeFalse();
});

test('getPageFileExtension returns correct extension per framework', function () {
    expect($this->service->getPageFileExtension('react', ['language' => 'typescript']))->toBe('tsx');
    expect($this->service->getPageFileExtension('react', ['language' => 'javascript']))->toBe('jsx');
    expect($this->service->getPageFileExtension('vue', ['language' => 'typescript']))->toBe('vue');
    expect($this->service->getPageFileExtension('vue', ['language' => 'javascript']))->toBe('vue');
    expect($this->service->getPageFileExtension('svelte', ['language' => 'typescript']))->toBe('svelte');
    expect($this->service->getPageFileExtension('angular', ['language' => 'typescript']))->toBe('component.ts');
});

test('getPageFilePath returns correct path per framework', function () {
    $config = ['language' => 'typescript'];

    $path = $this->service->getPageFilePath('dashboard', 'react', $config);
    expect($path)->toBe('src/pages/Dashboard.tsx');

    $path = $this->service->getPageFilePath('user-management', 'vue', $config);
    expect($path)->toBe('src/pages/UserManagement.vue');

    $path = $this->service->getPageFilePath('login', 'svelte', $config);
    expect($path)->toBe('src/routes/login/+page.svelte');

    $path = $this->service->getPageFilePath('settings', 'angular', $config);
    expect($path)->toBe('src/app/pages/settings/settings.component.ts');
});

test('generateScaffold creates React scaffold files', function () {
    $frameworkConfig = [
        'language' => 'typescript',
        'styling' => 'tailwind',
        'router' => true,
        'stateManagement' => 'zustand',
        'buildTool' => 'vite',
    ];

    $this->service->generateScaffold(
        $this->generation,
        'react',
        $frameworkConfig,
        ['dashboard', 'login', 'settings'],
        ['mode' => 'light', 'primary' => '#3b82f6'],
        ['navigation' => 'sidebar']
    );

    // Should have created scaffold files
    $scaffoldFiles = GenerationFile::where('generation_id', $this->generation->id)
        ->where('is_scaffold', true)
        ->get();

    expect($scaffoldFiles->count())->toBeGreaterThan(5);

    // Check essential files exist
    $filePaths = $scaffoldFiles->pluck('file_path')->toArray();
    expect($filePaths)->toContain('package.json');
    expect($filePaths)->toContain('vite.config.ts');
    expect($filePaths)->toContain('tsconfig.json');
    expect($filePaths)->toContain('src/main.tsx');
    expect($filePaths)->toContain('src/App.tsx');
    expect($filePaths)->toContain('src/router.tsx');

    // Verify package.json has zustand
    $packageJson = GenerationFile::where('generation_id', $this->generation->id)
        ->where('file_path', 'package.json')
        ->first();
    expect($packageJson)->not->toBeNull();
    expect($packageJson->file_content)->toContain('zustand');
    expect($packageJson->file_content)->toContain('react');
});

test('generateScaffold creates Vue scaffold files', function () {
    $frameworkConfig = [
        'language' => 'typescript',
        'styling' => 'tailwind',
        'router' => true,
        'stateManagement' => 'pinia',
        'buildTool' => 'vite',
    ];

    $this->service->generateScaffold(
        $this->generation,
        'vue',
        $frameworkConfig,
        ['dashboard', 'login'],
        ['mode' => 'light', 'primary' => '#3b82f6'],
        ['navigation' => 'sidebar']
    );

    $scaffoldFiles = GenerationFile::where('generation_id', $this->generation->id)
        ->where('is_scaffold', true)
        ->get();

    $filePaths = $scaffoldFiles->pluck('file_path')->toArray();
    expect($filePaths)->toContain('package.json');
    expect($filePaths)->toContain('vite.config.ts');
    expect($filePaths)->toContain('src/main.ts');
    expect($filePaths)->toContain('src/App.vue');
    expect($filePaths)->toContain('src/router/index.ts');

    // Verify package.json has pinia
    $packageJson = GenerationFile::where('generation_id', $this->generation->id)
        ->where('file_path', 'package.json')
        ->first();
    expect($packageJson->file_content)->toContain('pinia');
    expect($packageJson->file_content)->toContain('vue');
});

test('generateScaffold creates Svelte scaffold files', function () {
    $frameworkConfig = [
        'language' => 'typescript',
        'styling' => 'tailwind',
        'router' => true,
        'stateManagement' => 'svelte-store',
        'buildTool' => 'vite',
    ];

    $this->service->generateScaffold(
        $this->generation,
        'svelte',
        $frameworkConfig,
        ['dashboard'],
        ['mode' => 'dark'],
        ['navigation' => 'topbar']
    );

    $scaffoldFiles = GenerationFile::where('generation_id', $this->generation->id)
        ->where('is_scaffold', true)
        ->get();

    $filePaths = $scaffoldFiles->pluck('file_path')->toArray();
    expect($filePaths)->toContain('package.json');
    expect($filePaths)->toContain('svelte.config.js');

    $packageJson = GenerationFile::where('generation_id', $this->generation->id)
        ->where('file_path', 'package.json')
        ->first();
    expect($packageJson->file_content)->toContain('svelte');
});

test('generateScaffold creates Angular scaffold files', function () {
    $frameworkConfig = [
        'language' => 'typescript',
        'styling' => 'tailwind',
        'router' => true,
        'stateManagement' => 'ngrx',
        'buildTool' => 'vite',
    ];

    $this->service->generateScaffold(
        $this->generation,
        'angular',
        $frameworkConfig,
        ['dashboard', 'login'],
        ['mode' => 'light'],
        ['navigation' => 'sidebar']
    );

    $scaffoldFiles = GenerationFile::where('generation_id', $this->generation->id)
        ->where('is_scaffold', true)
        ->get();

    $filePaths = $scaffoldFiles->pluck('file_path')->toArray();
    expect($filePaths)->toContain('package.json');
    expect($filePaths)->toContain('angular.json');
    expect($filePaths)->toContain('src/main.ts');

    $packageJson = GenerationFile::where('generation_id', $this->generation->id)
        ->where('file_path', 'package.json')
        ->first();
    expect($packageJson->file_content)->toContain('@angular/core');
    expect($packageJson->file_content)->toContain('@ngrx/store');
});

test('scaffold files are marked with correct file types', function () {
    $frameworkConfig = [
        'language' => 'typescript',
        'styling' => 'tailwind',
        'router' => true,
        'stateManagement' => 'none',
        'buildTool' => 'vite',
    ];

    $this->service->generateScaffold(
        $this->generation,
        'react',
        $frameworkConfig,
        ['dashboard'],
        [],
        []
    );

    $jsonFile = GenerationFile::where('generation_id', $this->generation->id)
        ->where('file_path', 'package.json')
        ->first();
    expect($jsonFile->file_type)->toBe('json');

    $tsxFile = GenerationFile::where('generation_id', $this->generation->id)
        ->where('file_path', 'src/main.tsx')
        ->first();
    expect($tsxFile->file_type)->toBe('tsx');
});

test('generateScaffold returns GenerationFile records', function () {
    $frameworkConfig = [
        'language' => 'typescript',
        'styling' => 'tailwind',
        'router' => true,
        'stateManagement' => 'none',
        'buildTool' => 'vite',
    ];

    $records = $this->service->generateScaffold(
        $this->generation,
        'react',
        $frameworkConfig,
        ['dashboard'],
        [],
        []
    );

    expect($records)->toBeArray();
    expect(count($records))->toBeGreaterThan(0);

    foreach ($records as $record) {
        expect($record)->toBeInstanceOf(GenerationFile::class);
        expect($record->generation_id)->toBe($this->generation->id);
        expect($record->is_scaffold)->toBeTrue();
    }
});

test('React scaffold includes bootstrap dependencies when styling is bootstrap', function () {
    $frameworkConfig = [
        'language' => 'typescript',
        'styling' => 'bootstrap',
        'router' => true,
        'stateManagement' => 'none',
        'buildTool' => 'vite',
    ];

    $this->service->generateScaffold(
        $this->generation,
        'react',
        $frameworkConfig,
        ['dashboard'],
        [],
        []
    );

    $packageJson = GenerationFile::where('generation_id', $this->generation->id)
        ->where('file_path', 'package.json')
        ->first();

    expect($packageJson->file_content)->toContain('bootstrap');
    expect($packageJson->file_content)->toContain('@popperjs/core');
});

test('Vue scaffold includes bootstrap dependencies when styling is bootstrap', function () {
    $frameworkConfig = [
        'language' => 'typescript',
        'styling' => 'bootstrap',
        'router' => true,
        'stateManagement' => 'pinia',
        'buildTool' => 'vite',
    ];

    $this->service->generateScaffold(
        $this->generation,
        'vue',
        $frameworkConfig,
        ['dashboard'],
        [],
        []
    );

    $packageJson = GenerationFile::where('generation_id', $this->generation->id)
        ->where('file_path', 'package.json')
        ->first();

    expect($packageJson->file_content)->toContain('bootstrap');
    expect($packageJson->file_content)->toContain('@popperjs/core');
});

test('React scaffold includes styled-components when styling is styled-components', function () {
    $frameworkConfig = [
        'language' => 'typescript',
        'styling' => 'styled-components',
        'router' => true,
        'stateManagement' => 'none',
        'buildTool' => 'vite',
    ];

    $this->service->generateScaffold(
        $this->generation,
        'react',
        $frameworkConfig,
        ['dashboard'],
        [],
        []
    );

    $packageJson = GenerationFile::where('generation_id', $this->generation->id)
        ->where('file_path', 'package.json')
        ->first();

    expect($packageJson->file_content)->toContain('styled-components');
});

test('globals.css includes bootstrap import when styling is bootstrap', function () {
    $frameworkConfig = [
        'language' => 'typescript',
        'styling' => 'bootstrap',
        'router' => true,
        'stateManagement' => 'none',
        'buildTool' => 'vite',
    ];

    $this->service->generateScaffold(
        $this->generation,
        'react',
        $frameworkConfig,
        ['dashboard'],
        ['mode' => 'light', 'primary' => '#3b82f6'],
        []
    );

    $globalsCss = GenerationFile::where('generation_id', $this->generation->id)
        ->where('file_path', 'src/styles/globals.css')
        ->first();

    expect($globalsCss)->not->toBeNull();
    expect($globalsCss->file_content)->toContain('bootstrap/dist/css/bootstrap.min.css');
});
