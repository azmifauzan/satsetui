<?php

use App\Services\McpPromptBuilder;

// Helper function untuk membuat blueprint valid
function createValidBlueprint(array $overrides = []): array
{
    return array_merge([
        'framework' => 'tailwind',
        'category' => 'dashboard',
        'pages' => ['home', 'about'],
        'layout' => [
            'navigation' => 'sidebar',
            'breadcrumbs' => true,
            'footer' => 'full',
            'sidebarDefaultState' => 'expanded',
        ],
        'theme' => [
            'primary' => '#3B82F6',
            'secondary' => '#10B981',
            'mode' => 'light',
            'background' => 'solid',
        ],
        'uiDensity' => 'comfortable',
        'components' => [
            'navbar' => true,
            'sidebar' => true,
            'footer' => true,
            'breadcrumbs' => true,
        ],
        'interactionLevel' => 'standard',
        'responsiveness' => [
            'approach' => 'mobile-first',
            'breakpoints' => ['mobile', 'tablet', 'desktop'],
        ],
        'codePreferences' => [
            'indentation' => 'spaces',
            'indentSize' => 2,
            'naming' => 'camelCase',
        ],
        'outputFormat' => 'single-file',
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
        'pages' => ['home'],
    ]);
    
    $result = $builder->buildFromBlueprint($blueprint);
    
    expect($result)->toContain('Bootstrap');
    expect($result)->toContain('landing-page');
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

test('buildFromBlueprint reflects different page counts', function () {
    $builder = new McpPromptBuilder();
    
    $blueprintSingle = createValidBlueprint([
        'pages' => ['home'],
    ]);
    
    $blueprintMultiple = createValidBlueprint([
        'pages' => ['home', 'about', 'contact', 'services'],
    ]);
    
    $resultSingle = $builder->buildFromBlueprint($blueprintSingle);
    $resultMultiple = $builder->buildFromBlueprint($blueprintMultiple);
    
    expect($resultSingle)->toContain('Total Pages: 1');
    expect($resultMultiple)->toContain('Total Pages: 4');
    expect($resultMultiple)->toContain('about');
    expect($resultMultiple)->toContain('contact');
});
