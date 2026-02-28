<?php

namespace App\Services;

/**
 * MCP Prompt Builder
 *
 * Core service responsible for translating Blueprint JSON into deterministic
 * Model Context Prompts (MCP) for LLM consumption.
 *
 * CRITICAL PRINCIPLES:
 * - This service makes ZERO decisions. It translates only.
 * - Same Blueprint input MUST produce identical MCP output (pure function).
 * - No randomness, no external state, no creative interpretation.
 * - The LLM receives complete instructions with zero ambiguity.
 *
 * PER-PAGE GENERATION:
 * - Each page is generated with its own MCP prompt for better context and error recovery.
 * - Prompts include full project context but focus on single page implementation.
 * - Auto-selected values (responsiveness, interaction, codeStyle) are applied with best defaults.
 *
 * Architecture Role:
 * Wizard UI → Blueprint JSON → [McpPromptBuilder] → MCP String (per page) → LLM
 *
 * @see /docs/architecture.md for complete data flow
 * @see /app/Blueprints/template-blueprint.schema.json for input structure
 */
class McpPromptBuilder
{
    /**
     * Build deterministic MCP prompt for a specific page from validated Blueprint
     *
     * This is the primary method for per-page generation.
     *
     * @param  array  $blueprint  Validated blueprint data matching schema
     * @param  string  $pageName  Name of the page to generate (e.g., 'dashboard', 'custom:inventory')
     * @param  int  $pageIndex  0-based index of current page in generation order
     * @param  array  $sharedLayout  Optional pre-generated layout components (sidebar, topbar, footer, head_styles, scripts)
     * @return string Complete MCP prompt ready for LLM API
     */
    public function buildForPage(array $blueprint, string $pageName, int $pageIndex = 0, array $sharedLayout = []): string
    {
        // Normalize blueprint: sync top-level wizard fields into their nested counterparts
        $blueprint = $this->normalizeBlueprint($blueprint);

        // Extract auto-selected values with defaults
        $autoSelected = $blueprint['autoSelected'] ?? [];
        $blueprint['responsiveness'] = $autoSelected['responsiveness'] ?? 'fully-responsive';
        $blueprint['interaction'] = $autoSelected['interaction'] ?? 'moderate';
        $blueprint['codeStyle'] = $autoSelected['codeStyle'] ?? 'documented';

        // Get page info
        $isCustomPage = str_starts_with($pageName, 'custom:');
        $cleanPageName = $isCustomPage ? substr($pageName, 7) : $pageName;
        $customPageInfo = $this->getCustomPageInfo($blueprint, $cleanPageName);

        // Check if output is a JS framework (multi-file project)
        $isFrameworkOutput = $this->isFrameworkOutput($blueprint['outputFormat'] ?? 'vue');

        // Assemble MCP sections in strict order for single page
        $sections = [
            $this->buildRoleSection($blueprint),
            $this->buildProjectContextSection($blueprint, $pageName, $pageIndex),
            $this->buildProjectInfoSection($blueprint),
        ];

        // Add framework config section for JS framework outputs
        if ($isFrameworkOutput) {
            $sections[] = $this->buildFrameworkConfigSection($blueprint);
        }

        $sections = array_merge($sections, [
            $this->buildConstraintsSection($blueprint),
            $this->buildCurrentPageSection($blueprint, $pageName, $isCustomPage, $customPageInfo),
            $this->buildLayoutSection($blueprint),
            $this->buildThemeSection($blueprint),
            $this->buildUiDensitySection($blueprint),
            $this->buildComponentsSection($blueprint),
            $this->buildInteractionSection($blueprint),
            $this->buildResponsivenessSection($blueprint),
            $this->buildCodeStyleSection($blueprint),
            $this->buildSinglePageOutputFormat($blueprint, $pageName),
            $this->buildSinglePageImplementation($blueprint, $pageName, $isCustomPage, $customPageInfo),
        ]);

        // Inject shared layout components for HTML+CSS output
        if (! empty($sharedLayout) && ! $isFrameworkOutput) {
            $sections[] = $this->buildSharedLayoutInjection($sharedLayout, $pageName);
        }

        // Add custom instructions if provided (from wizard category-specific context)
        $customInstructions = $blueprint['customInstructions'] ?? '';
        if (! empty(trim($customInstructions))) {
            // Strip auto-context markers for cleaner prompt
            $cleanInstructions = preg_replace('/\[AUTO_CONTEXT\](.*?)\[\/AUTO_CONTEXT\]/s', '$1', $customInstructions);
            $cleanInstructions = trim($cleanInstructions);
            if (! empty($cleanInstructions)) {
                $sections[] = "ADDITIONAL CONTEXT & INSTRUCTIONS:\n{$cleanInstructions}";
            }
        }

        // Filter out empty sections
        return implode("\n\n", array_filter($sections, fn ($s) => ! empty(trim($s))));
    }

    /**
     * Build MCP prompt to generate shared layout components (sidebar/navbar/footer)
     *
     * For HTML+CSS output, each page is a standalone HTML file. To ensure
     * visual consistency across pages, we pre-generate the reusable layout
     * HTML (sidebar, navbar, footer) in a single LLM call. This layout HTML
     * is then injected verbatim into each page's MCP prompt.
     *
     * This method is NOT used for JS framework outputs — those use
     * ScaffoldGeneratorService which generates a deterministic MainLayout component.
     *
     * @param  array  $blueprint  Validated blueprint data
     * @return string MCP prompt for layout generation
     */
    public function buildLayoutGenerationPrompt(array $blueprint): string
    {
        // Normalize blueprint: sync top-level wizard fields into their nested counterparts
        $blueprint = $this->normalizeBlueprint($blueprint);

        // Extract auto-selected values with defaults
        $autoSelected = $blueprint['autoSelected'] ?? [];
        $blueprint['responsiveness'] = $autoSelected['responsiveness'] ?? 'fully-responsive';
        $blueprint['interaction'] = $autoSelected['interaction'] ?? 'moderate';
        $blueprint['codeStyle'] = $autoSelected['codeStyle'] ?? 'documented';

        $framework = $blueprint['framework'];
        $frameworkName = $this->getFrameworkName($framework);
        $allPages = $this->getPageList($blueprint);
        $navigation = $blueprint['layout']['navigation'] ?? 'sidebar';
        $footer = ucfirst($blueprint['layout']['footer'] ?? 'simple');
        $theme = $blueprint['theme'] ?? [];
        $ui = $blueprint['ui'] ?? [];

        $sections = [];

        // Role
        $sections[] = "You are an expert frontend developer specializing in {$frameworkName} and modern HTML5/CSS3.\n".
            "Your task is to generate ONLY the reusable layout components (navigation and footer) as HTML snippets.\n".
            'These exact HTML snippets will be embedded into every page to ensure 100% visual consistency.';

        // Project info
        $sections[] = $this->buildProjectInfoSection($blueprint);

        // Theme
        $sections[] = $this->buildThemeSection($blueprint);

        // UI density
        $sections[] = $this->buildUiDensitySection($blueprint);

        // Navigation structure
        $navSection = ['NAVIGATION STRUCTURE:'];
        $navSection[] = 'Navigation Pattern: '.$this->formatNavigationName($navigation);

        if ($navigation === 'sidebar' || $navigation === 'hybrid') {
            $sidebarState = $blueprint['layout']['sidebarDefaultState'] ?? 'expanded';
            $navSection[] = '- Sidebar Default State: '.ucfirst($sidebarState);
            $navSection[] = '- Sidebar Width: 256px expanded, 64px collapsed';
            $navSection[] = '- Collapsible with toggle button';
            $navSection[] = '- Include appropriate icons for each menu item (use inline SVG icons)';
        }
        if ($navigation === 'topbar' || $navigation === 'hybrid') {
            $navSection[] = '- Top Bar: Fixed position, full width';
            $navSection[] = '- Top Bar Height: 64px';
        }

        // Menu items
        $navSection[] = '';
        $navSection[] = 'Menu Items (in this exact order):';
        foreach ($allPages as $pageName) {
            if (str_starts_with($pageName, 'component:')) {
                $cleanName = str_replace(['component:custom:', 'component:'], '', $pageName);
                $displayName = ucwords(str_replace('-', ' ', $cleanName));
                $navSection[] = "  - {$displayName} (UI Components)";
            } elseif (str_starts_with($pageName, 'custom:')) {
                $cleanName = substr($pageName, 7);
                $displayName = ucwords(str_replace('-', ' ', $cleanName));
                $navSection[] = "  - {$displayName}";
            } else {
                $displayName = ucwords(str_replace('-', ' ', $pageName));
                $navSection[] = "  - {$displayName}";
            }
        }

        // Custom navigation items
        $customNavItems = $blueprint['layout']['customNavItems'] ?? [];
        if (! empty($customNavItems)) {
            foreach ($customNavItems as $item) {
                $navSection[] = "  - {$item['label']}";
            }
        }

        $sections[] = implode("\n", $navSection);

        // Footer spec
        $footerSection = ['FOOTER SPECIFICATION:'];
        $footerSection[] = "- Footer Style: {$footer}";
        $footerSection[] = '- Include copyright text with current year';
        if (strtolower($footer) === 'detailed') {
            $footerSection[] = '- Include quick links section';
            $footerSection[] = '- Include social media icon links';
        }
        $sections[] = implode("\n", $footerSection);

        // Framework-specific CSS instructions
        $cssSection = ['CSS FRAMEWORK: '.$frameworkName];
        if ($framework === 'tailwind') {
            $cssSection[] = '- Use Tailwind CSS utility classes for ALL styling';
            $cssSection[] = '- Dark mode support: Include dark: variants for key elements';
        } elseif ($framework === 'bootstrap') {
            $cssSection[] = '- Use Bootstrap utility classes and components';
        } else {
            $cssSection[] = '- Use clean, well-organized custom CSS';
        }
        $sections[] = implode("\n", $cssSection);

        // Responsiveness
        $sections[] = $this->buildResponsivenessSection($blueprint);

        // Output format instructions
        $outputSection = ['OUTPUT FORMAT:'];
        $outputSection[] = 'Generate exactly 3 clearly separated HTML snippets wrapped in markers:';
        $outputSection[] = '';

        if ($navigation === 'sidebar' || $navigation === 'hybrid') {
            $outputSection[] = '<!-- === SIDEBAR_START === -->';
            $outputSection[] = '(Complete sidebar HTML with navigation links, icons, collapse toggle)';
            $outputSection[] = '<!-- === SIDEBAR_END === -->';
            $outputSection[] = '';
        }

        if ($navigation === 'topbar' || $navigation === 'hybrid') {
            $outputSection[] = '<!-- === TOPBAR_START === -->';
            $outputSection[] = '(Complete top navigation bar HTML)';
            $outputSection[] = '<!-- === TOPBAR_END === -->';
            $outputSection[] = '';
        }

        $outputSection[] = '<!-- === FOOTER_START === -->';
        $outputSection[] = '(Complete footer HTML)';
        $outputSection[] = '<!-- === FOOTER_END === -->';
        $outputSection[] = '';
        $outputSection[] = '<!-- === HEAD_STYLES_START === -->';
        $outputSection[] = '(CSS styles for the layout components - navigation, sidebar, footer)';
        $outputSection[] = '(Include responsive styles, transitions, dark mode if applicable)';
        $outputSection[] = '<!-- === HEAD_STYLES_END === -->';
        $outputSection[] = '';
        $outputSection[] = '<!-- === SCRIPTS_START === -->';
        $outputSection[] = '(JavaScript for sidebar toggle, mobile menu, active link highlighting)';
        $outputSection[] = '<!-- === SCRIPTS_END === -->';
        $outputSection[] = '';
        $outputSection[] = 'CRITICAL RULES:';
        $outputSection[] = '- Each navigation link should use href="#pagename" (e.g., href="#dashboard")';
        $outputSection[] = '- Include a data-page attribute on the <body> or main wrapper to identify the active page';
        $outputSection[] = '- The JavaScript MUST highlight the active menu item based on data-page attribute';
        $outputSection[] = '- Return ONLY the HTML snippets above — no explanations, no markdown, no code blocks';
        $outputSection[] = '- Start directly with the first marker comment';
        $outputSection[] = '- Use realistic, professional styling';

        $sections[] = implode("\n", $outputSection);

        // Add custom instructions if provided (blog name, company name, etc. for navigation branding)
        $customInstructions = $blueprint['customInstructions'] ?? '';
        if (! empty(trim($customInstructions))) {
            $cleanInstructions = preg_replace('/\[AUTO_CONTEXT\](.*?)\[\/AUTO_CONTEXT\]/s', '$1', $customInstructions);
            $cleanInstructions = trim($cleanInstructions);
            if (! empty($cleanInstructions)) {
                $sections[] = "ADDITIONAL CONTEXT & INSTRUCTIONS:\n{$cleanInstructions}";
            }
        }

        return implode("\n\n", array_filter($sections, fn ($s) => ! empty(trim($s))));
    }

