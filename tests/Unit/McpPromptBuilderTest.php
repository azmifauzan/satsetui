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
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint();

    $result = $builder->buildFromBlueprint($blueprint);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(100);
    expect($result)->toContain('Vue.js');
    expect($result)->toContain('Tailwind');
});

test('buildFromBlueprint handles bootstrap framework', function () {
    $builder = new McpPromptBuilder;
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
    $builder = new McpPromptBuilder;
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
    $builder = new McpPromptBuilder;
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
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'pages' => ['login', 'dashboard'],
        'customPages' => [
            ['name' => 'inventory', 'description' => 'Inventory management page'],
        ],
        'components' => ['buttons', 'forms', 'charts'],
        'customComponents' => [
            ['name' => 'kanban-board', 'description' => 'Kanban board component'],
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
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'pages' => ['login'],
        'components' => ['buttons', 'forms'],
    ]);

    $allPages = $builder->getPageList($blueprint);

    // Should have: 1 regular page + 2 component showcase pages = 3 total
    expect(count($allPages))->toBe(3);
});

test('buildFromBlueprint reflects different page counts including component showcase pages', function () {
    $builder = new McpPromptBuilder;

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

test('buildForPage includes project info section when company name is provided', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'projectInfo' => [
            'companyName' => 'PT Mencari Cinta',
            'companyDescription' => 'Perusahaan Teknologi Terkemuka',
        ],
    ]);

    $result = $builder->buildForPage($blueprint, 'dashboard', 0);

    expect($result)->toContain('PROJECT INFORMATION');
    expect($result)->toContain('PT Mencari Cinta');
    expect($result)->toContain('Perusahaan Teknologi Terkemuka');
    expect($result)->toContain('CONSISTENCY RULES');
    expect($result)->toContain('Use the EXACT names provided above');
});

test('buildForPage includes app name for dashboard category', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'category' => 'dashboard',
        'projectInfo' => [
            'appName' => 'AdminPro Dashboard',
        ],
    ]);

    $result = $builder->buildForPage($blueprint, 'dashboard', 0);

    expect($result)->toContain('PROJECT INFORMATION');
    expect($result)->toContain('Application Name: AdminPro Dashboard');
    expect($result)->toContain('dashboard headers');
});

test('buildForPage includes store info for e-commerce category', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'category' => 'e-commerce',
        'projectInfo' => [
            'storeName' => 'TechStore Indonesia',
            'storeDescription' => 'Your trusted technology partner',
        ],
    ]);

    $result = $builder->buildForPage($blueprint, 'home', 0);

    expect($result)->toContain('PROJECT INFORMATION');
    expect($result)->toContain('Store Name: TechStore Indonesia');
    expect($result)->toContain('Your trusted technology partner');
    expect($result)->toContain('store branding');
});

test('buildForPage skips project info section when empty', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'projectInfo' => [],
    ]);

    $result = $builder->buildForPage($blueprint, 'dashboard', 0);

    // Should not contain the section header if no info provided
    expect($result)->not->toContain('PROJECT INFORMATION (USE CONSISTENTLY');
});

test('buildForPage emphasizes navigation consistency', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'layout' => [
            'navigation' => 'topbar',
            'breadcrumbs' => true,
            'footer' => 'minimal',
        ],
    ]);

    $result = $builder->buildForPage($blueprint, 'dashboard', 0);

    expect($result)->toContain('CRITICAL: You MUST use the \'topbar\' navigation pattern');
    expect($result)->toContain('DO NOT use a different navigation pattern');
    expect($result)->toContain('Use top bar navigation on ALL pages, not sidebar');
});

test('buildForPage emphasizes sidebar consistency when sidebar is selected', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'layout' => [
            'navigation' => 'sidebar',
            'breadcrumbs' => true,
            'footer' => 'minimal',
            'sidebarDefaultState' => 'expanded',
        ],
    ]);

    $result = $builder->buildForPage($blueprint, 'dashboard', 0);

    expect($result)->toContain('CRITICAL: You MUST use the \'sidebar\' navigation pattern');
    expect($result)->toContain('Use sidebar navigation on ALL pages, not top navigation');
});

