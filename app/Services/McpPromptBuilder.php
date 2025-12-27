<?php

namespace App\Services;

/**
 * MCP Prompt Builder
 *
 * Core service responsible for translating Blueprint JSON into a deterministic
 * Model Context Prompt (MCP) for LLM consumption.
 *
 * CRITICAL PRINCIPLES:
 * - This service makes ZERO decisions. It translates only.
 * - Same Blueprint input MUST produce identical MCP output (pure function).
 * - No randomness, no external state, no creative interpretation.
 * - The LLM receives complete instructions with zero ambiguity.
 *
 * Architecture Role:
 * Wizard UI → Blueprint JSON → [McpPromptBuilder] → MCP String → LLM
 *
 * @see /docs/architecture.md for complete data flow
 * @see /app/Blueprints/template-blueprint.schema.json for input structure
 */
class McpPromptBuilder
{
    /**
     * Build deterministic MCP prompt from validated Blueprint
     *
     * @param array $blueprint Validated blueprint data matching schema
     * @return string Complete MCP prompt ready for LLM API
     */
    public function buildFromBlueprint(array $blueprint): string
    {
        // Assemble MCP sections in strict order
        // Each section is pure translation of blueprint fields
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
     * ROLE: Define LLM expertise and behavior
     *
     * Reasoning: Establishes the LLM's technical persona. The role must match
     * the framework choice since Tailwind and Bootstrap require different expertise.
     */
    private function buildRoleSection(array $blueprint): string
    {
        $framework = $blueprint['framework'];
        $frameworkName = $framework === 'tailwind' ? 'Tailwind CSS' : 'Bootstrap';

        // Different role definitions based on framework
        if ($framework === 'tailwind') {
            return "You are an expert Vue.js developer specializing in Tailwind CSS.\n\n" .
                   "You build utility-first, responsive interfaces using Tailwind's class system.\n" .
                   "You write clean, type-safe TypeScript with Vue 3 Composition API.\n" .
                   "You follow best practices: semantic HTML, accessibility, performance.";
        } else {
            return "You are an expert Vue.js developer specializing in Bootstrap.\n\n" .
                   "You build component-based, responsive interfaces using Bootstrap's grid and components.\n" .
                   "You write clean, type-safe TypeScript with Vue 3 Composition API.\n" .
                   "You follow best practices: semantic HTML, accessibility, performance.";
        }
    }

    /**
     * CONTEXT: Project overview from wizard selections
     *
     * Reasoning: Provides high-level understanding of what's being built.
     * Category influences component priorities and content patterns.
     */
    private function buildContextSection(array $blueprint): string
    {
        $category = $this->formatCategoryName($blueprint['category']);
        $pagesList = implode(', ', array_map([$this, 'formatPageName'], $blueprint['pages']));
        $pageCount = count($blueprint['pages']);

        return "PROJECT CONTEXT:\n" .
               "- Template Category: {$category}\n" .
               "- Target Pages: {$pagesList}\n" .
               "- Total Pages: {$pageCount}\n" .
               "- Framework: " . ($blueprint['framework'] === 'tailwind' ? 'Tailwind CSS' : 'Bootstrap');
    }

    /**
     * CONSTRAINTS: Hard technology boundaries
     *
     * Reasoning: Prevents LLM from using unsupported libraries or making decisions.
     * These constraints are non-negotiable and enforce deterministic output.
     */
    private function buildConstraintsSection(array $blueprint): string
    {
        $framework = $blueprint['framework'];
        $constraints = ["CONSTRAINTS (MUST FOLLOW):"];

        if ($framework === 'tailwind') {
            $constraints[] = "- Use ONLY Tailwind CSS utility classes (no custom CSS files)";
            $constraints[] = "- No inline styles (style attribute)";
            $constraints[] = "- Responsive breakpoints: sm:640px, md:768px, lg:1024px, xl:1280px, 2xl:1536px";
        } else {
            $constraints[] = "- Use ONLY Bootstrap classes (no custom CSS files)";
            $constraints[] = "- No inline styles (style attribute)";
            $constraints[] = "- Responsive breakpoints: xs:<576px, sm:≥576px, md:≥768px, lg:≥992px, xl:≥1200px";
        }

        // Charts constraint (if applicable)
        if (in_array('charts', $blueprint['components'])) {
            $chartLib = $blueprint['chartLibrary'] ?? 'chartjs';
            $chartName = $chartLib === 'echarts' ? 'Apache ECharts' : 'Chart.js';
            $constraints[] = "- Use ONLY {$chartName} for data visualizations";
            $constraints[] = "- No other chart libraries (including D3.js, Recharts, etc.)";
        }

        $constraints[] = "- No backend logic (frontend templates only)";
        $constraints[] = "- No state management libraries (use Vue's reactive state)";
        $constraints[] = "- All imports must be valid (no placeholders)";

        return implode("\n", $constraints);
    }

    /**
     * LAYOUT: Navigation structure and layout elements
     *
     * Reasoning: Layout is structural foundation. Navigation pattern affects
     * component hierarchy and responsive behavior.
     */
    private function buildLayoutSection(array $blueprint): string
    {
        $layout = $blueprint['layout'];
        $navigation = $layout['navigation'];
        $breadcrumbs = $layout['breadcrumbs'] ? 'Enabled' : 'Disabled';
        $footer = ucfirst($layout['footer']);

        $section = ["LAYOUT REQUIREMENTS:"];
        $section[] = "- Navigation Pattern: " . $this->formatNavigationName($navigation);

        // Sidebar-specific instructions
        if ($navigation === 'sidebar' || $navigation === 'hybrid') {
            $sidebarState = $layout['sidebarDefaultState'] ?? 'expanded';
            $section[] = "- Sidebar Default State: " . ucfirst($sidebarState);
            $section[] = "- Sidebar Behavior: Collapsible with toggle button";
            $section[] = "- Sidebar Width: 256px expanded, 64px collapsed";

            if ($navigation === 'hybrid') {
                $section[] = "- Top Bar: Fixed position, contains user menu and notifications";
                $section[] = "- Sidebar: Contains primary navigation menu";
            }
        }

        // Topbar-specific instructions
        if ($navigation === 'topbar') {
            $section[] = "- Top Bar: Fixed position, full width, contains all navigation";
            $section[] = "- Top Bar Height: 64px";
        }

        $section[] = "- Breadcrumbs: {$breadcrumbs} on all pages";
        $section[] = "- Footer: {$footer} style";

        if ($footer === 'Minimal') {
            $section[] = "  - Footer Content: Copyright notice only";
        } else {
            $section[] = "  - Footer Content: Multiple columns with links (About, Legal, Social)";
        }

        return implode("\n", $section);
    }

    /**
     * THEME: Color scheme and visual mode
     *
     * Reasoning: Theme colors must be translated to framework-specific implementations.
     * Tailwind uses CSS variables, Bootstrap uses SCSS variables.
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

        // Framework-specific implementation instructions
        if ($framework === 'tailwind') {
            $section[] = "\nTailwind Implementation:";
            $section[] = "- Define CSS variables in :root and .dark classes";
            $section[] = "- Use custom color classes: bg-primary, text-primary, border-primary";
            $section[] = "- Dark mode: Add dark: prefix for dark mode styles";

            if ($theme['mode'] === 'dark') {
                $section[] = "- Default Mode: Apply .dark class to <html> element";
            }

            if ($theme['background'] === 'gradient') {
                $section[] = "- Background: Subtle gradient from primary (10% opacity) to transparent";
            }
        } else {
            $section[] = "\nBootstrap Implementation:";
            $section[] = "- Override Bootstrap variables: \$primary, \$secondary";
            $section[] = "- Use Bootstrap color utilities: bg-primary, text-secondary, etc.";
            $section[] = "- Dark mode: Use data-bs-theme=\"dark\" attribute";

            if ($theme['background'] === 'gradient') {
                $section[] = "- Background: Bootstrap gradient utility bg-gradient";
            }
        }

        return implode("\n", $section);
    }

    /**
     * UI DENSITY: Spacing and sizing scale
     *
     * Reasoning: Density affects spacing, font sizes, and component dimensions.
     * Must translate to specific utility class choices.
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

        // Translate density to spacing scale
        if ($framework === 'tailwind') {
            $section[] = "\nTailwind Spacing:";

            switch ($density) {
                case 'compact':
                    $section[] = "- Container Padding: p-4";
                    $section[] = "- Card Padding: p-3";
                    $section[] = "- Element Spacing: space-y-2, gap-2";
                    $section[] = "- Font Size: text-sm (body), text-xs (secondary)";
                    break;
                case 'comfortable':
                    $section[] = "- Container Padding: p-6";
                    $section[] = "- Card Padding: p-4";
                    $section[] = "- Element Spacing: space-y-4, gap-4";
                    $section[] = "- Font Size: text-base (body), text-sm (secondary)";
                    break;
                case 'spacious':
                    $section[] = "- Container Padding: p-8";
                    $section[] = "- Card Padding: p-6";
                    $section[] = "- Element Spacing: space-y-6, gap-6";
                    $section[] = "- Font Size: text-lg (body), text-base (secondary)";
                    break;
            }

            $section[] = "\nBorder Radius:";
            if ($borderRadius === 'sharp') {
                $section[] = "- Cards: rounded-sm (2px)";
                $section[] = "- Buttons: rounded (4px)";
                $section[] = "- Inputs: rounded (4px)";
            } else {
                $section[] = "- Cards: rounded-lg (8px)";
                $section[] = "- Buttons: rounded-md (6px)";
                $section[] = "- Inputs: rounded-md (6px)";
            }
        } else {
            $section[] = "\nBootstrap Spacing:";

            switch ($density) {
                case 'compact':
                    $section[] = "- Container Padding: p-3";
                    $section[] = "- Card Padding: card-body p-2";
                    $section[] = "- Element Spacing: mb-2, g-2";
                    break;
                case 'comfortable':
                    $section[] = "- Container Padding: p-4";
                    $section[] = "- Card Padding: card-body (default)";
                    $section[] = "- Element Spacing: mb-3, g-3";
                    break;
                case 'spacious':
                    $section[] = "- Container Padding: p-5";
                    $section[] = "- Card Padding: card-body p-4";
                    $section[] = "- Element Spacing: mb-4, g-4";
                    break;
            }

            $section[] = "\nBorder Radius:";
            if ($borderRadius === 'sharp') {
                $section[] = "- Use rounded-0 or rounded-1 classes";
            } else {
                $section[] = "- Use default Bootstrap rounded classes";
            }
        }

        return implode("\n", $section);
    }

    /**
     * COMPONENTS: Required UI components
     *
     * Reasoning: Explicit list ensures all requested components are implemented.
     * Each component has specific requirements.
     */
    private function buildComponentsSection(array $blueprint): string
    {
        $components = $blueprint['components'];
        $framework = $blueprint['framework'];

        $section = ["COMPONENT REQUIREMENTS:"];

        foreach ($components as $component) {
            switch ($component) {
                case 'buttons':
                    $section[] = "- Buttons: Primary (filled), Secondary (outline), Destructive (red)";
                    $section[] = "  - Sizes: Small, Default, Large";
                    $section[] = "  - States: Default, Hover, Active, Disabled";
                    break;

                case 'forms':
                    $section[] = "- Forms: Text Input, Email Input, Password Input, Select, Checkbox, Radio, Textarea";
                    $section[] = "  - Include labels, placeholders, validation states (error, success)";
                    $section[] = "  - Accessible: Use proper for/id associations, aria-labels";
                    break;

                case 'modals':
                    $section[] = "- Modals: Center-screen overlay with backdrop";
                    $section[] = "  - Header with title and close button";
                    $section[] = "  - Body content area";
                    $section[] = "  - Footer with action buttons";
                    $section[] = "  - Close on backdrop click and ESC key";
                    break;

                case 'dropdowns':
                    $section[] = "- Dropdowns: Button-triggered menu";
                    $section[] = "  - Support menu items, dividers, icons";
                    $section[] = "  - Click outside to close";
                    break;

                case 'alerts':
                    $section[] = "- Alerts/Toasts: Success, Error, Warning, Info variants";
                    $section[] = "  - Dismissible with close button";
                    $section[] = "  - Auto-dismiss option (5 seconds)";
                    break;

                case 'cards':
                    $section[] = "- Cards: Header, Body, Footer sections";
                    $section[] = "  - Optional image support";
                    $section[] = "  - Flexible content layout";
                    break;

                case 'tabs':
                    $section[] = "- Tabs: Horizontal tab navigation";
                    $section[] = "  - Active state indication";
                    $section[] = "  - Content panels switch on click";
                    break;

                case 'charts':
                    $chartLib = $blueprint['chartLibrary'] ?? 'chartjs';
                    $chartName = $chartLib === 'echarts' ? 'Apache ECharts' : 'Chart.js';
                    $section[] = "- Charts: Data visualizations using {$chartName}";
                    $section[] = "  - Line Chart: Time series data";
                    $section[] = "  - Bar Chart: Categorical comparisons";
                    $section[] = "  - Doughnut/Pie Chart: Proportional data";
                    $section[] = "  - Responsive: Enable responsive mode";
                    $section[] = "  - Theme-aware: Colors match theme primary/secondary";
                    break;
            }
        }

        return implode("\n", $section);
    }

    /**
     * INTERACTION: Animation and transition level
     *
     * Reasoning: Interaction level dictates CSS transition usage and JS-based animations.
     */
    private function buildInteractionSection(array $blueprint): string
    {
        $interaction = $blueprint['interaction'];
        $framework = $blueprint['framework'];

        $section = ["INTERACTION LEVEL: " . ucfirst($interaction)];

        switch ($interaction) {
            case 'static':
                $section[] = "- No animations or transitions";
                $section[] = "- Instant state changes";
                $section[] = "- Minimal hover effects (color change only)";
                $section[] = "- Maximum performance priority";
                break;

            case 'moderate':
                $section[] = "- Smooth transitions: 150ms ease-in-out for interactive elements";
                $section[] = "- Hover effects: Background/text color shifts, opacity changes";
                $section[] = "- Focus states: Outline/ring on keyboard navigation";
                $section[] = "- No complex animations or parallax";

                if ($framework === 'tailwind') {
                    $section[] = "- Use Tailwind transition utilities: transition, duration-150, ease-in-out";
                    $section[] = "- Hover: hover:bg-primary-dark, hover:scale-105";
                } else {
                    $section[] = "- Use Bootstrap transition utilities";
                    $section[] = "- Custom transition CSS where needed";
                }
                break;

            case 'rich':
                $section[] = "- Rich animations: Fade in, slide, scale transforms";
                $section[] = "- Micro-interactions: Button press feedback, ripple effects";
                $section[] = "- Loading skeletons: Pulse animations for loading states";
                $section[] = "- Page transitions: Smooth navigation between views";
                $section[] = "- Scroll animations: Fade/slide in elements on scroll (intersection observer)";

                if ($framework === 'tailwind') {
                    $section[] = "- Use Tailwind animate utilities: animate-pulse, animate-bounce";
                    $section[] = "- Custom keyframe animations for complex effects";
                }
                break;
        }

        return implode("\n", $section);
    }

    /**
     * RESPONSIVENESS: Responsive design approach
     *
     * Reasoning: Affects breakpoint usage, mobile navigation patterns, and layout stacking.
     */
    private function buildResponsivenessSection(array $blueprint): string
    {
        $responsiveness = $blueprint['responsiveness'];
        $framework = $blueprint['framework'];

        $section = ["RESPONSIVENESS: " . $this->formatResponsivenessName($responsiveness)];

        switch ($responsiveness) {
            case 'desktop-first':
                $section[] = "- Design optimized for desktop (≥1024px)";
                $section[] = "- Scale down to tablet and mobile";
                $section[] = "- Mobile (<640px): Hamburger menu, stacked layout, hide non-essential elements";
                $section[] = "- Tablet (640-1024px): Simplified sidebar or off-canvas menu";
                $section[] = "- Test primarily on desktop resolutions";
                break;

            case 'mobile-first':
                $section[] = "- Design optimized for mobile (<640px)";
                $section[] = "- Scale up to tablet and desktop";
                $section[] = "- Mobile: Bottom navigation or hamburger menu";
                $section[] = "- Tablet: Enhanced navigation, multi-column where appropriate";
                $section[] = "- Desktop: Full sidebar or top navigation, maximum information density";
                $section[] = "- Test primarily on mobile devices";
                break;

            case 'fully-responsive':
                $section[] = "- Equal optimization for all screen sizes";
                $section[] = "- Mobile (<640px): Hamburger menu, stacked single column";
                $section[] = "- Tablet (640-1024px): Collapsible sidebar, 2-column grid";
                $section[] = "- Desktop (>1024px): Expanded sidebar, 3+ column grid";
                $section[] = "- Touch-friendly: Minimum 44px tap targets on mobile";
                $section[] = "- Test across all breakpoints";
                break;
        }

        // Navigation responsiveness
        $layout = $blueprint['layout'];
        if ($layout['navigation'] === 'sidebar' || $layout['navigation'] === 'hybrid') {
            $section[] = "\nSidebar Responsive Behavior:";
            $section[] = "- Mobile: Off-canvas drawer (overlay), swipe to close";
            $section[] = "- Tablet: Collapsible, toggle button visible";
            $section[] = "- Desktop: Always visible, can be collapsed to icon-only";
        }

        return implode("\n", $section);
    }

    /**
     * CODE STYLE: Verbosity and documentation level
     *
     * Reasoning: Affects variable naming, comment density, and code structure.
     * This is purely stylistic and doesn't change functionality.
     */
    private function buildCodeStyleSection(array $blueprint): string
    {
        $codeStyle = $blueprint['codeStyle'];

        $section = ["CODE STYLE: " . ucfirst($codeStyle)];

        switch ($codeStyle) {
            case 'minimal':
                $section[] = "- Concise variable names (e.g., user, isOpen, handleClick)";
                $section[] = "- No comments unless complex logic";
                $section[] = "- Use Vue Composition API with <script setup>";
                $section[] = "- Inline simple logic, extract only when reused";
                $section[] = "- TypeScript interfaces: Minimal properties, no JSDoc";
                break;

            case 'verbose':
                $section[] = "- Explicit variable names (e.g., currentUser, isModalOpen, handleButtonClick)";
                $section[] = "- Moderate comments for non-obvious logic";
                $section[] = "- Use Vue Composition API with <script setup>";
                $section[] = "- Extract helper functions for clarity";
                $section[] = "- TypeScript interfaces: Descriptive property names, optional JSDoc";
                break;

            case 'documented':
                $section[] = "- Highly descriptive variable names (e.g., authenticatedUser, isUserProfileModalOpen)";
                $section[] = "- Comments on all functions, complex logic, and component purposes";
                $section[] = "- Use Vue Composition API with <script setup>";
                $section[] = "- Extract and document all helper functions";
                $section[] = "- TypeScript interfaces: Full JSDoc with property descriptions";
                $section[] = "- Include usage examples in component comments";
                break;
        }

        return implode("\n", $section);
    }

    /**
     * OUTPUT FORMAT: File structure and naming conventions
     *
     * Reasoning: Defines expected deliverable structure. Ensures consistent organization.
     */
    private function buildOutputFormatSection(array $blueprint): string
    {
        $pages = $blueprint['pages'];
        $components = $blueprint['components'];

        $section = ["OUTPUT FORMAT:"];
        $section[] = "Generate the following file structure:";
        $section[] = "";
        $section[] = "src/";
        $section[] = "├── pages/";

        // List page files
        foreach ($pages as $page) {
            $pageName = $this->getPageComponentName($page);
            $section[] = "│   ├── {$pageName}.vue";
        }

        $section[] = "├── components/";

        // List common components
        $section[] = "│   ├── Layout.vue         (Main layout wrapper)";

        if (in_array('sidebar', [$blueprint['layout']['navigation'], 'hybrid'])) {
            $section[] = "│   ├── Sidebar.vue";
        }

        if (in_array('topbar', [$blueprint['layout']['navigation'], 'hybrid']) || $blueprint['layout']['navigation'] === 'hybrid') {
            $section[] = "│   ├── Topbar.vue";
        }

        // List component files based on selection
        foreach ($components as $component) {
            switch ($component) {
                case 'buttons':
                    $section[] = "│   ├── Button.vue";
                    break;
                case 'cards':
                    $section[] = "│   ├── Card.vue";
                    break;
                case 'modals':
                    $section[] = "│   ├── Modal.vue";
                    break;
                case 'dropdowns':
                    $section[] = "│   ├── Dropdown.vue";
                    break;
                case 'alerts':
                    $section[] = "│   ├── Alert.vue";
                    break;
                case 'tabs':
                    $section[] = "│   ├── Tabs.vue";
                    break;
            }
        }

        $section[] = "├── composables/";
        $section[] = "│   └── useTheme.ts       (Theme switching logic)";
        $section[] = "└── types/";
        $section[] = "    └── index.ts          (TypeScript interfaces)";

        $section[] = "\nFile Format Requirements:";
        $section[] = "- Start each file with a comment: // src/path/to/File.vue";
        $section[] = "- Use Vue 3 <script setup> syntax";
        $section[] = "- Include all necessary imports (no placeholders like 'import ...')";
        $section[] = "- Export components as default";
        $section[] = "- TypeScript: Explicit types for props, emits, refs";

        return implode("\n", $section);
    }

    /**
     * IMPLEMENTATION INSTRUCTIONS: Page-specific requirements
     *
     * Reasoning: Each page type has specific content and functionality expectations.
     * These instructions ensure pages are functional, not just placeholder shells.
     */
    private function buildImplementationInstructions(array $blueprint): string
    {
        $pages = $blueprint['pages'];
        $outputIntent = $blueprint['outputIntent'];

        $section = ["IMPLEMENTATION INSTRUCTIONS:"];

        // Output intent affects depth of implementation
        switch ($outputIntent) {
            case 'mvp':
                $section[] = "Output Intent: MVP-Ready Scaffold";
                $section[] = "- Placeholder content is acceptable";
                $section[] = "- Basic error handling (console.log)";
                $section[] = "- Accessibility: Basic (labels, alt text)";
                $section[] = "- Focus on speed and structure";
                break;

            case 'production':
                $section[] = "Output Intent: Production-Ready Base";
                $section[] = "- Realistic sample content";
                $section[] = "- Proper error handling (try-catch, error states)";
                $section[] = "- Accessibility: WCAG AA (ARIA labels, keyboard nav, focus management)";
                $section[] = "- Form validation with user feedback";
                break;

            case 'design-system':
                $section[] = "Output Intent: Design System Starter";
                $section[] = "- Component documentation (JSDoc)";
                $section[] = "- Variant examples (sizes, states)";
                $section[] = "- Accessibility: WCAG AA (comprehensive)";
                $section[] = "- Storybook-ready structure";
                break;
        }

        $section[] = "\nPage-Specific Requirements:";

        foreach ($pages as $page) {
            $section[] = "\n" . $this->getPageImplementationRequirements($page, $blueprint);
        }

        $section[] = "\nGeneral Requirements:";
        $section[] = "- All pages use the Layout component";
        $section[] = "- Navigation menu items correspond to included pages";
        $section[] = "- Active route indication in navigation";
        $section[] = "- Theme colors applied consistently across all pages";
        $section[] = "- Responsive breakpoints applied to all layouts";

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
                $requirements[] = "- Recent activity table (5 rows with timestamp, action, user)";
                $requirements[] = "- Quick actions section (buttons for common tasks)";
                break;

            case 'login':
                $requirements[] = "- Email and password inputs";
                $requirements[] = "- Remember me checkbox";
                $requirements[] = "- Submit button (disabled when invalid)";
                $requirements[] = "- Link to forgot password and register";
                $requirements[] = "- Client-side validation (email format, required fields)";
                break;

            case 'register':
                $requirements[] = "- Name, email, password, confirm password inputs";
                $requirements[] = "- Terms acceptance checkbox";
                $requirements[] = "- Submit button (disabled until valid)";
                $requirements[] = "- Link to login page";
                $requirements[] = "- Password strength indicator";
                break;

            case 'forgot-password':
                $requirements[] = "- Email input field";
                $requirements[] = "- Submit button";
                $requirements[] = "- Success message on submission";
                $requirements[] = "- Link back to login";
                break;

            case 'user-management':
                $requirements[] = "- Data table: Avatar, Name, Email, Role, Status, Actions columns";
                $requirements[] = "- Search/filter input";
                $requirements[] = "- Add user button (opens modal)";
                $requirements[] = "- Edit and delete actions per row";
                $requirements[] = "- Pagination (10 rows per page)";
                $requirements[] = "- Sample data: 15 users";
                break;

            case 'settings':
                $requirements[] = "- Tabs: Profile, Account, Notifications, Security";
                $requirements[] = "- Profile tab: Name, email, avatar upload";
                $requirements[] = "- Account tab: Language, timezone, theme preferences";
                $requirements[] = "- Notifications tab: Email/push notification toggles";
                $requirements[] = "- Security tab: Change password form, 2FA toggle";
                break;

            case 'charts':
                $chartLib = $blueprint['chartLibrary'] ?? 'chartjs';
                $chartName = $chartLib === 'echarts' ? 'Apache ECharts' : 'Chart.js';
                $requirements[] = "- Line chart: Revenue over time (12 months)";
                $requirements[] = "- Bar chart: Sales by product category (6 categories)";
                $requirements[] = "- Doughnut chart: Traffic sources (4 sources)";
                $requirements[] = "- Charts must be responsive and theme-aware";
                $requirements[] = "- Use {$chartName} library";
                break;

            case 'tables':
                $requirements[] = "- Sortable columns (click header to sort)";
                $requirements[] = "- Filterable rows (search input)";
                $requirements[] = "- Selectable rows (checkbox column)";
                $requirements[] = "- Bulk actions (delete selected)";
                $requirements[] = "- Export button (CSV download)";
                $requirements[] = "- Sample data: 25 rows";
                break;

            case 'profile':
                $requirements[] = "- User avatar (large, center-aligned)";
                $requirements[] = "- User info: Name, email, role, joined date";
                $requirements[] = "- Edit profile button";
                $requirements[] = "- Activity timeline (last 10 actions)";
                $requirements[] = "- Stats cards: Posts, Followers, Following";
                break;

            case 'about':
                $requirements[] = "- Hero section with company mission";
                $requirements[] = "- Team section (4 team members with photos)";
                $requirements[] = "- Values section (3 core values)";
                $requirements[] = "- Call-to-action section";
                break;

            case 'contact':
                $requirements[] = "- Contact form: Name, email, subject, message";
                $requirements[] = "- Contact info: Address, phone, email";
                $requirements[] = "- Map embed (placeholder image or iframe)";
                $requirements[] = "- Social media links";
                break;

            default:
                $requirements[] = "- Basic page structure with title";
                $requirements[] = "- Placeholder content";
                break;
        }

        return implode("\n", $requirements);
    }

    // ========================================================================
    // Helper Methods (Formatting and Translation)
    // ========================================================================

    private function formatCategoryName(string $category): string
    {
        return ucwords(str_replace('-', ' ', $category));
    }

    private function formatPageName(string $page): string
    {
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
        // Convert page slug to PascalCase component name
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $page)));
    }
}