    /**
     * Parse shared layout components from LLM-generated layout HTML
     *
     * Extracts sidebar, topbar, footer, styles, and scripts from the marked-up HTML output.
     *
     * @param  string  $layoutHtml  Raw LLM output with marker comments
     * @return array Associative array with keys: sidebar, topbar, footer, head_styles, scripts
     */
    public function parseLayoutComponents(string $layoutHtml): array
    {
        $components = [
            'sidebar' => '',
            'topbar' => '',
            'footer' => '',
            'head_styles' => '',
            'scripts' => '',
        ];

        $patterns = [
            'sidebar' => '/<!-- === SIDEBAR_START === -->(.*?)<!-- === SIDEBAR_END === -->/s',
            'topbar' => '/<!-- === TOPBAR_START === -->(.*?)<!-- === TOPBAR_END === -->/s',
            'footer' => '/<!-- === FOOTER_START === -->(.*?)<!-- === FOOTER_END === -->/s',
            'head_styles' => '/<!-- === HEAD_STYLES_START === -->(.*?)<!-- === HEAD_STYLES_END === -->/s',
            'scripts' => '/<!-- === SCRIPTS_START === -->(.*?)<!-- === SCRIPTS_END === -->/s',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $layoutHtml, $match)) {
                $components[$key] = trim($match[1]);
            }
        }

        return $components;
    }

    /**
     * Build the shared layout injection section for page MCP prompts
     *
     * Injects pre-generated layout HTML into the page prompt so the LLM
     * uses the exact same sidebar/navbar/footer on every page.
     *
     * @param  array  $sharedLayout  Parsed layout components from parseLayoutComponents()
     * @param  string  $pageName  Current page name (for active state)
     * @return string Layout injection section for the MCP prompt
     */
    private function buildSharedLayoutInjection(array $sharedLayout, string $pageName): string
    {
        $cleanName = str_starts_with($pageName, 'custom:') ? substr($pageName, 7) : $pageName;
        $cleanName = str_starts_with($cleanName, 'component:custom:') ? substr($cleanName, 17) : $cleanName;
        $cleanName = str_starts_with($cleanName, 'component:') ? substr($cleanName, 10) : $cleanName;

        $section = [];
        $section[] = '=== SHARED LAYOUT COMPONENTS (USE VERBATIM) ===';
        $section[] = '';
        $section[] = '⚠️ CRITICAL: The following HTML snippets are PRE-GENERATED shared layout components.';
        $section[] = 'You MUST copy them EXACTLY into your page output. DO NOT modify, restyle, or recreate them.';
        $section[] = "The only change allowed is marking '{$cleanName}' as the active menu item.";
        $section[] = '';

        if (! empty($sharedLayout['head_styles'])) {
            $section[] = 'LAYOUT CSS (include in <head> inside <style> tag):';
            $section[] = $sharedLayout['head_styles'];
            $section[] = '';
        }

        if (! empty($sharedLayout['sidebar'])) {
            $section[] = 'SIDEBAR HTML (place inside <body>):';
            $section[] = $sharedLayout['sidebar'];
            $section[] = '';
        }

        if (! empty($sharedLayout['topbar'])) {
            $section[] = 'TOPBAR HTML (place inside <body>):';
            $section[] = $sharedLayout['topbar'];
            $section[] = '';
        }

        if (! empty($sharedLayout['footer'])) {
            $section[] = 'FOOTER HTML (place before closing </body>):';
            $section[] = $sharedLayout['footer'];
            $section[] = '';
        }

        if (! empty($sharedLayout['scripts'])) {
            $section[] = 'LAYOUT SCRIPTS (include before closing </body>):';
            $section[] = $sharedLayout['scripts'];
            $section[] = '';
        }

        $section[] = 'PAGE STRUCTURE TEMPLATE:';
        $section[] = 'Your output MUST follow this structure:';
        $section[] = '<!DOCTYPE html>';
        $section[] = '<html lang="en">';
        $section[] = '<head>';
        $section[] = '  (meta tags, title, CDN links)';
        $section[] = '  <style>(LAYOUT CSS from above + page-specific styles)</style>';
        $section[] = '</head>';
        $section[] = "<body data-page=\"{$cleanName}\">";

        if (! empty($sharedLayout['sidebar'])) {
            $section[] = '  (SIDEBAR HTML from above — copied verbatim)';
        }
        if (! empty($sharedLayout['topbar'])) {
            $section[] = '  (TOPBAR HTML from above — copied verbatim)';
        }

        $section[] = '  <main class="main-content">';
        $section[] = '    (PAGE-SPECIFIC CONTENT — this is what YOU generate)';
        $section[] = '  </main>';

        if (! empty($sharedLayout['footer'])) {
            $section[] = '  (FOOTER HTML from above — copied verbatim)';
        }
        if (! empty($sharedLayout['scripts'])) {
            $section[] = '  <script>(LAYOUT SCRIPTS from above + page-specific scripts)</script>';
        }

        $section[] = '</body>';
        $section[] = '</html>';

        return implode("\n", $section);
    }

    /**
     * Build MCP prompt for all pages (legacy method for compatibility)
     *
     * @deprecated Use buildForPage() for per-page generation
     *
     * @param  array  $blueprint  Validated blueprint data
     * @return string Complete MCP prompt for all pages
     */
    public function buildFromBlueprint(array $blueprint): string
    {
        // Normalize blueprint: sync top-level wizard fields into their nested counterparts
        $blueprint = $this->normalizeBlueprint($blueprint);

        // Extract auto-selected values with defaults
        $autoSelected = $blueprint['autoSelected'] ?? [];
        $blueprint['responsiveness'] = $autoSelected['responsiveness'] ?? 'fully-responsive';
        $blueprint['interaction'] = $autoSelected['interaction'] ?? 'moderate';
        $blueprint['codeStyle'] = $autoSelected['codeStyle'] ?? 'documented';

        return implode("\n\n", [
            $this->buildRoleSection($blueprint),
            $this->buildContextSection($blueprint),
            $this->buildConstraintsSection($blueprint),
            $this->buildLayoutSection($blueprint),
            $this->buildThemeSection($blueprint),
            $this->buildUiDensitySection($blueprint),
            $this->buildComponentsSection($blueprint),
            $this->buildInteractionSection($blueprint),
            $this->buildResponsivenessSection($blueprint),
            $this->buildCodeStyleSection($blueprint),
            $this->buildOutputFormatSection($blueprint),
            $this->buildImplementationInstructions($blueprint),
        ]);
    }

    /**
     * Get list of all pages to generate from blueprint
     *
     * @param  array  $blueprint  Blueprint data
     * @return array List of page names (including custom: and component: prefixes)
     */
    public function getPageList(array $blueprint): array
    {
        $pages = $blueprint['pages'] ?? [];
        $customPages = $blueprint['customPages'] ?? [];
        $components = $blueprint['components'] ?? [];
        $customComponents = $blueprint['customComponents'] ?? [];

        // Add custom pages with prefix
        foreach ($customPages as $customPage) {
            $pages[] = 'custom:'.$customPage['name'];
        }

        // Add component showcase pages with prefix
        foreach ($components as $component) {
            $pages[] = 'component:'.$component;
        }

        // Add custom component showcase pages
        foreach ($customComponents as $customComponent) {
            $pages[] = 'component:custom:'.$customComponent['name'];
        }

        return $pages;
    }

    /**
     * Get custom page info from blueprint
     */
    private function getCustomPageInfo(array $blueprint, string $pageName): ?array
    {
        $customPages = $blueprint['customPages'] ?? [];
        foreach ($customPages as $page) {
            if ($page['name'] === $pageName) {
                return $page;
            }
        }

        return null;
    }

    // ========================================================================
    // Section Builders
    // ========================================================================

    /**
     * Normalize blueprint: sync top-level wizard fields into their expected nested positions.
     *
     * The wizard stores some selections both at the top level (e.g. themeMode) and inside
     * nested objects (e.g. theme.mode). The top-level value is the authoritative one because
     * it is what the user actually chose in the wizard UI, so we always reconcile downward.
     *
     * Fields normalized:
     * - themeMode  → theme.mode  (dark / light)
     */
    private function normalizeBlueprint(array $blueprint): array
    {
        // themeMode (top-level wizard field) → theme.mode (what McpPromptBuilder sections read)
        if (isset($blueprint['themeMode']) && isset($blueprint['theme'])) {
            $blueprint['theme']['mode'] = $blueprint['themeMode'];
        }

        return $blueprint;
    }

    /**
     * ROLE: Define LLM expertise and behavior
     *
     * Reasoning: Establishes the LLM's technical persona. The role must match
     * the framework and output format for appropriate expertise.
     */
    private function buildRoleSection(array $blueprint): string
    {
        $framework = $blueprint['framework'];
        $outputFormat = $blueprint['outputFormat'] ?? 'vue';
        $frameworkName = $this->getFrameworkName($framework);
        $frameworkConfig = $blueprint['frameworkConfig'] ?? [];
        $lang = ($frameworkConfig['language'] ?? 'typescript') === 'typescript' ? 'TypeScript' : 'JavaScript';

        // Get output format expertise
        $outputExpertise = match ($outputFormat) {
            'react' => "React 19 with {$lang} and functional components",
            'angular' => "Angular 19 with {$lang} and standalone components",
            'svelte' => "Svelte 5 with {$lang} and runes",
            'html-css' => 'modern HTML5 and CSS3',
            'custom' => $blueprint['customOutputFormat'] ?? 'modern frontend frameworks',
            default => "Vue.js 3 with {$lang} and Composition API",
        };

        $roleText = "You are an expert frontend developer specializing in {$frameworkName} and {$outputExpertise}.\n\n".
               "You build responsive, accessible interfaces with clean, maintainable code.\n".
               "You follow best practices: semantic HTML, WCAG accessibility, performance optimization.\n".
               'You write complete, working code without placeholders or TODOs.';

        // Add framework-specific expertise context
        if ($this->isFrameworkOutput($outputFormat)) {
            $stateManagement = $frameworkConfig['stateManagement'] ?? 'none';
            if ($stateManagement !== 'none') {
                $smName = $this->getStateManagementName($stateManagement);
                $roleText .= "\nYou are proficient with {$smName} for state management.";
            }
        }

        return $roleText;
    }

    /**
     * PROJECT CONTEXT: Full project overview with page list
     *
     * Used in per-page generation to give LLM full context.
     */
    private function buildProjectContextSection(array $blueprint, string $currentPage, int $pageIndex): string
    {
        $category = $this->formatCategoryName($blueprint['category']);
        $allPages = $this->getPageList($blueprint);
        $pagesList = implode(', ', array_map([$this, 'formatPageName'], $allPages));
        $pageCount = count($allPages);
        $currentPageFormatted = $this->formatPageName($currentPage);

        $section = ['PROJECT CONTEXT:'];
        $section[] = "- Template Category: {$category}";
        $section[] = '- Framework: '.$this->getFrameworkName($blueprint['framework']);
        $section[] = '- Output Format: '.$this->getOutputFormatName($blueprint['outputFormat'] ?? 'vue');

        // Add framework config summary for JS framework outputs
        $outputFormat = $blueprint['outputFormat'] ?? 'vue';
        if ($this->isFrameworkOutput($outputFormat)) {
            $frameworkConfig = $blueprint['frameworkConfig'] ?? [];
            $lang = ($frameworkConfig['language'] ?? 'typescript') === 'typescript' ? 'TypeScript' : 'JavaScript';
            $section[] = "- Language: {$lang}";
            $section[] = "- Project Type: Multi-file {$this->getOutputFormatName($outputFormat)} project";
        }

        $section[] = "- All Pages in Project: {$pagesList}";
        $section[] = "- Total Pages: {$pageCount}";
        $section[] = '';
        $section[] = 'CURRENT GENERATION:';
        $section[] = "- Generating Page: {$currentPageFormatted}";
        $section[] = '- Page Index: '.($pageIndex + 1)." of {$pageCount}";

        if ($this->isFrameworkOutput($outputFormat)) {
            $section[] = '';
            $section[] = 'NOTE: This is a multi-file project. Scaffold files (package.json, config, router,';
            $section[] = 'layout) are already generated. You are generating ONLY the page component file.';
        }

        return implode("\n", $section);
    }

    /**
     * PROJECT INFO: Company/project information for consistent branding across all pages
     *
     * This section is CRITICAL for consistency. It ensures that company name, descriptions,
     * and other project-specific information are included in EVERY page generation.
     */
    private function buildProjectInfoSection(array $blueprint): string
    {
        $projectInfo = $blueprint['projectInfo'] ?? [];

        // Skip if no project info provided
        if (empty($projectInfo)) {
            return '';
        }

        $section = ['PROJECT INFORMATION (USE CONSISTENTLY ACROSS ALL PAGES):'];

        // Company/Organization Info
        if (! empty($projectInfo['companyName'])) {
            $section[] = "- Company/Organization Name: {$projectInfo['companyName']}";
            $section[] = '  → Use this exact name in headers, footers, page titles, and branding';
        }

        if (! empty($projectInfo['companyDescription'])) {
            $section[] = "- Company Description: {$projectInfo['companyDescription']}";
            $section[] = '  → Use this for meta descriptions, about sections, and taglines';
        }

        // Application Info
        if (! empty($projectInfo['appName'])) {
            $section[] = "- Application Name: {$projectInfo['appName']}";
            $section[] = '  → Use this in dashboard headers, page titles, and navigation';
        }

        // Store Info (E-commerce)
        if (! empty($projectInfo['storeName'])) {
            $section[] = "- Store Name: {$projectInfo['storeName']}";
            $section[] = '  → Use this in store branding, headers, and footer';
        }

        if (! empty($projectInfo['storeDescription'])) {
            $section[] = "- Store Description: {$projectInfo['storeDescription']}";
            $section[] = '  → Use this for store tagline and about content';
        }

        // Blog Info
        if (! empty($projectInfo['blogName'])) {
            $section[] = "- Blog/Site Name: {$projectInfo['blogName']}";
            $section[] = '  → Use this as the site title in headers, navigation logo/text, page titles, and footer';
        }

        if (! empty($projectInfo['blogTopic'])) {
            $section[] = "- Blog Topic/Niche: {$projectInfo['blogTopic']}";
            $section[] = '  → Use this to generate relevant placeholder content, article titles, categories, and taglines';
            $section[] = '  → All dummy/placeholder content should be related to this topic';
        }

        $section[] = '';
        $section[] = 'IMPORTANT CONSISTENCY RULES:';
        $section[] = '1. Use the EXACT names provided above - do NOT make up different names';
        $section[] = '2. Apply these consistently across ALL pages (navigation, headers, footers, titles)';
        $section[] = '3. Every page must use the same company/project name in its branding';
        $section[] = '4. Footer content must be identical across all pages';
        $section[] = '5. Navigation menu must be identical across all pages (same items, same order)';

        return implode("\n", $section);
    }

    /**
     * CONTEXT: Project overview (legacy, all-pages version)
     */
    private function buildContextSection(array $blueprint): string
    {
        $category = $this->formatCategoryName($blueprint['category']);
        $allPages = $this->getPageList($blueprint);
        $pagesList = implode(', ', array_map([$this, 'formatPageName'], $allPages));
        $pageCount = count($allPages);

        return "PROJECT CONTEXT:\n".
               "- Template Category: {$category}\n".
               "- Target Pages: {$pagesList}\n".
               "- Total Pages: {$pageCount}\n".
               '- Framework: '.$this->getFrameworkName($blueprint['framework'])."\n".
               '- Output Format: '.$this->getOutputFormatName($blueprint['outputFormat'] ?? 'vue');
    }

    /**
     * CONSTRAINTS: Hard technology boundaries
     */
    private function buildConstraintsSection(array $blueprint): string
    {
        $framework = $blueprint['framework'];
        $outputFormat = $blueprint['outputFormat'] ?? 'vue';
        $constraints = ['CONSTRAINTS (MUST FOLLOW):'];

        // Determine effective CSS approach
        // For JS framework outputs, use frameworkConfig.styling (which may differ from the base CSS framework)
        // For HTML+CSS output, use the base CSS framework directly
        $effectiveStyling = $framework;
        if ($this->isFrameworkOutput($outputFormat)) {
            $effectiveStyling = $blueprint['frameworkConfig']['styling'] ?? $framework;
        }

        // CSS/Styling constraints
        if ($effectiveStyling === 'tailwind') {
            $constraints[] = '- Use ONLY Tailwind CSS utility classes for styling';
            $constraints[] = '- No inline styles (style attribute)';
            $constraints[] = '- Responsive breakpoints: sm:640px, md:768px, lg:1024px, xl:1280px, 2xl:1536px';
        } elseif ($effectiveStyling === 'bootstrap') {
            $constraints[] = '- Use ONLY Bootstrap classes for styling';
            $constraints[] = '- No inline styles (style attribute)';
            $constraints[] = '- Responsive breakpoints: xs:<576px, sm:≥576px, md:≥768px, lg:≥992px, xl:≥1200px';
        } elseif ($effectiveStyling === 'css-modules') {
            $constraints[] = '- Use CSS Modules for component-scoped styling';
            $constraints[] = "- Import styles as: import styles from './ComponentName.module.css'";
            $constraints[] = '- Use className={styles.selector} pattern for applying styles';
            $constraints[] = '- Standard responsive breakpoints: mobile (<768px), tablet (768-1024px), desktop (>1024px)';
        } elseif ($effectiveStyling === 'styled-components') {
            $constraints[] = '- Use styled-components for all styling';
            $constraints[] = "- Import: import styled from 'styled-components'";
            $constraints[] = '- Create styled components for each UI element';
            $constraints[] = '- Use theme props and CSS variables for theming';
            $constraints[] = '- Standard responsive breakpoints: mobile (<768px), tablet (768-1024px), desktop (>1024px)';
        } else {
            // pure-css
            $constraints[] = '- Use semantic CSS with BEM naming convention';
            $constraints[] = '- Create CSS variables for theme customization';
            $constraints[] = '- Standard responsive breakpoints: mobile (<768px), tablet (768-1024px), desktop (>1024px)';
        }

        // Charts constraint
        if (in_array('charts', $blueprint['components'] ?? [])) {
            $chartLib = $blueprint['chartLibrary'] ?? 'chartjs';
            $chartName = $chartLib === 'echarts' ? 'Apache ECharts' : 'Chart.js';
            $constraints[] = "- Use ONLY {$chartName} for data visualizations";
            $constraints[] = '- No other chart libraries (including D3.js, Recharts, etc.)';
        }

        // Icon library constraints — external icon packages are NOT pre-installed
        $constraints[] = '- DO NOT import external icon libraries such as @heroicons/vue, lucide-vue-next, @radix-ui/react-icons, react-icons, vue-feather-icons, @phosphor-icons/vue, etc.';
        $constraints[] = '- For icons: copy inline SVG markup directly (e.g. from heroicons.com). Never import from a separate npm icon package.';

        // Output format constraints
        $constraints[] = '- No backend logic (frontend templates only)';
        $constraints[] = '- All imports must be valid (no placeholders)';
        $constraints[] = '- Generate complete, working code without TODO comments';

        return implode("\n", $constraints);
    }

    /**
     * CURRENT PAGE: Specific focus for per-page generation
     */
    private function buildCurrentPageSection(array $blueprint, string $pageName, bool $isCustom, ?array $customPageInfo): string
    {
        $section = ['CURRENT PAGE FOCUS:'];

        // Check if this is a component showcase page
        if (str_starts_with($pageName, 'component:')) {
            $isCustomComponent = str_starts_with($pageName, 'component:custom:');

            if ($isCustomComponent) {
                $componentName = substr($pageName, 17); // Remove 'component:custom:'
                $customComponents = $blueprint['customComponents'] ?? [];
                $componentInfo = null;
                foreach ($customComponents as $comp) {
                    if ($comp['name'] === $componentName) {
                        $componentInfo = $comp;
                        break;
                    }
                }

                $section[] = "- Page Name: {$componentName} (Custom Component Showcase)";
                $section[] = '- Page Type: Component Demo Page';
                $section[] = "- Purpose: Showcase page demonstrating the '{$componentName}' component with various examples and use cases";
                if ($componentInfo) {
                    $section[] = "- Component Description: {$componentInfo['description']}";
                }
                $section[] = '';
                $section[] = 'Requirements for this component showcase page:';
                $section[] = '- Create a dedicated page that showcases this custom component';
                $section[] = '- Include multiple examples/variations of the component';
                $section[] = '- Show different states (default, hover, active, disabled if applicable)';
                $section[] = '- Provide visual examples with descriptions';
                $section[] = '- Use realistic data and scenarios';
            } else {
                $componentName = substr($pageName, 10); // Remove 'component:'
                $displayName = ucwords(str_replace('-', ' ', $componentName));

                $section[] = "- Page Name: {$displayName}";
                $section[] = '- Page Type: Component Showcase Page';
                $section[] = "- Purpose: Demonstrate all variations and states of {$displayName} components (like AdminLTE UI Elements pages)";
                $section[] = '';
                $section[] = $this->getComponentShowcaseRequirements($componentName, $blueprint);
            }
        } elseif ($isCustom) {
            $cleanName = substr($pageName, 7); // Remove 'custom:'
            $formattedName = $this->formatPageName($cleanName);

            $section[] = "- Page Name: {$formattedName}";
            $section[] = '- Page Type: Custom (user-defined)';

            if ($customPageInfo) {
                $section[] = "- Custom Description: {$customPageInfo['description']}";
                $section[] = '';
                $section[] = 'IMPORTANT: This is a custom page defined by the user.';
                $section[] = "Use the description above to determine the page's purpose and content.";
                $section[] = 'Implement appropriate functionality based on the description.';
            }
        } else {
            $formattedName = $this->formatPageName($pageName);
            $section[] = "- Page Name: {$formattedName}";
            $section[] = '- Page Type: Predefined';
        }

        return implode("\n", $section);
    }

    /**
     * LAYOUT: Navigation structure and layout elements
     */
    private function buildLayoutSection(array $blueprint): string
    {
        $layout = $blueprint['layout'];
        $navigation = $layout['navigation'];
        $breadcrumbs = $layout['breadcrumbs'] ? 'Enabled' : 'Disabled';
        $footer = ucfirst($layout['footer']);

        $section = ['LAYOUT REQUIREMENTS:'];
        $section[] = '- Navigation Pattern: '.$this->formatNavigationName($navigation);
        $section[] = '';
        $section[] = "CRITICAL: You MUST use the '{$navigation}' navigation pattern for ALL pages.";
        $section[] = 'DO NOT use a different navigation pattern for different pages.';
        $section[] = '';

        if ($navigation === 'sidebar' || $navigation === 'hybrid') {
            $sidebarState = $layout['sidebarDefaultState'] ?? 'expanded';
            $section[] = '- Sidebar Default State: '.ucfirst($sidebarState);
            $section[] = '- Sidebar Behavior: Collapsible with toggle button';
            $section[] = '- Sidebar Width: 256px expanded, 64px collapsed';
            $section[] = '- IMPORTANT: Use sidebar navigation on ALL pages, not top navigation';

            if ($navigation === 'hybrid') {
                $section[] = '- Top Bar: Fixed position, contains user menu and notifications';
                $section[] = '- Sidebar: Contains primary navigation menu';
            }
        }

        if ($navigation === 'topbar') {
            $section[] = '- Top Bar: Fixed position, full width, contains all navigation';
            $section[] = '- Top Bar Height: 64px';
            $section[] = '- IMPORTANT: Use top bar navigation on ALL pages, not sidebar';
        }

        $section[] = "- Breadcrumbs: {$breadcrumbs}";
        $section[] = "- Footer: {$footer} style";
        $section[] = '';
        $section[] = 'Footer Consistency:';
        $section[] = '- Footer content MUST be IDENTICAL across all pages';
        $section[] = '- Use the same copyright text, links, and layout on every page';
        $section[] = '- Do NOT create different footers for different pages';

        // Include navigation menu structure
        $allPages = $this->getPageList($blueprint);
        $regularPages = [];
        $componentPages = [];

        foreach ($allPages as $pageName) {
            if (str_starts_with($pageName, 'component:')) {
                $componentPages[] = $pageName;
            } elseif (! str_starts_with($pageName, 'custom:')) {
                $regularPages[] = $pageName;
            }
        }

        if (! empty($regularPages) || ! empty($componentPages)) {
            $section[] = '';
            $section[] = 'Navigation Menu Structure (MUST BE IDENTICAL ON ALL PAGES):';
            $section[] = 'The following menu items MUST appear in the same order on every page:';
            $section[] = '';

            if (! empty($regularPages)) {
                $section[] = 'Main Pages Group:';
                foreach ($regularPages as $pageName) {
                    $displayName = $this->formatPageName($pageName);
                    $section[] = "  • {$displayName}";
                }
            }

            if (! empty($componentPages)) {
                $section[] = '';
                $section[] = "UI Components Group (or 'UI Elements'):";
                foreach ($componentPages as $pageName) {
                    $cleanName = str_replace('component:', '', $pageName);
                    $cleanName = str_replace('custom:', '', $cleanName);
                    $displayName = ucwords(str_replace('-', ' ', $cleanName));
                    $section[] = "  • {$displayName}";
                }
            }

            $section[] = '';
            $section[] = '⚠️ CRITICAL: Every page MUST have the EXACT SAME menu items in the SAME ORDER.';
            $section[] = 'Do NOT add, remove, or reorder menu items between pages.';
        }

        // Include custom navigation items
        $customNavItems = $layout['customNavItems'] ?? [];
        if (! empty($customNavItems)) {
            $section[] = "\nCustom Navigation Items:";
            foreach ($customNavItems as $item) {
                $icon = isset($item['icon']) ? " (icon: {$item['icon']})" : '';
                $section[] = "- {$item['label']}: {$item['route']}{$icon}";
            }
        }

        return implode("\n", $section);
    }

    /**
     * THEME: Color scheme and visual mode
     */
    private function buildThemeSection(array $blueprint): string
    {
        $theme = $blueprint['theme'];
        $framework = $blueprint['framework'];
        $outputFormat = $blueprint['outputFormat'] ?? 'vue';

        // For JS framework outputs, use frameworkConfig.styling for CSS-specific instructions
        $effectiveStyling = $framework;
        if ($this->isFrameworkOutput($outputFormat)) {
            $effectiveStyling = $blueprint['frameworkConfig']['styling'] ?? $framework;
        }

        // Include color scheme name if available for better LLM understanding
        $colorSchemeName = $blueprint['colorScheme'] ?? null;
        $colorSchemeNames = [
            'blue' => 'Ocean Blue',
            'green' => 'Forest Green',
            'purple' => 'Royal Purple',
            'red' => 'Ruby Red',
            'amber' => 'Warm Amber',
            'slate' => 'Slate Gray',
        ];
        $colorLabel = $colorSchemeNames[$colorSchemeName] ?? ($colorSchemeName === 'custom' ? 'Custom' : null);

        // Include style preset for design direction
        $stylePreset = $blueprint['stylePreset'] ?? null;
        $stylePresetNames = [
            'modern' => 'Modern & Clean',
            'minimal' => 'Minimalist',
            'bold' => 'Bold & Vibrant',
            'elegant' => 'Elegant',
            'playful' => 'Playful',
        ];
        $styleLabel = $stylePresetNames[$stylePreset] ?? null;

        // Include font family for typography
        $fontFamily = $blueprint['fontFamily'] ?? null;
        $fontFamilyNames = [
            'inter' => 'Inter',
            'poppins' => 'Poppins',
            'roboto' => 'Roboto',
            'playfair' => 'Playfair Display',
            'mono' => 'JetBrains Mono',
        ];
        $fontLabel = null;
        if ($fontFamily) {
            if (str_starts_with($fontFamily, 'custom:')) {
                $fontLabel = substr($fontFamily, 7);
            } else {
                $fontLabel = $fontFamilyNames[$fontFamily] ?? ucfirst($fontFamily);
            }
        }

        $section = ['THEME SPECIFICATION:'];
        if ($colorLabel) {
            $section[] = "- Color Theme: {$colorLabel}";
        }
        $section[] = "- Primary Color: {$theme['primary']} — USE THIS as the dominant accent color for buttons, links, active states, highlights, and key UI elements";
        $section[] = "- Secondary Color: {$theme['secondary']} — USE THIS for secondary accents, hover states, and complementary elements";
        $section[] = '- Color Mode: '.ucfirst($theme['mode']);
        $section[] = '- Background Style: '.ucfirst($theme['background']);

        if ($styleLabel) {
            $section[] = "- Design Style: {$styleLabel}";
        }

        if ($fontLabel) {
            $section[] = "- Font Family: {$fontLabel}";
            $section[] = '  → Import this font (Google Fonts or CDN) and apply as the primary font-family';
        }

        $section[] = '';
        $section[] = 'CRITICAL COLOR REQUIREMENTS:';
        $section[] = "- The primary color ({$theme['primary']}) MUST be prominently visible in the design";
        $section[] = '- Apply primary color to: navigation active states, buttons, links, headings accents, icons, badges';
        $section[] = '- Apply secondary color to: hover states, secondary buttons, subtle accents, gradients';
        $section[] = '- Do NOT use generic blue (#3B82F6) unless it IS the selected primary color';
        $section[] = '- Define CSS variables: --color-primary and --color-secondary with the exact hex values above';

        if ($effectiveStyling === 'tailwind') {
            $section[] = "\nTailwind Implementation:";
            $section[] = '- Define CSS variables in :root and .dark classes';
            $section[] = '- Use custom color classes: bg-primary, text-primary, border-primary';
            $section[] = '- Dark mode: Add dark: prefix for dark mode styles';

            if ($theme['mode'] === 'dark') {
                $section[] = '- Default Mode: Apply .dark class to <html> element';
            }
        } elseif ($effectiveStyling === 'bootstrap') {
            $section[] = "\nBootstrap Implementation:";
            $section[] = '- Override Bootstrap variables: $primary, $secondary';
            $section[] = '- Use Bootstrap color utilities: bg-primary, text-secondary';
            $section[] = '- Dark mode: Use data-bs-theme="dark" attribute';
        } elseif ($effectiveStyling === 'css-modules' || $effectiveStyling === 'styled-components') {
            $section[] = "\nCSS Implementation:";
            $section[] = '- Use CSS custom properties (variables) for theming: var(--color-primary)';
            $section[] = '- Dark mode: Use prefers-color-scheme media query or class-based toggle';
        }

        return implode("\n", $section);
    }

    /**
     * UI DENSITY: Spacing and sizing scale
     */
    private function buildUiDensitySection(array $blueprint): string
    {
        $ui = $blueprint['ui'];
        $density = $ui['density'];
        $borderRadius = $ui['borderRadius'];
        $framework = $blueprint['framework'];
        $outputFormat = $blueprint['outputFormat'] ?? 'vue';

        // For JS framework outputs, use frameworkConfig.styling for CSS-specific instructions
        $effectiveStyling = $framework;
        if ($this->isFrameworkOutput($outputFormat)) {
            $effectiveStyling = $blueprint['frameworkConfig']['styling'] ?? $framework;
        }

        $section = ['UI DENSITY & STYLE:'];
        $section[] = '- Density Level: '.ucfirst($density);
        $section[] = '- Border Radius: '.ucfirst($borderRadius);

        if ($effectiveStyling === 'tailwind') {
            $section[] = "\nTailwind Spacing:";
            $spacing = match ($density) {
                'compact' => [
                    '- Container Padding: p-4',
                    '- Card Padding: p-3',
                    '- Element Spacing: space-y-2, gap-2',
                    '- Font Size: text-sm (body), text-xs (secondary)',
                ],
                'spacious' => [
                    '- Container Padding: p-8',
                    '- Card Padding: p-6',
                    '- Element Spacing: space-y-6, gap-6',
                    '- Font Size: text-lg (body), text-base (secondary)',
                ],
                default => [
                    '- Container Padding: p-6',
                    '- Card Padding: p-4',
                    '- Element Spacing: space-y-4, gap-4',
                    '- Font Size: text-base (body), text-sm (secondary)',
                ],
            };
            $section = array_merge($section, $spacing);

            $section[] = "\nBorder Radius:";
            if ($borderRadius === 'sharp') {
                $section[] = '- Cards: rounded-sm (2px)';
                $section[] = '- Buttons: rounded (4px)';
            } else {
                $section[] = '- Cards: rounded-lg (8px)';
                $section[] = '- Buttons: rounded-md (6px)';
            }
        } elseif ($effectiveStyling === 'bootstrap') {
            $section[] = "\nBootstrap Spacing:";
            $spacing = match ($density) {
                'compact' => [
                    '- Container: .container with p-2',
                    '- Cards: .card with p-2',
                    '- Element Spacing: .mb-1, .gap-1',
                    '- Font Size: .fs-6 (body), .small (secondary)',
                ],
                'spacious' => [
                    '- Container: .container with p-5',
                    '- Cards: .card with p-4',
                    '- Element Spacing: .mb-4, .gap-4',
                    '- Font Size: .fs-5 (body), .fs-6 (secondary)',
                ],
                default => [
                    '- Container: .container with p-3',
                    '- Cards: .card with p-3',
                    '- Element Spacing: .mb-3, .gap-3',
                    '- Font Size: default Bootstrap sizing',
                ],
            };
            $section = array_merge($section, $spacing);

            $section[] = "\nBorder Radius:";
            if ($borderRadius === 'sharp') {
                $section[] = '- Cards: .rounded-1';
                $section[] = '- Buttons: .rounded-1';
            } else {
                $section[] = '- Cards: .rounded-3';
                $section[] = '- Buttons: .rounded-2';
            }
        } elseif ($effectiveStyling === 'css-modules' || $effectiveStyling === 'styled-components') {
            $spacingScale = match ($density) {
                'compact' => '4px base unit',
                'spacious' => '12px base unit',
                default => '8px base unit',
            };
            $section[] = "\nCSS Spacing Scale: {$spacingScale}";
            $section[] = '- Use consistent spacing multipliers (1x, 2x, 3x, 4x)';
            $section[] = '- Define spacing variables: --spacing-sm, --spacing-md, --spacing-lg';

            $section[] = "\nBorder Radius:";
            if ($borderRadius === 'sharp') {
                $section[] = '- Cards: 2px';
                $section[] = '- Buttons: 4px';
            } else {
                $section[] = '- Cards: 8px';
                $section[] = '- Buttons: 6px';
            }
        }

        return implode("\n", $section);
    }

    /**
     * COMPONENTS: Required UI components
     *
     * NOTE: Each selected component will generate a dedicated SHOWCASE PAGE.
     * Similar to AdminLTE, these are demonstration pages showing:
     * - All variations of the component
     * - Different states (hover, active, disabled, etc.)
     * - Code examples and usage patterns
     * - Multiple examples with different configurations
     *
     * Example component pages:
     * - "Buttons" page: Shows all button types, sizes, states
     * - "Forms" page: Shows all form inputs with examples
     * - "Charts" page: Shows different chart types with sample data
     */
    private function buildComponentsSection(array $blueprint): string
    {
        $components = $blueprint['components'] ?? [];
        $customComponents = $blueprint['customComponents'] ?? [];

        $section = ['COMPONENT PAGES:'];
        $section[] = "Each component below will have its own dedicated showcase page (like AdminLTE UI Elements pages).\n";

        // Predefined components
        if (count($components) > 0) {
            $section[] = 'Standard Component Pages:';
            foreach ($components as $component) {
                $displayName = ucwords(str_replace('-', ' ', $component));
                $section[] = "- {$displayName}: Showcase page for {$component}";
            }
        }

        // Custom components
        if (count($customComponents) > 0) {
            $section[] = "\nCustom Component Pages:";
            foreach ($customComponents as $customComponent) {
                $section[] = "- {$customComponent['name']}: {$customComponent['description']}";
            }
        }

        return implode("\n", $section);
    }

    /**
     * Get showcase requirements for a specific component
     */
    private function getComponentShowcaseRequirements(string $component, array $blueprint): string
    {
        $requirements = ['Component Showcase Requirements:'];

        switch ($component) {
            case 'buttons':
                $requirements[] = '- Show all button variants: Primary, Secondary, Success, Danger, Warning, Info';
                $requirements[] = '- Demonstrate all sizes: Small, Default, Large';
                $requirements[] = '- Show all states: Default, Hover (describe hover effect), Active, Disabled';
                $requirements[] = '- Include icon buttons, button groups, outline buttons';
                $requirements[] = '- Add block buttons (full width)';
                $requirements[] = '- Include loading state buttons';
                $requirements[] = '- Organize in sections with clear labels';
                break;

            case 'forms':
                $requirements[] = '- Text inputs: Default, with placeholder, with label, with helper text';
                $requirements[] = '- Input validation states: Success, Error, Warning';
                $requirements[] = '- Input sizes: Small, Default, Large';
                $requirements[] = '- Input types: Text, Email, Password, Number, Tel, URL';
                $requirements[] = '- Textarea: Default, with character counter';
                $requirements[] = '- Select dropdown: Single select, multiple select';
                $requirements[] = '- Checkboxes: Single, multiple, with labels';
                $requirements[] = '- Radio buttons: Group with multiple options';
                $requirements[] = '- File upload input';
                $requirements[] = '- Form layouts: Vertical, horizontal, inline';
                $requirements[] = '- Complete form example with validation';
                break;

            case 'modals':
                $requirements[] = '- Basic modal with header, body, footer';
                $requirements[] = '- Modal sizes: Small, Default, Large, Extra Large';
                $requirements[] = '- Modal variations: Centered, scrollable content';
                $requirements[] = '- Confirm dialog modal';
                $requirements[] = '- Form inside modal example';
                $requirements[] = '- Include buttons to trigger each modal type';
                break;

            case 'dropdowns':
                $requirements[] = '- Basic dropdown menu';
                $requirements[] = '- Dropdown with icons';
                $requirements[] = '- Dropdown with dividers';
                $requirements[] = '- Dropdown alignments: Left, Right';
                $requirements[] = '- Dropdown directions: Down, Up';
                $requirements[] = '- Split button dropdown';
                $requirements[] = '- Dropdown in button group';
                break;

            case 'alerts':
                $requirements[] = '- Alert types: Success, Info, Warning, Error/Danger';
                $requirements[] = '- Dismissible alerts (with close button)';
                $requirements[] = '- Alerts with icons';
                $requirements[] = '- Alerts with action buttons';
                $requirements[] = '- Alert with title and description';
                $requirements[] = '- Toast notifications (if applicable to framework)';
                break;

            case 'cards':
                $requirements[] = '- Basic card with header, body, footer';
                $requirements[] = '- Card with image (top, overlay)';
                $requirements[] = '- Card with list group';
                $requirements[] = '- Card with tabs';
                $requirements[] = '- Horizontal card layout';
                $requirements[] = '- Card with actions (buttons in header/footer)';
                $requirements[] = '- Card colors/variants';
                $requirements[] = '- Card grid layout example (3 cards in row)';
                break;

            case 'tabs':
                $requirements[] = '- Horizontal tabs (default)';
                $requirements[] = '- Tabs with icons';
                $requirements[] = '- Tabs justified (full width)';
                $requirements[] = '- Vertical tabs';
                $requirements[] = '- Pills style tabs';
                $requirements[] = '- Example with real content in each tab panel';
                break;

            case 'charts':
                $chartLib = $blueprint['chartLibrary'] ?? 'chartjs';
                $chartName = $chartLib === 'echarts' ? 'Apache ECharts' : 'Chart.js';
                $requirements[] = "- Use {$chartName} library exclusively";
                $requirements[] = '- Line Chart: Monthly revenue for 12 months with 2 data series';
                $requirements[] = '- Bar Chart: Sales by category (6-8 categories)';
                $requirements[] = '- Pie/Doughnut Chart: Market share (4-5 segments)';
                $requirements[] = '- Area Chart: Website traffic over time';
                $requirements[] = '- Mixed Chart: Combination of line and bar';
                $requirements[] = '- Each chart in its own card with title';
                $requirements[] = '- Use theme colors in charts';
                $requirements[] = '- Responsive charts that adapt to container';
                break;

            case 'tables':
                $requirements[] = '- Basic table with striped rows';
                $requirements[] = '- Table with hover effect';
                $requirements[] = '- Bordered table';
                $requirements[] = '- Compact table (small padding)';
                $requirements[] = '- Table with action buttons in last column';
                $requirements[] = '- Responsive table (horizontal scroll on mobile)';
                $requirements[] = '- Table with search/filter input';
                $requirements[] = '- Sortable table headers (indicate sort direction)';
                $requirements[] = '- Table with pagination (sample data: 20+ rows)';
                $requirements[] = '- Table with row selection (checkboxes)';
                break;

            default:
                $requirements[] = "- Show multiple examples of {$component}";
                $requirements[] = '- Demonstrate different variations and use cases';
                $requirements[] = '- Include different states where applicable';
                $requirements[] = '- Use realistic sample data';
                break;
        }

        $requirements[] = "\nGeneral Showcase Guidelines:";
        $requirements[] = '- Organize examples in sections with descriptive headings';
        $requirements[] = '- Each example should be in its own card or section';
        $requirements[] = '- Add brief descriptions/labels for each variant';
        $requirements[] = '- Use consistent spacing between examples';
        $requirements[] = '- Make it easy to understand and copy-paste patterns';

        return implode("\n", $requirements);
    }

    /**
     * Get requirements for a specific component (legacy - for reference)
     */
    private function getComponentRequirements(string $component, array $blueprint): string
    {
        $requirements = match ($component) {
            'buttons' => "- Buttons: Primary (filled), Secondary (outline), Destructive (red)\n".
                        "  Sizes: Small, Default, Large\n".
                        '  States: Default, Hover, Active, Disabled',
            'forms' => "- Forms: Text Input, Email Input, Password Input, Select, Checkbox, Radio, Textarea\n".
                      "  Include labels, placeholders, validation states\n".
                      '  Accessible: proper for/id associations, aria-labels',
            'modals' => "- Modals: Center-screen overlay with backdrop\n".
                       "  Header with title and close button\n".
                       "  Body content area, Footer with action buttons\n".
                       '  Close on backdrop click and ESC key',
            'dropdowns' => "- Dropdowns: Button-triggered menu\n".
                          "  Support menu items, dividers, icons\n".
                          '  Click outside to close',
            'alerts' => "- Alerts/Toasts: Success, Error, Warning, Info variants\n".
                       "  Dismissible with close button\n".
                       '  Auto-dismiss option (5 seconds)',
            'cards' => "- Cards: Header, Body, Footer sections\n".
                      "  Optional image support\n".
                      '  Flexible content layout',
            'tabs' => "- Tabs: Horizontal tab navigation\n".
                     "  Active state indication\n".
                     '  Content panels switch on click',
            'charts' => $this->getChartRequirements($blueprint),
            default => "- {$component}: Standard implementation",
        };

        return $requirements;
    }

    /**
     * Get chart-specific requirements
     */
    private function getChartRequirements(array $blueprint): string
    {
        $chartLib = $blueprint['chartLibrary'] ?? 'chartjs';
        $chartName = $chartLib === 'echarts' ? 'Apache ECharts' : 'Chart.js';

        return "- Charts: Data visualizations using {$chartName}\n".
               "  Line Chart: Time series data\n".
               "  Bar Chart: Categorical comparisons\n".
               "  Doughnut/Pie Chart: Proportional data\n".
               '  Responsive and theme-aware';
    }

    /**
     * INTERACTION: Animation and transition level (auto-selected)
     */
    private function buildInteractionSection(array $blueprint): string
    {
        $interaction = $blueprint['interaction'] ?? 'moderate';

        $section = ['INTERACTION LEVEL: '.ucfirst($interaction).' (auto-selected)'];

        $details = match ($interaction) {
            'static' => [
                '- No animations or transitions',
                '- Instant state changes',
                '- Minimal hover effects (color change only)',
            ],
            'rich' => [
                '- Rich animations: Fade in, slide, scale transforms',
                '- Micro-interactions: Button press feedback, ripple effects',
                '- Loading skeletons: Pulse animations for loading states',
                '- Page transitions: Smooth navigation between views',
            ],
            default => [
                '- Smooth transitions: 150ms ease-in-out for interactive elements',
                '- Hover effects: Background/text color shifts, opacity changes',
                '- Focus states: Outline/ring on keyboard navigation',
                '- No complex animations or parallax',
            ],
        };

        return implode("\n", array_merge($section, $details));
    }

    /**
     * RESPONSIVENESS: Responsive design approach (auto-selected)
     */
    private function buildResponsivenessSection(array $blueprint): string
    {
        $responsiveness = $blueprint['responsiveness'] ?? 'fully-responsive';

        $section = ['RESPONSIVENESS: '.$this->formatResponsivenessName($responsiveness).' (auto-selected)'];

        $details = match ($responsiveness) {
            'desktop-first' => [
                '- Design optimized for desktop (≥1024px)',
                '- Scale down to tablet and mobile',
                '- Mobile (<640px): Hamburger menu, stacked layout',
            ],
            'mobile-first' => [
                '- Design optimized for mobile (<640px)',
                '- Scale up to tablet and desktop',
                '- Mobile: Bottom navigation or hamburger menu',
            ],
            default => [
                '- Equal optimization for all screen sizes',
                '- Mobile (<640px): Hamburger menu, stacked single column',
                '- Tablet (640-1024px): Collapsible sidebar, 2-column grid',
                '- Desktop (>1024px): Expanded sidebar, 3+ column grid',
                '- Touch-friendly: Minimum 44px tap targets on mobile',
            ],
        };

        return implode("\n", array_merge($section, $details));
    }

    /**
     * CODE STYLE: Verbosity and documentation level (auto-selected)
     */
    private function buildCodeStyleSection(array $blueprint): string
    {
        $codeStyle = $blueprint['codeStyle'] ?? 'documented';

        $section = ['CODE STYLE: '.ucfirst($codeStyle).' (auto-selected)'];

        $details = match ($codeStyle) {
            'minimal' => [
                '- Concise variable names (e.g., user, isOpen)',
                '- No comments unless complex logic',
                '- Inline simple logic, extract only when reused',
            ],
            'verbose' => [
                '- Explicit variable names (e.g., currentUser, isModalOpen)',
                '- Moderate comments for non-obvious logic',
                '- Extract helper functions for clarity',
            ],
            default => [
                '- Highly descriptive variable names',
                '- Comments on all functions, complex logic, and component purposes',
                '- Extract and document all helper functions',
                '- Full JSDoc with property descriptions',
                '- Include usage examples in component comments',
            ],
        };

        return implode("\n", array_merge($section, $details));
    }

    /**
     * OUTPUT FORMAT: Single page file structure
     *
     * For framework outputs, generates component-specific instructions.
     * Scaffold files (package.json, config, layout) are generated separately by ScaffoldGeneratorService.
     */
    private function buildSinglePageOutputFormat(array $blueprint, string $pageName): string
    {
        $isCustom = str_starts_with($pageName, 'custom:');
        $cleanName = $isCustom ? substr($pageName, 7) : $pageName;
        $componentName = $this->getPageComponentName($cleanName);
        $outputFormat = $blueprint['outputFormat'] ?? 'vue';
        $frameworkConfig = $blueprint['frameworkConfig'] ?? [];
        $isTs = ($frameworkConfig['language'] ?? 'typescript') === 'typescript';

        $extension = match ($outputFormat) {
            'react' => $isTs ? 'tsx' : 'jsx',
            'angular' => 'component.ts',
            'svelte' => 'svelte',
            'html-css' => 'html',
            default => 'vue',
        };

        // Determine correct file path based on output format
        $filePath = match ($outputFormat) {
            'react' => "src/pages/{$componentName}.{$extension}",
            'vue' => "src/pages/{$componentName}.{$extension}",
            'svelte' => 'src/routes/'.$this->toKebabCase($cleanName).'/+page.svelte',
            'angular' => 'src/app/pages/'.$this->toKebabCase($cleanName).'/'.$this->toKebabCase($cleanName).'.component.ts',
            default => "src/pages/{$componentName}.{$extension}",
        };

        $section = ['OUTPUT FORMAT:'];
        $section[] = 'Generate a SINGLE file for this page:';
        $section[] = '';
        $section[] = "File: {$filePath}";
        $section[] = '';
        $section[] = 'Requirements:';
        $section[] = "- Start with comment: // {$filePath}";
        $section[] = '- Include all necessary imports';
        $section[] = '- Export component as default';
        $section[] = '- Include any page-specific types/interfaces';
        $section[] = '- Generate complete, working code';

        // Add framework-specific output instructions
        if ($this->isFrameworkOutput($outputFormat)) {
            $section[] = '';
            $section[] = 'FRAMEWORK-SPECIFIC RULES:';
            $section = array_merge($section, $this->getFrameworkOutputRules($outputFormat, $frameworkConfig));
        }

        $section[] = '';
        $section[] = 'CRITICAL OUTPUT RULES:';
        $section[] = '- Return ONLY the code, nothing else';
        $section[] = '- DO NOT include explanations or markdown';
        $section[] = '- DO NOT wrap in ```typescript, ```vue, or any code blocks';
        $section[] = "- DO NOT add 'Here is...' or any introductory text";
        $section[] = '- Start directly with the file comment';
        $section[] = '';
        $section[] = 'DO NOT generate:';
        $section[] = '- Layout/wrapper components (they already exist in the project scaffold)';
        $section[] = '- Shared components (assume they exist: Button, Card, Modal, etc.)';
        $section[] = '- Router configuration (already generated in scaffold)';
        $section[] = '- Type files (only page-specific inline types)';
        $section[] = '- package.json, vite.config, or any config files';

        return implode("\n", $section);
    }

    /**
     * OUTPUT FORMAT: All pages (legacy)
     */
    private function buildOutputFormatSection(array $blueprint): string
    {
        $allPages = $this->getPageList($blueprint);
        $components = $blueprint['components'] ?? [];

        $section = ['OUTPUT FORMAT:'];
        $section[] = 'Generate the following file structure:';
        $section[] = '';
        $section[] = 'src/';
        $section[] = '├── pages/';

        foreach ($allPages as $page) {
            $pageName = $this->getPageComponentName($page);
            $section[] = "│   ├── {$pageName}.vue";
        }

        $section[] = '├── components/';
        $section[] = '│   ├── Layout.vue';

        $navigation = $blueprint['layout']['navigation'] ?? 'sidebar';
        if (in_array($navigation, ['sidebar', 'hybrid'])) {
            $section[] = '│   ├── Sidebar.vue';
        }
        if (in_array($navigation, ['topbar', 'hybrid'])) {
            $section[] = '│   ├── Topbar.vue';
        }

        foreach ($components as $component) {
            $componentName = ucfirst($component);
            if ($component === 'charts') {
                $section[] = '│   ├── Chart.vue';
            } else {
                $section[] = "│   ├── {$componentName}.vue";
            }
        }

        $section[] = '├── composables/';
        $section[] = '│   └── useTheme.ts';
        $section[] = '└── types/';
        $section[] = '    └── index.ts';

        return implode("\n", $section);
    }

    /**
     * IMPLEMENTATION: Single page requirements
     */
    private function buildSinglePageImplementation(array $blueprint, string $pageName, bool $isCustom, ?array $customPageInfo): string
    {
        $outputFormat = $blueprint['outputFormat'] ?? 'vue';
        $frameworkConfig = $blueprint['frameworkConfig'] ?? [];
        $section = ['IMPLEMENTATION REQUIREMENTS:'];

        if ($isCustom && $customPageInfo) {
            $cleanName = substr($pageName, 7);
            $section[] = "\nCustom Page: {$cleanName}";
            $section[] = "User Description: {$customPageInfo['description']}";
            $section[] = '';
            $section[] = 'Based on the description, implement:';
            $section[] = '- Appropriate page structure and layout';
            $section[] = '- Relevant content and functionality';
            $section[] = '- Forms if data input is implied';
            $section[] = '- Tables/lists if data display is implied';
            $section[] = '- Charts if analytics are implied';
            $section[] = '- Actions if CRUD operations are implied';
        } else {
            $section[] = $this->getPageImplementationRequirements($pageName, $blueprint);
        }

        $section[] = "\nGeneral Requirements:";

        if ($this->isFrameworkOutput($outputFormat)) {
            // Framework-specific implementation requirements
            $section[] = '- DO NOT wrap content in a layout — the router/layout already handles this';
            $section[] = '- Apply theme colors consistently using the CSS framework';
            $section[] = '- Implement responsive design';
            $section[] = '- Include loading and error states where appropriate';
            $section[] = '- Use realistic sample data (not Lorem ipsum)';

            $stateManagement = $frameworkConfig['stateManagement'] ?? 'none';
            if ($stateManagement !== 'none') {
                $smName = $this->getStateManagementName($stateManagement);
                $section[] = "- Use {$smName} for any shared state";
            }

            $section = array_merge($section, $this->getFrameworkImplementationGuidance($outputFormat, $frameworkConfig));
        } else {
            $section[] = '- Generate a COMPLETE standalone HTML page with <!DOCTYPE html>';
            $section[] = '- If shared layout components are provided below, COPY them EXACTLY into the page';
            $section[] = '- DO NOT recreate or restyle the sidebar, navbar, or footer — use them verbatim';
            $section[] = '- Only generate the main content area that is unique to this page';
            $section[] = '- Apply theme colors consistently';
            $section[] = '- Implement responsive design';
            $section[] = '- Include loading and error states where appropriate';
            $section[] = '- Use realistic sample data (not Lorem ipsum)';
        }

        return implode("\n", $section);
    }

    /**
     * IMPLEMENTATION: All pages (legacy)
     */
    private function buildImplementationInstructions(array $blueprint): string
    {
        $allPages = $this->getPageList($blueprint);

        $section = ['IMPLEMENTATION INSTRUCTIONS:'];
        $section[] = 'Output Intent: Production-Ready Base';
        $section[] = '- Realistic sample content';
        $section[] = '- Proper error handling';
        $section[] = '- Accessibility: WCAG AA';
        $section[] = '- Form validation with user feedback';

        $section[] = "\nPage-Specific Requirements:";

        foreach ($allPages as $page) {
            $isCustom = str_starts_with($page, 'custom:');
            if ($isCustom) {
                $cleanName = substr($page, 7);
                $customPageInfo = $this->getCustomPageInfo($blueprint, $cleanName);
                $section[] = "\n".ucfirst($cleanName).' (Custom):';
                $section[] = '- '.($customPageInfo['description'] ?? 'User-defined page');
            } else {
                $section[] = "\n".$this->getPageImplementationRequirements($page, $blueprint);
            }
        }

        $section[] = "\nGeneral Requirements:";
        $section[] = '- All pages use the Layout component';
        $section[] = '- Navigation menu items correspond to included pages';
        $section[] = '- Active route indication in navigation';
        $section[] = '- Theme colors applied consistently';
        $section[] = '- Responsive breakpoints applied';

        return implode("\n", $section);
    }

    /**
     * Get implementation requirements for specific page type
     */
    private function getPageImplementationRequirements(string $page, array $blueprint): string
    {
        $requirements = [ucfirst($this->formatPageName($page)).':'];

        switch ($page) {
            case 'dashboard':
                $requirements[] = '- 4 metric cards (e.g., Total Users, Revenue, Orders, Growth %)';
                $requirements[] = '- Line chart showing trend (last 7 days)';
                $requirements[] = '- Recent activity table (5 rows)';
                $requirements[] = '- Quick actions section';
                break;

            case 'login':
                $requirements[] = '- Email and password inputs';
                $requirements[] = '- Remember me checkbox';
                $requirements[] = '- Submit button (disabled when invalid)';
                $requirements[] = '- Links to forgot password and register';
                $requirements[] = '- Client-side validation';
                break;

            case 'register':
                $requirements[] = '- Name, email, password, confirm password inputs';
                $requirements[] = '- Terms acceptance checkbox';
                $requirements[] = '- Submit button';
                $requirements[] = '- Link to login';
                $requirements[] = '- Password strength indicator';
                break;

            case 'forgot-password':
                $requirements[] = '- Email input field';
                $requirements[] = '- Submit button';
                $requirements[] = '- Success message on submission';
                $requirements[] = '- Link back to login';
                break;

            case 'user-management':
                $requirements[] = '- Data table with columns: Avatar, Name, Email, Role, Status, Actions';
                $requirements[] = '- Search/filter input';
                $requirements[] = '- Add user button';
                $requirements[] = '- Edit and delete actions per row';
                $requirements[] = '- Pagination (10 rows per page)';
                $requirements[] = '- Sample data: 15 users';
                break;

            case 'settings':
                $requirements[] = '- Tabs: Profile, Account, Notifications, Security';
                $requirements[] = '- Profile tab: Name, email, avatar upload';
                $requirements[] = '- Account tab: Language, timezone, theme';
                $requirements[] = '- Notifications tab: Email/push toggles';
                $requirements[] = '- Security tab: Change password, 2FA toggle';
                break;

            case 'charts':
                $chartLib = $blueprint['chartLibrary'] ?? 'chartjs';
                $chartName = $chartLib === 'echarts' ? 'Apache ECharts' : 'Chart.js';
                $requirements[] = '- Line chart: Revenue over time (12 months)';
                $requirements[] = '- Bar chart: Sales by category (6 categories)';
                $requirements[] = '- Doughnut chart: Traffic sources (4 sources)';
                $requirements[] = "- Use {$chartName} library";
                break;

            case 'tables':
                $requirements[] = '- Sortable columns (click header to sort)';
                $requirements[] = '- Filterable rows (search input)';
                $requirements[] = '- Selectable rows (checkbox column)';
                $requirements[] = '- Bulk actions (delete selected)';
                $requirements[] = '- Export button (CSV)';
                $requirements[] = '- Sample data: 25 rows';
                break;

            case 'profile':
                $requirements[] = '- User avatar (large, centered)';
                $requirements[] = '- User info: Name, email, role, joined date';
                $requirements[] = '- Edit profile button';
                $requirements[] = '- Activity timeline (last 10 actions)';
                $requirements[] = '- Stats cards: Posts, Followers, Following';
                break;

            case 'about':
                $requirements[] = '- Hero section with company mission';
                $requirements[] = '- Team section (4 team members)';
                $requirements[] = '- Values section (3 core values)';
                $requirements[] = '- Call-to-action section';
                break;

            case 'contact':
                $requirements[] = '- Contact form: Name, email, subject, message';
                $requirements[] = '- Contact info: Address, phone, email';
                $requirements[] = '- Map placeholder';
                $requirements[] = '- Social media links';
                break;

            default:
                $requirements[] = '- Basic page structure with title';
                $requirements[] = '- Content relevant to page name';
                break;
        }

        return implode("\n", $requirements);
    }

    // ========================================================================
    // Helper Methods
    // ========================================================================

    private function getFrameworkName(string $framework): string
    {
        return match ($framework) {
            'tailwind' => 'Tailwind CSS',
            'bootstrap' => 'Bootstrap',
            'pure-css' => 'Pure CSS',
            default => ucfirst($framework),
        };
    }

    private function getOutputFormatName(string $format): string
    {
        return match ($format) {
            'html-css' => 'HTML + CSS',
            'react' => 'React',
            'vue' => 'Vue.js',
            'angular' => 'Angular',
            'svelte' => 'Svelte',
            'custom' => 'Custom',
            default => ucfirst($format),
        };
    }

    private function formatCategoryName(string $category): string
    {
        if ($category === 'custom') {
            return 'Custom Category';
        }

        return ucwords(str_replace('-', ' ', $category));
    }

    private function formatPageName(string $page): string
    {
        if (str_starts_with($page, 'component:custom:')) {
            return ucwords(str_replace('-', ' ', substr($page, 17))).' (Component Showcase)';
        }

        if (str_starts_with($page, 'component:')) {
            return ucwords(str_replace('-', ' ', substr($page, 10))).' (Component Showcase)';
        }

        if (str_starts_with($page, 'custom:')) {
            return ucwords(str_replace('-', ' ', substr($page, 7))).' (Custom)';
        }

        return ucwords(str_replace('-', ' ', $page));
    }

    private function formatNavigationName(string $navigation): string
    {
        return match ($navigation) {
            'sidebar' => 'Collapsible Sidebar',
            'topbar' => 'Top Navigation Bar',
            'hybrid' => 'Hybrid (Sidebar + Top Bar)',
            default => ucfirst($navigation),
        };
    }

    private function formatResponsivenessName(string $responsiveness): string
    {
        return match ($responsiveness) {
            'desktop-first' => 'Desktop-First',
            'mobile-first' => 'Mobile-First',
            'fully-responsive' => 'Fully Responsive',
            default => ucfirst($responsiveness),
        };
    }

    private function getPageComponentName(string $page): string
    {
        if (str_starts_with($page, 'custom:')) {
            $page = substr($page, 7);
        }

        return str_replace(' ', '', ucwords(str_replace('-', ' ', $page)));
    }

    /**
     * Check if the output format is a JS framework requiring multi-file scaffold.
     */
    private function isFrameworkOutput(string $outputFormat): bool
    {
        return in_array($outputFormat, ['react', 'vue', 'svelte', 'angular']);
    }

    /**
     * Convert string to kebab-case for file paths.
     */
    private function toKebabCase(string $str): string
    {
        return strtolower(
            preg_replace('/[A-Z]/', '-$0', lcfirst(
                str_replace([' ', '_'], '-', $str)
            ))
        );
    }

    /**
     * Get human-readable state management name.
     */
    private function getStateManagementName(string $stateManagement): string
    {
        return match ($stateManagement) {
            'zustand' => 'Zustand',
            'redux' => 'Redux Toolkit',
            'pinia' => 'Pinia',
            'ngrx' => 'NgRx',
            'svelte-store' => 'Svelte stores',
            default => ucfirst($stateManagement),
        };
    }

    /**
     * FRAMEWORK CONFIG: Framework-specific configuration context
     *
     * Provides the LLM with full context about the framework setup,
     * so it generates code compatible with the scaffold.
     */
    private function buildFrameworkConfigSection(array $blueprint): string
    {
        $frameworkConfig = $blueprint['frameworkConfig'] ?? [];
        $outputFormat = $blueprint['outputFormat'] ?? 'vue';
        $language = ($frameworkConfig['language'] ?? 'typescript') === 'typescript' ? 'TypeScript' : 'JavaScript';
        $styling = $frameworkConfig['styling'] ?? 'tailwind';
        $hasRouter = $frameworkConfig['router'] ?? true;
        $stateManagement = $frameworkConfig['stateManagement'] ?? 'none';
        $buildTool = $frameworkConfig['buildTool'] ?? 'vite';

        $section = ['FRAMEWORK PROJECT CONFIGURATION:'];
        $section[] = "This is a multi-file {$this->getOutputFormatName($outputFormat)} project with the following setup:";
        $section[] = "- Language: {$language}";
        $section[] = '- CSS/Styling: '.$this->getStylingDisplayName($styling);
        $section[] = '- Router: '.($hasRouter ? 'Enabled' : 'Disabled');
        $section[] = '- State Management: '.($stateManagement === 'none' ? 'None (local state only)' : $this->getStateManagementName($stateManagement));
        $section[] = '- Build Tool: '.ucfirst($buildTool);
        $section[] = '';
        $section[] = 'SCAFFOLD FILES (ALREADY GENERATED - DO NOT RECREATE):';
        $section[] = '- package.json with all dependencies';
        $section[] = "- Build tool configuration ({$buildTool}.config)";
        $section[] = '- Main entry point (src/main)';
        $section[] = '- App root component';

        if ($hasRouter) {
            $section[] = '- Router configuration with all page routes';
        }

        $section[] = '- MainLayout component (sidebar/topbar navigation)';
        $section[] = '- Global CSS with theme variables';
        $section[] = '';
        $section[] = 'You ONLY need to generate the page component content.';

        return implode("\n", $section);
    }

    /**
     * Get display name for styling option.
     */
    private function getStylingDisplayName(string $styling): string
    {
        return match ($styling) {
            'tailwind' => 'Tailwind CSS',
            'bootstrap' => 'Bootstrap',
            'css-modules' => 'CSS Modules',
            'styled-components' => 'Styled Components',
            default => ucfirst($styling),
        };
    }

    /**
     * Get framework-specific output rules for the LLM.
     *
     * @return string[]
     */
    private function getFrameworkOutputRules(string $outputFormat, array $frameworkConfig): array
    {
        $isTs = ($frameworkConfig['language'] ?? 'typescript') === 'typescript';
        $styling = $frameworkConfig['styling'] ?? 'tailwind';
        $rules = [];

        switch ($outputFormat) {
            case 'react':
                $rules[] = '- Use React functional components with hooks';
                $rules[] = $isTs
                    ? '- Use TypeScript interfaces for all props and data types'
                    : '- Use PropTypes or JSDoc for component props';
                $rules[] = "- Use 'export default function ComponentName()' pattern";
                $rules[] = '- Import React hooks as needed: useState, useEffect, useMemo, useCallback';
                if ($styling === 'tailwind') {
                    $rules[] = '- Use Tailwind CSS utility classes for all styling';
                } elseif ($styling === 'css-modules') {
                    $rules[] = "- Use CSS Modules for styling (import styles from './ComponentName.module.css')";
                } elseif ($styling === 'styled-components') {
                    $rules[] = '- Use styled-components for styling';
                }
                break;

            case 'vue':
                $rules[] = '- Use Vue 3 Composition API with <script setup'.($isTs ? ' lang="ts"' : '').'>';
                $rules[] = '- Use ref(), computed(), and watch() for reactivity';
                $rules[] = $isTs
                    ? '- Define TypeScript interfaces for props using defineProps<T>()'
                    : '- Define props using defineProps()';
                $rules[] = '- Use single-file component (.vue) format';
                if ($styling === 'tailwind') {
                    $rules[] = '- Use Tailwind CSS utility classes in template';
                }
                break;

            case 'svelte':
                $rules[] = '- Use Svelte 5 runes ($state, $derived, $effect)';
                $rules[] = '- DO NOT use legacy Svelte 4 syntax (no export let, no $: reactive)';
                $rules[] = '- Use {@render children()} for slot rendering, not <slot>';
                $rules[] = '- Use $props() for component props';
                if ($isTs) {
                    $rules[] = '- Use <script lang="ts"> for TypeScript';
                }
                if ($styling === 'tailwind') {
                    $rules[] = '- Use Tailwind CSS utility classes';
                }
                break;

            case 'angular':
                $rules[] = '- Use Angular standalone components (standalone: true)';
                $rules[] = '- Import CommonModule and other Angular modules as needed';
                $rules[] = '- Use Angular signals for reactive state where appropriate';
                $rules[] = '- Use inline template (template: `...`) for single-file components';
                $rules[] = '- Import RouterLink for navigation links';
                break;
        }

        return $rules;
    }

    /**
     * Get framework-specific implementation guidance.
     *
     * @return string[]
     */
    private function getFrameworkImplementationGuidance(string $outputFormat, array $frameworkConfig): array
    {
        $guidance = [];

        switch ($outputFormat) {
            case 'react':
                $guidance[] = "- Use React Router's useNavigate() for programmatic navigation";
                $guidance[] = "- Use React Router's useParams() for route parameters";
                break;

            case 'vue':
                $guidance[] = '- Use useRoute() and useRouter() from vue-router for navigation';
                $guidance[] = '- Emit events for parent communication';
                break;

            case 'svelte':
                $guidance[] = '- Use goto() from $app/navigation for programmatic navigation';
                $guidance[] = '- Use page from $app/state for current route info';
                break;

            case 'angular':
                $guidance[] = '- Use Router and ActivatedRoute for navigation';
                $guidance[] = "- Use Angular's HttpClient pattern for data fetching (mock data ok)";
                break;
        }

        return $guidance;
    }
}