test('buildForPage includes footer consistency requirements', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint();

    $result = $builder->buildForPage($blueprint, 'dashboard', 0);

    expect($result)->toContain('Footer Consistency');
    expect($result)->toContain('Footer content MUST be IDENTICAL across all pages');
    expect($result)->toContain('Do NOT create different footers for different pages');
});

test('buildForPage includes menu consistency requirements', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'pages' => ['home', 'about', 'contact'],
    ]);

    $result = $builder->buildForPage($blueprint, 'home', 0);

    expect($result)->toContain('MUST BE IDENTICAL ON ALL PAGES');
    expect($result)->toContain('CRITICAL: Every page MUST have the EXACT SAME menu items');
    expect($result)->toContain('Do NOT add, remove, or reorder menu items between pages');
});

test('projectInfo is deterministic across multiple page generations', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'projectInfo' => [
            'companyName' => 'Consistent Corp',
            'companyDescription' => 'Always the same',
        ],
    ]);

    $resultPage1 = $builder->buildForPage($blueprint, 'home', 0);
    $resultPage2 = $builder->buildForPage($blueprint, 'about', 1);
    $resultPage3 = $builder->buildForPage($blueprint, 'contact', 2);

    // All pages should contain the same company info
    expect($resultPage1)->toContain('Consistent Corp');
    expect($resultPage2)->toContain('Consistent Corp');
    expect($resultPage3)->toContain('Consistent Corp');

    expect($resultPage1)->toContain('Always the same');
    expect($resultPage2)->toContain('Always the same');
    expect($resultPage3)->toContain('Always the same');
});

test('MCP uses frameworkConfig styling for JS framework constraints', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'framework' => 'bootstrap',
        'outputFormat' => 'react',
        'frameworkConfig' => [
            'language' => 'typescript',
            'styling' => 'css-modules',
            'router' => true,
            'stateManagement' => 'none',
            'buildTool' => 'vite',
        ],
    ]);

    $result = $builder->buildForPage($blueprint, 'dashboard', 0);

    // Should reference CSS Modules import pattern, not Bootstrap utilities
    expect($result)->toContain('CSS Modules');
    expect($result)->toContain('import styles from');
});

test('MCP uses frameworkConfig styling for theme section', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'framework' => 'tailwind',
        'outputFormat' => 'react',
        'frameworkConfig' => [
            'language' => 'typescript',
            'styling' => 'styled-components',
            'router' => true,
            'stateManagement' => 'none',
            'buildTool' => 'vite',
        ],
    ]);

    $result = $builder->buildForPage($blueprint, 'dashboard', 0);

    // Theme section should use CSS custom properties guidance (styled-components)
    // instead of Tailwind classes
    expect($result)->toContain('CSS custom properties');
    expect($result)->toContain('prefers-color-scheme');
});

test('MCP uses frameworkConfig styling for UI density section', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'framework' => 'tailwind',
        'outputFormat' => 'react',
        'frameworkConfig' => [
            'language' => 'typescript',
            'styling' => 'bootstrap',
            'router' => true,
            'stateManagement' => 'none',
            'buildTool' => 'vite',
        ],
    ]);

    $result = $builder->buildForPage($blueprint, 'dashboard', 0);

    // UI density should use Bootstrap spacing classes, not Tailwind
    expect($result)->toContain('Bootstrap Spacing');
    expect($result)->toContain('.container');
});

test('MCP uses base framework for HTML+CSS output', function () {
    $builder = new McpPromptBuilder;
    $blueprint = createValidBlueprint([
        'framework' => 'tailwind',
        'outputFormat' => 'html-css',
    ]);

    $result = $builder->buildForPage($blueprint, 'dashboard', 0);

    // Should use Tailwind classes directly
    expect($result)->toContain('Tailwind');
    expect($result)->toContain('dark:');
});
