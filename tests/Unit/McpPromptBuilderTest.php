<?php

use App\Services\McpPromptBuilder;

// Helper function untuk membuat blueprint valid
function createValidBlueprint(array $overrides = []): array
{
    return array_merge([
        'framework' => 'tailwind',
        'category' => 'admin-dashboard',
        'pages' => ['login', 'dashboard'],
        'layout' => [
            'navigation' => 'sidebar',
            'breadcrumbs' => true,
            'footer' => 'minimal',
            'sidebarDefaultState' => 'expanded',
        ],
        'theme' => [
            'primary' => '#3B82F6',
            'secondary' => '#10B981',
            'mode' => 'light',
            'background' => 'solid',
        ],
        'ui' => [
            'density' => 'comfortable',
            'borderRadius' => 'rounded',
        ],
        'components' => ['buttons', 'forms', 'cards', 'alerts'],
        'interaction' => 'moderate',
        'responsiveness' => 'fully-responsive',
        'codeStyle' => 'minimal',
        'outputFormat' => 'vue',
        'llmModel' => 'gemini-flash',
        'modelCredits' => 0,
    ], $overrides);
}

test('buildFromBlueprint returns non-empty string', function () {
    $builder = new McpPromptBuilder();
    $blueprint = createValidBlueprint();
    
    $result = $builder->buildFromBlueprint($blueprint);
    
    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(100);
    expect($result)->toContain('Vue.js');
    expect($result)->toContain('Tailwind');
});

test('buildFromBlueprint handles bootstrap framework', function () {
    $builder = new McpPromptBuilder();
    $blueprint = createValidBlueprint([
        'framework' => 'bootstrap',
        'category' => 'landing-page',
        'pages' => ['login'],
    ]);
    
    $result = $builder->buildFromBlueprint($blueprint);
    
    expect($result)->toContain('Bootstrap');
    expect($result)->toContain('Landing Page');
});

test('buildFromBlueprint is deterministic', function () {
    $builder = new McpPromptBuilder();
    $blueprint = createValidBlueprint([
        'pages' => ['home', 'settings'],
        'theme' => [
            'primary' => '#3B82F6',
            'secondary' => '#10B981',
            'mode' => 'dark',
            'background' => 'gradient',
        ],
        'uiDensity' => 'spacious',
    ]);
    
    // Generate prompt multiple times with same blueprint
    $result1 = $builder->buildFromBlueprint($blueprint);
    $result2 = $builder->buildFromBlueprint($blueprint);
    $result3 = $builder->buildFromBlueprint($blueprint);
    
    // All results should be identical (deterministic)
    expect($result1)->toBe($result2);
    expect($result2)->toBe($result3);
});

test('buildFromBlueprint includes all major sections', function () {
    $builder = new McpPromptBuilder();
    $blueprint = createValidBlueprint();
    
    $result = $builder->buildFromBlueprint($blueprint);
    
    // Should contain all major sections
    expect($result)->toContain('PROJECT CONTEXT');
    expect($result)->toContain('CONSTRAINTS');
    expect($result)->toContain('LAYOUT');
    expect($result)->toContain('THEME');
    expect($result)->toContain('UI DENSITY');
});

test('getPageList includes component showcase pages', function () {
    $builder = new McpPromptBuilder();
    $blueprint = createValidBlueprint([
        'pages' => ['login', 'dashboard'],
        'customPages' => [
            ['name' => 'inventory', 'description' => 'Inventory management page']
        ],
        'components' => ['buttons', 'forms', 'charts'],
        'customComponents' => [
            ['name' => 'kanban-board', 'description' => 'Kanban board component']
        ],
    ]);
    
    $pages = $builder->getPageList($blueprint);
    
    // Should have: 2 regular pages + 1 custom page + 3 component pages + 1 custom component page = 7 total
    expect(count($pages))->toBe(7);
    expect($pages)->toContain('login');
    expect($pages)->toContain('dashboard');
    expect($pages)->toContain('custom:inventory');
    expect($pages)->toContain('component:buttons');
    expect($pages)->toContain('component:forms');
    expect($pages)->toContain('component:charts');
    expect($pages)->toContain('component:custom:kanban-board');
});

test('component showcase pages are included in total page count', function () {
    $builder = new McpPromptBuilder();
    $blueprint = createValidBlueprint([
        'pages' => ['login'],
        'components' => ['buttons', 'forms'],
    ]);
    
    $allPages = $builder->getPageList($blueprint);
    
    // Should have: 1 regular page + 2 component showcase pages = 3 total
    expect(count($allPages))->toBe(3);
});

test('buildFromBlueprint reflects different page counts including component showcase pages', function () {
    $builder = new McpPromptBuilder();
    
    // Single page + 4 component showcase pages = 5 total
    $blueprintSingle = createValidBlueprint([
        'pages' => ['login'],
        'components' => ['buttons', 'forms', 'cards', 'alerts'], // Default components from createValidBlueprint
    ]);
    
    // Multiple pages + 2 component showcase pages = 6 total
    $blueprintMultiple = createValidBlueprint([
        'pages' => ['login', 'dashboard', 'settings', 'profile'],
        'components' => ['buttons', 'forms'], // Only 2 components
    ]);
    
    $resultSingle = $builder->buildFromBlueprint($blueprintSingle);
    $resultMultiple = $builder->buildFromBlueprint($blueprintMultiple);
    
    // Single page (1) + component pages (4) = 5 total
    expect($resultSingle)->toContain('Total Pages: 5');
    // Multiple pages (4) + component pages (2) = 6 total
    expect($resultMultiple)->toContain('Total Pages: 6');
    expect($resultMultiple)->toContain('Dashboard');
    expect($resultMultiple)->toContain('Settings');
});
