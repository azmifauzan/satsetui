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
     * @param array $blueprint Validated blueprint data matching schema
     * @param string $pageName Name of the page to generate (e.g., 'dashboard', 'custom:inventory')
     * @param int $pageIndex 0-based index of current page in generation order
     * @return string Complete MCP prompt ready for LLM API
     */
    public function buildForPage(array $blueprint, string $pageName, int $pageIndex = 0): string
    {
        // Extract auto-selected values with defaults
        $autoSelected = $blueprint['autoSelected'] ?? [];
        $blueprint['responsiveness'] = $autoSelected['responsiveness'] ?? 'fully-responsive';
        $blueprint['interaction'] = $autoSelected['interaction'] ?? 'moderate';
        $blueprint['codeStyle'] = $autoSelected['codeStyle'] ?? 'documented';

        // Get page info
        $isCustomPage = str_starts_with($pageName, 'custom:');
        $cleanPageName = $isCustomPage ? substr($pageName, 7) : $pageName;
        $customPageInfo = $this->getCustomPageInfo($blueprint, $cleanPageName);

        // Assemble MCP sections in strict order for single page
        return implode("\n\n", [
            $this->buildRoleSection($blueprint),
            $this->buildProjectContextSection($blueprint, $pageName, $pageIndex),
            $this->buildProjectInfoSection($blueprint),
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
    }

    /**
     * Build MCP prompt for all pages (legacy method for compatibility)
     *
     * @deprecated Use buildForPage() for per-page generation
     * @param array $blueprint Validated blueprint data
     * @return string Complete MCP prompt for all pages
     */
    public function buildFromBlueprint(array $blueprint): string
    {
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
     * @param array $blueprint Blueprint data
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
            $pages[] = 'custom:' . $customPage['name'];
        }

        // Add component showcase pages with prefix
        foreach ($components as $component) {
            $pages[] = 'component:' . $component;
        }

        // Add custom component showcase pages
        foreach ($customComponents as $customComponent) {
            $pages[] = 'component:custom:' . $customComponent['name'];
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

        // Get output format expertise
        $outputExpertise = match($outputFormat) {
            'react' => "React 18 with TypeScript and functional components",
            'angular' => "Angular 17 with TypeScript and standalone components",
            'svelte' => "Svelte 4 with TypeScript",
            'html-css' => "modern HTML5 and CSS3",
            'custom' => $blueprint['customOutputFormat'] ?? 'modern frontend frameworks',
            default => "Vue.js 3 with TypeScript and Composition API",
        };

        return "You are an expert frontend developer specializing in {$frameworkName} and {$outputExpertise}.\n\n" .
               "You build responsive, accessible interfaces with clean, maintainable code.\n" .
               "You follow best practices: semantic HTML, WCAG accessibility, performance optimization.\n" .
               "You write complete, working code without placeholders or TODOs.";
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

        $section = ["PROJECT CONTEXT:"];
        $section[] = "- Template Category: {$category}";
        $section[] = "- Framework: " . $this->getFrameworkName($blueprint['framework']);
        $section[] = "- Output Format: " . $this->getOutputFormatName($blueprint['outputFormat'] ?? 'vue');
        $section[] = "- All Pages in Project: {$pagesList}";
        $section[] = "- Total Pages: {$pageCount}";
        $section[] = "";
        $section[] = "CURRENT GENERATION:";
        $section[] = "- Generating Page: {$currentPageFormatted}";
        $section[] = "- Page Index: " . ($pageIndex + 1) . " of {$pageCount}";

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

        $section = ["PROJECT INFORMATION (USE CONSISTENTLY ACROSS ALL PAGES):"];
        
        // Company/Organization Info
        if (!empty($projectInfo['companyName'])) {
            $section[] = "- Company/Organization Name: {$projectInfo['companyName']}";
            $section[] = "  → Use this exact name in headers, footers, page titles, and branding";
        }
        
        if (!empty($projectInfo['companyDescription'])) {
            $section[] = "- Company Description: {$projectInfo['companyDescription']}";
            $section[] = "  → Use this for meta descriptions, about sections, and taglines";
        }

        // Application Info
        if (!empty($projectInfo['appName'])) {
            $section[] = "- Application Name: {$projectInfo['appName']}";
            $section[] = "  → Use this in dashboard headers, page titles, and navigation";
        }

        // Store Info (E-commerce)
        if (!empty($projectInfo['storeName'])) {
            $section[] = "- Store Name: {$projectInfo['storeName']}";
            $section[] = "  → Use this in store branding, headers, and footer";
        }
        
        if (!empty($projectInfo['storeDescription'])) {
            $section[] = "- Store Description: {$projectInfo['storeDescription']}";
            $section[] = "  → Use this for store tagline and about content";
        }

        $section[] = "";
        $section[] = "IMPORTANT CONSISTENCY RULES:";
        $section[] = "1. Use the EXACT names provided above - do NOT make up different names";
        $section[] = "2. Apply these consistently across ALL pages (navigation, headers, footers, titles)";
        $section[] = "3. Every page must use the same company/project name in its branding";
        $section[] = "4. Footer content must be identical across all pages";
        $section[] = "5. Navigation menu must be identical across all pages (same items, same order)";

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

        return "PROJECT CONTEXT:\n" .
               "- Template Category: {$category}\n" .
               "- Target Pages: {$pagesList}\n" .
               "- Total Pages: {$pageCount}\n" .
               "- Framework: " . $this->getFrameworkName($blueprint['framework']) . "\n" .
               "- Output Format: " . $this->getOutputFormatName($blueprint['outputFormat'] ?? 'vue');
    }

    /**
     * CONSTRAINTS: Hard technology boundaries
     */
    private function buildConstraintsSection(array $blueprint): string
    {
        $framework = $blueprint['framework'];
        $outputFormat = $blueprint['outputFormat'] ?? 'vue';
        $constraints = ["CONSTRAINTS (MUST FOLLOW):"];

        // Framework constraints
        if ($framework === 'tailwind') {
            $constraints[] = "- Use ONLY Tailwind CSS utility classes (no custom CSS files)";
            $constraints[] = "- No inline styles (style attribute)";
            $constraints[] = "- Responsive breakpoints: sm:640px, md:768px, lg:1024px, xl:1280px, 2xl:1536px";
        } elseif ($framework === 'bootstrap') {
            $constraints[] = "- Use ONLY Bootstrap classes (no custom CSS files)";
            $constraints[] = "- No inline styles (style attribute)";
            $constraints[] = "- Responsive breakpoints: xs:<576px, sm:≥576px, md:≥768px, lg:≥992px, xl:≥1200px";
        } else {
            // pure-css
            $constraints[] = "- Use semantic CSS with BEM naming convention";
            $constraints[] = "- Create CSS variables for theme customization";
            $constraints[] = "- Standard responsive breakpoints: mobile (<768px), tablet (768-1024px), desktop (>1024px)";
        }

        // Charts constraint
        if (in_array('charts', $blueprint['components'] ?? [])) {
            $chartLib = $blueprint['chartLibrary'] ?? 'chartjs';
            $chartName = $chartLib === 'echarts' ? 'Apache ECharts' : 'Chart.js';
            $constraints[] = "- Use ONLY {$chartName} for data visualizations";
            $constraints[] = "- No other chart libraries (including D3.js, Recharts, etc.)";
        }

        // Output format constraints
        $constraints[] = "- No backend logic (frontend templates only)";
        $constraints[] = "- All imports must be valid (no placeholders)";
        $constraints[] = "- Generate complete, working code without TODO comments";

        return implode("\n", $constraints);
    }

    /**
     * CURRENT PAGE: Specific focus for per-page generation
     */
    private function buildCurrentPageSection(array $blueprint, string $pageName, bool $isCustom, ?array $customPageInfo): string
    {
        $section = ["CURRENT PAGE FOCUS:"];
        
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
                $section[] = "- Page Type: Component Demo Page";
                $section[] = "- Purpose: Showcase page demonstrating the '{$componentName}' component with various examples and use cases";
                if ($componentInfo) {
                    $section[] = "- Component Description: {$componentInfo['description']}";
                }
                $section[] = "";
                $section[] = "Requirements for this component showcase page:";
                $section[] = "- Create a dedicated page that showcases this custom component";
                $section[] = "- Include multiple examples/variations of the component";
                $section[] = "- Show different states (default, hover, active, disabled if applicable)";
                $section[] = "- Provide visual examples with descriptions";
                $section[] = "- Use realistic data and scenarios";
            } else {
                $componentName = substr($pageName, 10); // Remove 'component:'
                $displayName = ucwords(str_replace('-', ' ', $componentName));
                
                $section[] = "- Page Name: {$displayName}";
                $section[] = "- Page Type: Component Showcase Page";
                $section[] = "- Purpose: Demonstrate all variations and states of {$displayName} components (like AdminLTE UI Elements pages)";
                $section[] = "";
                $section[] = $this->getComponentShowcaseRequirements($componentName, $blueprint);
            }
        } elseif ($isCustom) {
            $cleanName = substr($pageName, 7); // Remove 'custom:'
            $formattedName = $this->formatPageName($cleanName);
            
            $section[] = "- Page Name: {$formattedName}";
            $section[] = "- Page Type: Custom (user-defined)";
            
            if ($customPageInfo) {
                $section[] = "- Custom Description: {$customPageInfo['description']}";
                $section[] = "";
                $section[] = "IMPORTANT: This is a custom page defined by the user.";
                $section[] = "Use the description above to determine the page's purpose and content.";
                $section[] = "Implement appropriate functionality based on the description.";
            }
        } else {
            $formattedName = $this->formatPageName($pageName);
            $section[] = "- Page Name: {$formattedName}";
            $section[] = "- Page Type: Predefined";
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

        $section = ["LAYOUT REQUIREMENTS:"];
        $section[] = "- Navigation Pattern: " . $this->formatNavigationName($navigation);
        $section[] = "";
        $section[] = "CRITICAL: You MUST use the '{$navigation}' navigation pattern for ALL pages.";
        $section[] = "DO NOT use a different navigation pattern for different pages.";
        $section[] = "";

        if ($navigation === 'sidebar' || $navigation === 'hybrid') {
            $sidebarState = $layout['sidebarDefaultState'] ?? 'expanded';
            $section[] = "- Sidebar Default State: " . ucfirst($sidebarState);
            $section[] = "- Sidebar Behavior: Collapsible with toggle button";
            $section[] = "- Sidebar Width: 256px expanded, 64px collapsed";
            $section[] = "- IMPORTANT: Use sidebar navigation on ALL pages, not top navigation";

            if ($navigation === 'hybrid') {
                $section[] = "- Top Bar: Fixed position, contains user menu and notifications";
                $section[] = "- Sidebar: Contains primary navigation menu";
            }
        }

        if ($navigation === 'topbar') {
            $section[] = "- Top Bar: Fixed position, full width, contains all navigation";
            $section[] = "- Top Bar Height: 64px";
            $section[] = "- IMPORTANT: Use top bar navigation on ALL pages, not sidebar";
        }

        $section[] = "- Breadcrumbs: {$breadcrumbs}";
        $section[] = "- Footer: {$footer} style";
        $section[] = "";
        $section[] = "Footer Consistency:";
        $section[] = "- Footer content MUST be IDENTICAL across all pages";
        $section[] = "- Use the same copyright text, links, and layout on every page";
        $section[] = "- Do NOT create different footers for different pages";

        // Include navigation menu structure
        $allPages = $this->getPageList($blueprint);
        $regularPages = [];
        $componentPages = [];
        
        foreach ($allPages as $pageName) {
            if (str_starts_with($pageName, 'component:')) {
                $componentPages[] = $pageName;
            } elseif (!str_starts_with($pageName, 'custom:')) {
                $regularPages[] = $pageName;
            }
        }
        
        if (!empty($regularPages) || !empty($componentPages)) {
            $section[] = "";
            $section[] = "Navigation Menu Structure (MUST BE IDENTICAL ON ALL PAGES):";
            $section[] = "The following menu items MUST appear in the same order on every page:";
            $section[] = "";
            
            if (!empty($regularPages)) {
                $section[] = "Main Pages Group:";
                foreach ($regularPages as $pageName) {
                    $displayName = $this->formatPageName($pageName);
                    $section[] = "  • {$displayName}";
                }
            }
            
            if (!empty($componentPages)) {
                $section[] = "";
                $section[] = "UI Components Group (or 'UI Elements'):";
                foreach ($componentPages as $pageName) {
                    $cleanName = str_replace('component:', '', $pageName);
                    $cleanName = str_replace('custom:', '', $cleanName);
                    $displayName = ucwords(str_replace('-', ' ', $cleanName));
                    $section[] = "  • {$displayName}";
                }
            }
            
            $section[] = "";
            $section[] = "⚠️ CRITICAL: Every page MUST have the EXACT SAME menu items in the SAME ORDER.";
            $section[] = "Do NOT add, remove, or reorder menu items between pages.";
        }

        // Include custom navigation items
        $customNavItems = $layout['customNavItems'] ?? [];
        if (!empty($customNavItems)) {
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

        $section = ["THEME SPECIFICATION:"];
        $section[] = "- Primary Color: {$theme['primary']}";
        $section[] = "- Secondary Color: {$theme['secondary']}";
        $section[] = "- Color Mode: " . ucfirst($theme['mode']);
        $section[] = "- Background Style: " . ucfirst($theme['background']);

        if ($framework === 'tailwind') {
            $section[] = "\nTailwind Implementation:";
            $section[] = "- Define CSS variables in :root and .dark classes";
            $section[] = "- Use custom color classes: bg-primary, text-primary, border-primary";
            $section[] = "- Dark mode: Add dark: prefix for dark mode styles";

            if ($theme['mode'] === 'dark') {
                $section[] = "- Default Mode: Apply .dark class to <html> element";
            }
        } elseif ($framework === 'bootstrap') {
            $section[] = "\nBootstrap Implementation:";
            $section[] = "- Override Bootstrap variables: \$primary, \$secondary";
            $section[] = "- Use Bootstrap color utilities: bg-primary, text-secondary";
            $section[] = "- Dark mode: Use data-bs-theme=\"dark\" attribute";
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

        $section = ["UI DENSITY & STYLE:"];
        $section[] = "- Density Level: " . ucfirst($density);
        $section[] = "- Border Radius: " . ucfirst($borderRadius);

        if ($framework === 'tailwind') {
            $section[] = "\nTailwind Spacing:";
            $spacing = match($density) {
                'compact' => [
                    "- Container Padding: p-4",
                    "- Card Padding: p-3",
                    "- Element Spacing: space-y-2, gap-2",
                    "- Font Size: text-sm (body), text-xs (secondary)",
                ],
                'spacious' => [
                    "- Container Padding: p-8",
                    "- Card Padding: p-6",
                    "- Element Spacing: space-y-6, gap-6",
                    "- Font Size: text-lg (body), text-base (secondary)",
                ],
                default => [
                    "- Container Padding: p-6",
                    "- Card Padding: p-4",
                    "- Element Spacing: space-y-4, gap-4",
                    "- Font Size: text-base (body), text-sm (secondary)",
                ],
            };
            $section = array_merge($section, $spacing);

            $section[] = "\nBorder Radius:";
            if ($borderRadius === 'sharp') {
                $section[] = "- Cards: rounded-sm (2px)";
                $section[] = "- Buttons: rounded (4px)";
            } else {
                $section[] = "- Cards: rounded-lg (8px)";
                $section[] = "- Buttons: rounded-md (6px)";
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

        $section = ["COMPONENT PAGES:"];
        $section[] = "Each component below will have its own dedicated showcase page (like AdminLTE UI Elements pages).\n";

        // Predefined components
        if (count($components) > 0) {
            $section[] = "Standard Component Pages:";
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
        $requirements = ["Component Showcase Requirements:"];

        switch($component) {
            case 'buttons':
                $requirements[] = "- Show all button variants: Primary, Secondary, Success, Danger, Warning, Info";
                $requirements[] = "- Demonstrate all sizes: Small, Default, Large";
                $requirements[] = "- Show all states: Default, Hover (describe hover effect), Active, Disabled";
                $requirements[] = "- Include icon buttons, button groups, outline buttons";
                $requirements[] = "- Add block buttons (full width)";
                $requirements[] = "- Include loading state buttons";
                $requirements[] = "- Organize in sections with clear labels";
                break;

            case 'forms':
                $requirements[] = "- Text inputs: Default, with placeholder, with label, with helper text";
                $requirements[] = "- Input validation states: Success, Error, Warning";
                $requirements[] = "- Input sizes: Small, Default, Large";
                $requirements[] = "- Input types: Text, Email, Password, Number, Tel, URL";
                $requirements[] = "- Textarea: Default, with character counter";
                $requirements[] = "- Select dropdown: Single select, multiple select";
                $requirements[] = "- Checkboxes: Single, multiple, with labels";
                $requirements[] = "- Radio buttons: Group with multiple options";
                $requirements[] = "- File upload input";
                $requirements[] = "- Form layouts: Vertical, horizontal, inline";
                $requirements[] = "- Complete form example with validation";
                break;

            case 'modals':
                $requirements[] = "- Basic modal with header, body, footer";
                $requirements[] = "- Modal sizes: Small, Default, Large, Extra Large";
                $requirements[] = "- Modal variations: Centered, scrollable content";
                $requirements[] = "- Confirm dialog modal";
                $requirements[] = "- Form inside modal example";
                $requirements[] = "- Include buttons to trigger each modal type";
                break;

            case 'dropdowns':
                $requirements[] = "- Basic dropdown menu";
                $requirements[] = "- Dropdown with icons";
                $requirements[] = "- Dropdown with dividers";
                $requirements[] = "- Dropdown alignments: Left, Right";
                $requirements[] = "- Dropdown directions: Down, Up";
                $requirements[] = "- Split button dropdown";
                $requirements[] = "- Dropdown in button group";
                break;

            case 'alerts':
                $requirements[] = "- Alert types: Success, Info, Warning, Error/Danger";
                $requirements[] = "- Dismissible alerts (with close button)";
                $requirements[] = "- Alerts with icons";
                $requirements[] = "- Alerts with action buttons";
                $requirements[] = "- Alert with title and description";
                $requirements[] = "- Toast notifications (if applicable to framework)";
                break;

            case 'cards':
                $requirements[] = "- Basic card with header, body, footer";
                $requirements[] = "- Card with image (top, overlay)";
                $requirements[] = "- Card with list group";
                $requirements[] = "- Card with tabs";
                $requirements[] = "- Horizontal card layout";
                $requirements[] = "- Card with actions (buttons in header/footer)";
                $requirements[] = "- Card colors/variants";
                $requirements[] = "- Card grid layout example (3 cards in row)";
                break;

            case 'tabs':
                $requirements[] = "- Horizontal tabs (default)";
                $requirements[] = "- Tabs with icons";
                $requirements[] = "- Tabs justified (full width)";
                $requirements[] = "- Vertical tabs";
                $requirements[] = "- Pills style tabs";
                $requirements[] = "- Example with real content in each tab panel";
                break;

            case 'charts':
                $chartLib = $blueprint['chartLibrary'] ?? 'chartjs';
                $chartName = $chartLib === 'echarts' ? 'Apache ECharts' : 'Chart.js';
                $requirements[] = "- Use {$chartName} library exclusively";
                $requirements[] = "- Line Chart: Monthly revenue for 12 months with 2 data series";
                $requirements[] = "- Bar Chart: Sales by category (6-8 categories)";
                $requirements[] = "- Pie/Doughnut Chart: Market share (4-5 segments)";
                $requirements[] = "- Area Chart: Website traffic over time";
                $requirements[] = "- Mixed Chart: Combination of line and bar";
                $requirements[] = "- Each chart in its own card with title";
                $requirements[] = "- Use theme colors in charts";
                $requirements[] = "- Responsive charts that adapt to container";
                break;

            case 'tables':
                $requirements[] = "- Basic table with striped rows";
                $requirements[] = "- Table with hover effect";
                $requirements[] = "- Bordered table";
                $requirements[] = "- Compact table (small padding)";
                $requirements[] = "- Table with action buttons in last column";
                $requirements[] = "- Responsive table (horizontal scroll on mobile)";
                $requirements[] = "- Table with search/filter input";
                $requirements[] = "- Sortable table headers (indicate sort direction)";
                $requirements[] = "- Table with pagination (sample data: 20+ rows)";
                $requirements[] = "- Table with row selection (checkboxes)";
                break;

            default:
                $requirements[] = "- Show multiple examples of {$component}";
                $requirements[] = "- Demonstrate different variations and use cases";
                $requirements[] = "- Include different states where applicable";
                $requirements[] = "- Use realistic sample data";
                break;
        }

        $requirements[] = "\nGeneral Showcase Guidelines:";
        $requirements[] = "- Organize examples in sections with descriptive headings";
        $requirements[] = "- Each example should be in its own card or section";
        $requirements[] = "- Add brief descriptions/labels for each variant";
        $requirements[] = "- Use consistent spacing between examples";
        $requirements[] = "- Make it easy to understand and copy-paste patterns";

        return implode("\n", $requirements);
    }

    /**
     * Get requirements for a specific component (legacy - for reference)
     */
    private function getComponentRequirements(string $component, array $blueprint): string
    {
        $requirements = match($component) {
            'buttons' => "- Buttons: Primary (filled), Secondary (outline), Destructive (red)\n" .
                        "  Sizes: Small, Default, Large\n" .
                        "  States: Default, Hover, Active, Disabled",
            'forms' => "- Forms: Text Input, Email Input, Password Input, Select, Checkbox, Radio, Textarea\n" .
                      "  Include labels, placeholders, validation states\n" .
                      "  Accessible: proper for/id associations, aria-labels",
            'modals' => "- Modals: Center-screen overlay with backdrop\n" .
                       "  Header with title and close button\n" .
                       "  Body content area, Footer with action buttons\n" .
                       "  Close on backdrop click and ESC key",
            'dropdowns' => "- Dropdowns: Button-triggered menu\n" .
                          "  Support menu items, dividers, icons\n" .
                          "  Click outside to close",
            'alerts' => "- Alerts/Toasts: Success, Error, Warning, Info variants\n" .
                       "  Dismissible with close button\n" .
                       "  Auto-dismiss option (5 seconds)",
            'cards' => "- Cards: Header, Body, Footer sections\n" .
                      "  Optional image support\n" .
                      "  Flexible content layout",
            'tabs' => "- Tabs: Horizontal tab navigation\n" .
                     "  Active state indication\n" .
                     "  Content panels switch on click",
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

        return "- Charts: Data visualizations using {$chartName}\n" .
               "  Line Chart: Time series data\n" .
               "  Bar Chart: Categorical comparisons\n" .
               "  Doughnut/Pie Chart: Proportional data\n" .
               "  Responsive and theme-aware";
    }

    /**
     * INTERACTION: Animation and transition level (auto-selected)
     */
    private function buildInteractionSection(array $blueprint): string
    {
        $interaction = $blueprint['interaction'] ?? 'moderate';

        $section = ["INTERACTION LEVEL: " . ucfirst($interaction) . " (auto-selected)"];

        $details = match($interaction) {
            'static' => [
                "- No animations or transitions",
                "- Instant state changes",
                "- Minimal hover effects (color change only)",
            ],
            'rich' => [
                "- Rich animations: Fade in, slide, scale transforms",
                "- Micro-interactions: Button press feedback, ripple effects",
                "- Loading skeletons: Pulse animations for loading states",
                "- Page transitions: Smooth navigation between views",
            ],
            default => [
                "- Smooth transitions: 150ms ease-in-out for interactive elements",
                "- Hover effects: Background/text color shifts, opacity changes",
                "- Focus states: Outline/ring on keyboard navigation",
                "- No complex animations or parallax",
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

        $section = ["RESPONSIVENESS: " . $this->formatResponsivenessName($responsiveness) . " (auto-selected)"];

        $details = match($responsiveness) {
            'desktop-first' => [
                "- Design optimized for desktop (≥1024px)",
                "- Scale down to tablet and mobile",
                "- Mobile (<640px): Hamburger menu, stacked layout",
            ],
            'mobile-first' => [
                "- Design optimized for mobile (<640px)",
                "- Scale up to tablet and desktop",
                "- Mobile: Bottom navigation or hamburger menu",
            ],
            default => [
                "- Equal optimization for all screen sizes",
                "- Mobile (<640px): Hamburger menu, stacked single column",
                "- Tablet (640-1024px): Collapsible sidebar, 2-column grid",
                "- Desktop (>1024px): Expanded sidebar, 3+ column grid",
                "- Touch-friendly: Minimum 44px tap targets on mobile",
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

        $section = ["CODE STYLE: " . ucfirst($codeStyle) . " (auto-selected)"];

        $details = match($codeStyle) {
            'minimal' => [
                "- Concise variable names (e.g., user, isOpen)",
                "- No comments unless complex logic",
                "- Inline simple logic, extract only when reused",
            ],
            'verbose' => [
                "- Explicit variable names (e.g., currentUser, isModalOpen)",
                "- Moderate comments for non-obvious logic",
                "- Extract helper functions for clarity",
            ],
            default => [
                "- Highly descriptive variable names",
                "- Comments on all functions, complex logic, and component purposes",
                "- Extract and document all helper functions",
                "- Full JSDoc with property descriptions",
                "- Include usage examples in component comments",
            ],
        };

        return implode("\n", array_merge($section, $details));
    }

    /**
     * OUTPUT FORMAT: Single page file structure
     */
    private function buildSinglePageOutputFormat(array $blueprint, string $pageName): string
    {
        $isCustom = str_starts_with($pageName, 'custom:');
        $cleanName = $isCustom ? substr($pageName, 7) : $pageName;
        $componentName = $this->getPageComponentName($cleanName);
        $outputFormat = $blueprint['outputFormat'] ?? 'vue';

        $extension = match($outputFormat) {
            'react' => 'tsx',
            'angular' => 'component.ts',
            'svelte' => 'svelte',
            'html-css' => 'html',
            default => 'vue',
        };

        $section = ["OUTPUT FORMAT:"];
        $section[] = "Generate a SINGLE file for this page:";
        $section[] = "";
        $section[] = "File: src/pages/{$componentName}.{$extension}";
        $section[] = "";
        $section[] = "Requirements:";
        $section[] = "- Start with comment: // src/pages/{$componentName}.{$extension}";
        $section[] = "- Include all necessary imports";
        $section[] = "- Export component as default";
        $section[] = "- Include any page-specific types/interfaces";
        $section[] = "- Generate complete, working code";
        $section[] = "";
        $section[] = "CRITICAL OUTPUT RULES:";
        $section[] = "- Return ONLY the code, nothing else";
        $section[] = "- DO NOT include explanations or markdown";
        $section[] = "- DO NOT wrap in ```typescript, ```vue, or any code blocks";
        $section[] = "- DO NOT add 'Here is...' or any introductory text";
        $section[] = "- Start directly with the file comment";
        $section[] = "";
        $section[] = "DO NOT generate:";
        $section[] = "- Layout/wrapper components (assume they exist)";
        $section[] = "- Shared components (assume they exist: Button, Card, Modal, etc.)";
        $section[] = "- Router configuration";
        $section[] = "- Type files (only page-specific inline types)";

        return implode("\n", $section);
    }

    /**
     * OUTPUT FORMAT: All pages (legacy)
     */
    private function buildOutputFormatSection(array $blueprint): string
    {
        $allPages = $this->getPageList($blueprint);
        $components = $blueprint['components'] ?? [];

        $section = ["OUTPUT FORMAT:"];
        $section[] = "Generate the following file structure:";
        $section[] = "";
        $section[] = "src/";
        $section[] = "├── pages/";

        foreach ($allPages as $page) {
            $pageName = $this->getPageComponentName($page);
            $section[] = "│   ├── {$pageName}.vue";
        }

        $section[] = "├── components/";
        $section[] = "│   ├── Layout.vue";

        $navigation = $blueprint['layout']['navigation'] ?? 'sidebar';
        if (in_array($navigation, ['sidebar', 'hybrid'])) {
            $section[] = "│   ├── Sidebar.vue";
        }
        if (in_array($navigation, ['topbar', 'hybrid'])) {
            $section[] = "│   ├── Topbar.vue";
        }

        foreach ($components as $component) {
            $componentName = ucfirst($component);
            if ($component === 'charts') {
                $section[] = "│   ├── Chart.vue";
            } else {
                $section[] = "│   ├── {$componentName}.vue";
            }
        }

        $section[] = "├── composables/";
        $section[] = "│   └── useTheme.ts";
        $section[] = "└── types/";
        $section[] = "    └── index.ts";

        return implode("\n", $section);
    }

    /**
     * IMPLEMENTATION: Single page requirements
     */
    private function buildSinglePageImplementation(array $blueprint, string $pageName, bool $isCustom, ?array $customPageInfo): string
    {
        $section = ["IMPLEMENTATION REQUIREMENTS:"];

        if ($isCustom && $customPageInfo) {
            $cleanName = substr($pageName, 7);
            $section[] = "\nCustom Page: {$cleanName}";
            $section[] = "User Description: {$customPageInfo['description']}";
            $section[] = "";
            $section[] = "Based on the description, implement:";
            $section[] = "- Appropriate page structure and layout";
            $section[] = "- Relevant content and functionality";
            $section[] = "- Forms if data input is implied";
            $section[] = "- Tables/lists if data display is implied";
            $section[] = "- Charts if analytics are implied";
            $section[] = "- Actions if CRUD operations are implied";
        } else {
            $section[] = $this->getPageImplementationRequirements($pageName, $blueprint);
        }

        $section[] = "\nGeneral Requirements:";
        $section[] = "- Use the Layout component wrapper";
        $section[] = "- Apply theme colors consistently";
        $section[] = "- Implement responsive design";
        $section[] = "- Include loading and error states where appropriate";
        $section[] = "- Use realistic sample data (not Lorem ipsum)";

        return implode("\n", $section);
    }

    /**
     * IMPLEMENTATION: All pages (legacy)
     */
    private function buildImplementationInstructions(array $blueprint): string
    {
        $allPages = $this->getPageList($blueprint);

        $section = ["IMPLEMENTATION INSTRUCTIONS:"];
        $section[] = "Output Intent: Production-Ready Base";
        $section[] = "- Realistic sample content";
        $section[] = "- Proper error handling";
        $section[] = "- Accessibility: WCAG AA";
        $section[] = "- Form validation with user feedback";

        $section[] = "\nPage-Specific Requirements:";

        foreach ($allPages as $page) {
            $isCustom = str_starts_with($page, 'custom:');
            if ($isCustom) {
                $cleanName = substr($page, 7);
                $customPageInfo = $this->getCustomPageInfo($blueprint, $cleanName);
                $section[] = "\n" . ucfirst($cleanName) . " (Custom):";
                $section[] = "- " . ($customPageInfo['description'] ?? 'User-defined page');
            } else {
                $section[] = "\n" . $this->getPageImplementationRequirements($page, $blueprint);
            }
        }

        $section[] = "\nGeneral Requirements:";
        $section[] = "- All pages use the Layout component";
        $section[] = "- Navigation menu items correspond to included pages";
        $section[] = "- Active route indication in navigation";
        $section[] = "- Theme colors applied consistently";
        $section[] = "- Responsive breakpoints applied";

        return implode("\n", $section);
    }

    /**
     * Get implementation requirements for specific page type
     */
    private function getPageImplementationRequirements(string $page, array $blueprint): string
    {
        $requirements = [ucfirst($this->formatPageName($page)) . ":"];

        switch ($page) {
            case 'dashboard':
                $requirements[] = "- 4 metric cards (e.g., Total Users, Revenue, Orders, Growth %)";
                $requirements[] = "- Line chart showing trend (last 7 days)";
                $requirements[] = "- Recent activity table (5 rows)";
                $requirements[] = "- Quick actions section";
                break;

            case 'login':
                $requirements[] = "- Email and password inputs";
                $requirements[] = "- Remember me checkbox";
                $requirements[] = "- Submit button (disabled when invalid)";
                $requirements[] = "- Links to forgot password and register";
                $requirements[] = "- Client-side validation";
                break;

            case 'register':
                $requirements[] = "- Name, email, password, confirm password inputs";
                $requirements[] = "- Terms acceptance checkbox";
                $requirements[] = "- Submit button";
                $requirements[] = "- Link to login";
                $requirements[] = "- Password strength indicator";
                break;

            case 'forgot-password':
                $requirements[] = "- Email input field";
                $requirements[] = "- Submit button";
                $requirements[] = "- Success message on submission";
                $requirements[] = "- Link back to login";
                break;

            case 'user-management':
                $requirements[] = "- Data table with columns: Avatar, Name, Email, Role, Status, Actions";
                $requirements[] = "- Search/filter input";
                $requirements[] = "- Add user button";
                $requirements[] = "- Edit and delete actions per row";
                $requirements[] = "- Pagination (10 rows per page)";
                $requirements[] = "- Sample data: 15 users";
                break;

            case 'settings':
                $requirements[] = "- Tabs: Profile, Account, Notifications, Security";
                $requirements[] = "- Profile tab: Name, email, avatar upload";
                $requirements[] = "- Account tab: Language, timezone, theme";
                $requirements[] = "- Notifications tab: Email/push toggles";
                $requirements[] = "- Security tab: Change password, 2FA toggle";
                break;

            case 'charts':
                $chartLib = $blueprint['chartLibrary'] ?? 'chartjs';
                $chartName = $chartLib === 'echarts' ? 'Apache ECharts' : 'Chart.js';
                $requirements[] = "- Line chart: Revenue over time (12 months)";
                $requirements[] = "- Bar chart: Sales by category (6 categories)";
                $requirements[] = "- Doughnut chart: Traffic sources (4 sources)";
                $requirements[] = "- Use {$chartName} library";
                break;

            case 'tables':
                $requirements[] = "- Sortable columns (click header to sort)";
                $requirements[] = "- Filterable rows (search input)";
                $requirements[] = "- Selectable rows (checkbox column)";
                $requirements[] = "- Bulk actions (delete selected)";
                $requirements[] = "- Export button (CSV)";
                $requirements[] = "- Sample data: 25 rows";
                break;

            case 'profile':
                $requirements[] = "- User avatar (large, centered)";
                $requirements[] = "- User info: Name, email, role, joined date";
                $requirements[] = "- Edit profile button";
                $requirements[] = "- Activity timeline (last 10 actions)";
                $requirements[] = "- Stats cards: Posts, Followers, Following";
                break;

            case 'about':
                $requirements[] = "- Hero section with company mission";
                $requirements[] = "- Team section (4 team members)";
                $requirements[] = "- Values section (3 core values)";
                $requirements[] = "- Call-to-action section";
                break;

            case 'contact':
                $requirements[] = "- Contact form: Name, email, subject, message";
                $requirements[] = "- Contact info: Address, phone, email";
                $requirements[] = "- Map placeholder";
                $requirements[] = "- Social media links";
                break;

            default:
                $requirements[] = "- Basic page structure with title";
                $requirements[] = "- Content relevant to page name";
                break;
        }

        return implode("\n", $requirements);
    }

    // ========================================================================
    // Helper Methods
    // ========================================================================

    private function getFrameworkName(string $framework): string
    {
        return match($framework) {
            'tailwind' => 'Tailwind CSS',
            'bootstrap' => 'Bootstrap',
            'pure-css' => 'Pure CSS',
            default => ucfirst($framework),
        };
    }

    private function getOutputFormatName(string $format): string
    {
        return match($format) {
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
            return ucwords(str_replace('-', ' ', substr($page, 17))) . ' (Component Showcase)';
        }
        
        if (str_starts_with($page, 'component:')) {
            return ucwords(str_replace('-', ' ', substr($page, 10))) . ' (Component Showcase)';
        }
        
        if (str_starts_with($page, 'custom:')) {
            return ucwords(str_replace('-', ' ', substr($page, 7))) . ' (Custom)';
        }
        
        return ucwords(str_replace('-', ' ', $page));
    }

    private function formatNavigationName(string $navigation): string
    {
        return match($navigation) {
            'sidebar' => 'Collapsible Sidebar',
            'topbar' => 'Top Navigation Bar',
            'hybrid' => 'Hybrid (Sidebar + Top Bar)',
            default => ucfirst($navigation),
        };
    }

    private function formatResponsivenessName(string $responsiveness): string
    {
        return match($responsiveness) {
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
}
