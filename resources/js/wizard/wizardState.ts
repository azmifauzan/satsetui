/**
 * Wizard State Management
 *
 * Central reactive state for the 11-step template wizard.
 * This state is the source of truth for all wizard selections.
 *
 * IMPORTANT:
 * - This structure MUST match /app/Blueprints/template-blueprint.schema.json
 * - Any changes here require corresponding schema updates
 * - State is client-side only until final submission
 * - No external state management library (Vue's reactivity is sufficient)
 *
 * Architecture Role:
 * Wizard UI → [wizardState] → Blueprint JSON → Backend
 */

import { reactive, computed, ComputedRef } from 'vue';

/**
 * Step 1: Framework Selection
 */
export type Framework = 'tailwind' | 'bootstrap';

/**
 * Step 2: Template Category
 */
export type Category =
  | 'admin-dashboard'
  | 'company-profile'
  | 'landing-page'
  | 'saas-application'
  | 'blog-content-site';

/**
 * Step 3: Page Selection (multi-select)
 */
export type Page =
  | 'login'
  | 'register'
  | 'forgot-password'
  | 'dashboard'
  | 'user-management'
  | 'settings'
  | 'charts'
  | 'tables'
  | 'profile'
  | 'about'
  | 'contact';

/**
 * Step 4: Layout & Navigation
 */
export type NavigationType = 'sidebar' | 'topbar' | 'hybrid';
export type SidebarState = 'collapsed' | 'expanded';
export type FooterStyle = 'minimal' | 'full';

export interface LayoutConfig {
  navigation: NavigationType;
  sidebarDefaultState?: SidebarState; // Only applicable for sidebar/hybrid
  breadcrumbs: boolean;
  footer: FooterStyle;
}

/**
 * Step 5: Theme & Visual Identity
 */
export type ThemeMode = 'light' | 'dark';
export type BackgroundStyle = 'solid' | 'gradient';

export interface ThemeConfig {
  primary: string; // Hex color
  secondary: string; // Hex color
  mode: ThemeMode;
  background: BackgroundStyle;
}

/**
 * Step 6: UI Density & Style
 */
export type UiDensity = 'compact' | 'comfortable' | 'spacious';
export type BorderRadius = 'sharp' | 'rounded';

export interface UiConfig {
  density: UiDensity;
  borderRadius: BorderRadius;
}

/**
 * Step 7: Components (multi-select)
 */
export type Component =
  | 'buttons'
  | 'forms'
  | 'modals'
  | 'dropdowns'
  | 'alerts'
  | 'cards'
  | 'tabs'
  | 'charts';

export type ChartLibrary = 'chartjs' | 'echarts';

/**
 * Step 8: Interaction Level
 */
export type InteractionLevel = 'static' | 'moderate' | 'rich';

/**
 * Step 9: Responsiveness
 */
export type ResponsivenessType = 'desktop-first' | 'mobile-first' | 'fully-responsive';

/**
 * Step 10: Code Preferences
 */
export type CodeStyle = 'minimal' | 'verbose' | 'documented';

/**
 * Step 11: Output Intent
 */
export type OutputIntent = 'mvp' | 'production' | 'design-system';

/**
 * Complete wizard state interface
 */
export interface WizardState {
  // Meta
  currentStep: number;

  // Step 1: Framework
  framework: Framework;

  // Step 2: Category
  category: Category;

  // Step 3: Pages
  pages: Page[];

  // Step 4: Layout
  layout: LayoutConfig;

  // Step 5: Theme
  theme: ThemeConfig;

  // Step 6: UI
  ui: UiConfig;

  // Step 7: Components
  components: Component[];
  chartLibrary?: ChartLibrary; // Required if 'charts' in components

  // Step 8: Interaction
  interaction: InteractionLevel;

  // Step 9: Responsiveness
  responsiveness: ResponsivenessType;

  // Step 10: Code Style
  codeStyle: CodeStyle;

  // Step 11: Output Intent
  outputIntent: OutputIntent;
}

/**
 * Default wizard state
 *
 * These defaults represent the most common use case:
 * - Tailwind CSS (popular, utility-first)
 * - Admin Dashboard (most requested category)
 * - Essential pages (login, dashboard)
 * - Sidebar navigation (standard for dashboards)
 * - Light mode with blue theme (neutral, professional)
 * - Comfortable density (balanced)
 * - Moderate interactions (not too plain, not too flashy)
 * - Fully responsive (covers all devices)
 * - Minimal code style (experienced developers)
 * - Production-ready output (robust)
 */
export const wizardState = reactive<WizardState>({
  currentStep: 1,

  // Step 1
  framework: 'tailwind',

  // Step 2
  category: 'admin-dashboard',

  // Step 3
  pages: ['login', 'dashboard'],

  // Step 4
  layout: {
    navigation: 'sidebar',
    sidebarDefaultState: 'expanded',
    breadcrumbs: true,
    footer: 'minimal',
  },

  // Step 5
  theme: {
    primary: '#3B82F6', // Blue-500
    secondary: '#6366F1', // Indigo-500
    mode: 'light',
    background: 'solid',
  },

  // Step 6
  ui: {
    density: 'comfortable',
    borderRadius: 'rounded',
  },

  // Step 7
  components: ['buttons', 'forms', 'cards', 'alerts'],
  chartLibrary: undefined,

  // Step 8
  interaction: 'moderate',

  // Step 9
  responsiveness: 'fully-responsive',

  // Step 10
  codeStyle: 'minimal',

  // Step 11
  outputIntent: 'production',
});

// ========================================================================
// Computed Properties (Validation and Dependencies)
// ========================================================================

/**
 * Check if current step is valid
 *
 * Each step has specific validation rules:
 * - Step 1: Framework must be selected (always valid, has default)
 * - Step 2: Category must be selected (always valid, has default)
 * - Step 3: At least one page must be selected
 * - Step 4: Layout must have all required fields
 * - Step 5: Theme colors must be valid hex codes
 * - Step 6: UI preferences must be set
 * - Step 7: At least one component must be selected
 * - Step 8-11: Always valid (single-select enums)
 */
export const isCurrentStepValid: ComputedRef<boolean> = computed(() => {
  switch (wizardState.currentStep) {
    case 1:
      // Framework must be one of the enum values
      return ['tailwind', 'bootstrap'].includes(wizardState.framework);

    case 2:
      // Category must be one of the enum values
      return [
        'admin-dashboard',
        'company-profile',
        'landing-page',
        'saas-application',
        'blog-content-site',
      ].includes(wizardState.category);

    case 3:
      // At least one page must be selected
      return wizardState.pages.length > 0;

    case 4:
      // Layout must have navigation, breadcrumbs, footer
      // If sidebar/hybrid, must have sidebarDefaultState
      const { navigation, sidebarDefaultState, breadcrumbs, footer } = wizardState.layout;
      const hasNavigation = ['sidebar', 'topbar', 'hybrid'].includes(navigation);
      const hasFooter = ['minimal', 'full'].includes(footer);
      const hasBreadcrumbs = typeof breadcrumbs === 'boolean';

      if (navigation === 'sidebar' || navigation === 'hybrid') {
        return (
          hasNavigation &&
          hasFooter &&
          hasBreadcrumbs &&
          !!sidebarDefaultState &&
          ['collapsed', 'expanded'].includes(sidebarDefaultState)
        );
      }

      return hasNavigation && hasFooter && hasBreadcrumbs;

    case 5:
      // Theme colors must be valid hex codes
      const hexPattern = /^#[0-9A-Fa-f]{6}$/;
      const { primary, secondary, mode, background } = wizardState.theme;
      return (
        hexPattern.test(primary) &&
        hexPattern.test(secondary) &&
        ['light', 'dark'].includes(mode) &&
        ['solid', 'gradient'].includes(background)
      );

    case 6:
      // UI preferences must be valid enum values
      const { density, borderRadius } = wizardState.ui;
      return (
        ['compact', 'comfortable', 'spacious'].includes(density) &&
        ['sharp', 'rounded'].includes(borderRadius)
      );

    case 7:
      // At least one component must be selected
      if (wizardState.components.length === 0) return false;
      if (wizardState.components.includes('charts')) {
        return !!wizardState.chartLibrary && ['chartjs', 'echarts'].includes(wizardState.chartLibrary);
      }
      return true;

    case 8:
      // Interaction must be valid enum
      return ['static', 'moderate', 'rich'].includes(wizardState.interaction);

    case 9:
      // Responsiveness must be valid enum
      return ['desktop-first', 'mobile-first', 'fully-responsive'].includes(wizardState.responsiveness);

    case 10:
      // Code style must be valid enum
      return ['minimal', 'verbose', 'documented'].includes(wizardState.codeStyle);

    case 11:
      // Output intent must be valid enum
      return ['mvp', 'production', 'design-system'].includes(wizardState.outputIntent);

    default:
      return false;
  }
});

/**
 * Check if wizard can proceed to next step
 */
export const canProceedToNext: ComputedRef<boolean> = computed(() => {
  return isCurrentStepValid.value && wizardState.currentStep < 11;
});

/**
 * Check if wizard can go back to previous step
 */
export const canGoBack: ComputedRef<boolean> = computed(() => {
  return wizardState.currentStep > 1;
});

/**
 * Check if wizard is on final step
 */
export const isLastStep: ComputedRef<boolean> = computed(() => {
  return wizardState.currentStep === 11;
});

/**
 * Get suggested pages based on selected category
 *
 * This is a helper for Step 3 to show relevant default selections.
 * User can still freely choose any pages.
 *
 * Dependency: Step 2 (category)
 */
export const suggestedPages: ComputedRef<Page[]> = computed(() => {
  switch (wizardState.category) {
    case 'admin-dashboard':
      return ['login', 'dashboard', 'user-management', 'charts', 'tables', 'settings'];

    case 'company-profile':
      return ['about', 'contact'];

    case 'landing-page':
      return ['about', 'contact'];

    case 'saas-application':
      return ['login', 'register', 'dashboard', 'settings', 'profile'];

    case 'blog-content-site':
      return ['about', 'contact'];

    default:
      return ['dashboard'];
  }
});

/**
 * Check if sidebar state should be shown
 *
 * Dependency: Step 4 (layout.navigation)
 * Only show sidebar state option when navigation is 'sidebar' or 'hybrid'
 */
export const shouldShowSidebarState: ComputedRef<boolean> = computed(() => {
  return wizardState.layout.navigation === 'sidebar' || wizardState.layout.navigation === 'hybrid';
});

/**
 * Check if chart library should be shown
 *
 * Dependency: Step 7 (components array)
 * Only show chart library option when 'charts' is in components
 */
export const hasChartsEnabled: ComputedRef<boolean> = computed(() => {
  return wizardState.components.includes('charts');
});

export const shouldShowChartLibrary: ComputedRef<boolean> = computed(() => {
  return wizardState.components.includes('charts');
});

/**
 * Convert wizard state to Blueprint JSON
 *
 * This is the final serialization before submitting to backend.
 * Output must match template-blueprint.schema.json exactly.
 */
export const blueprintJSON: ComputedRef<object> = computed(() => {
  const blueprint: any = {
    framework: wizardState.framework,
    category: wizardState.category,
    pages: wizardState.pages,
    layout: {
      navigation: wizardState.layout.navigation,
      breadcrumbs: wizardState.layout.breadcrumbs,
      footer: wizardState.layout.footer,
    },
    theme: wizardState.theme,
    ui: wizardState.ui,
    components: wizardState.components,
    interaction: wizardState.interaction,
    responsiveness: wizardState.responsiveness,
    codeStyle: wizardState.codeStyle,
    outputIntent: wizardState.outputIntent,
  };

  // Conditionally add sidebarDefaultState
  if (
    wizardState.layout.navigation === 'sidebar' ||
    wizardState.layout.navigation === 'hybrid'
  ) {
    blueprint.layout.sidebarDefaultState = wizardState.layout.sidebarDefaultState;
  }

  // Conditionally add chartLibrary
  if (wizardState.components.includes('charts')) {
    blueprint.chartLibrary = wizardState.chartLibrary;
  }

  return blueprint;
});

// ========================================================================
// Actions (State Mutations)
// ========================================================================

/**
 * Navigate to next step
 *
 * Only proceeds if current step is valid.
 * Automatically handles conditional logic (e.g., setting chartLibrary when adding 'charts')
 */
export function nextStep(): void {
  if (canProceedToNext.value) {
    wizardState.currentStep++;
  }
}

/**
 * Navigate to previous step
 */
export function previousStep(): void {
  if (canGoBack.value) {
    wizardState.currentStep--;
  }
}

/**
 * Jump to specific step
 *
 * Used by step indicator or navigation.
 * Only allows jumping to completed steps or next step.
 */
export function goToStep(step: number): void {
  if (step >= 1 && step <= 11) {
    wizardState.currentStep = step;
  }
}

/**
 * Reset wizard to defaults
 *
 * Used when starting a new template from scratch.
 */
export function resetWizard(): void {
  wizardState.currentStep = 1;
  wizardState.framework = 'tailwind';
  wizardState.category = 'admin-dashboard';
  wizardState.pages = ['login', 'dashboard'];
  wizardState.layout = {
    navigation: 'sidebar',
    sidebarDefaultState: 'expanded',
    breadcrumbs: true,
    footer: 'minimal',
  };
  wizardState.theme = {
    primary: '#3B82F6',
    secondary: '#6366F1',
    mode: 'light',
    background: 'solid',
  };
  wizardState.ui = {
    density: 'comfortable',
    borderRadius: 'rounded',
  };
  wizardState.components = ['buttons', 'forms', 'cards', 'alerts'];
  wizardState.chartLibrary = undefined;
  wizardState.interaction = 'moderate';
  wizardState.responsiveness = 'fully-responsive';
  wizardState.codeStyle = 'minimal';
  wizardState.outputIntent = 'production';
}

/**
 * Load wizard state from existing blueprint
 *
 * Used when editing a saved template or loading from URL.
 *
 * @param blueprint Blueprint JSON object
 */
export function loadFromBlueprint(blueprint: Partial<WizardState>): void {
  if (blueprint.framework) wizardState.framework = blueprint.framework;
  if (blueprint.category) wizardState.category = blueprint.category;
  if (blueprint.pages) wizardState.pages = blueprint.pages;
  if (blueprint.layout) wizardState.layout = { ...wizardState.layout, ...blueprint.layout };
  if (blueprint.theme) wizardState.theme = { ...wizardState.theme, ...blueprint.theme };
  if (blueprint.ui) wizardState.ui = { ...wizardState.ui, ...blueprint.ui };
  if (blueprint.components) wizardState.components = blueprint.components;
  if (blueprint.chartLibrary) wizardState.chartLibrary = blueprint.chartLibrary;
  if (blueprint.interaction) wizardState.interaction = blueprint.interaction;
  if (blueprint.responsiveness) wizardState.responsiveness = blueprint.responsiveness;
  if (blueprint.codeStyle) wizardState.codeStyle = blueprint.codeStyle;
  if (blueprint.outputIntent) wizardState.outputIntent = blueprint.outputIntent;
}

export function syncChartLibrary(): void {
  if (wizardState.components.includes('charts') && !wizardState.chartLibrary) {
    wizardState.chartLibrary = 'chartjs';
  } else if (!wizardState.components.includes('charts')) {
    wizardState.chartLibrary = undefined;
  }
}

/**
 * Auto-clear sidebar state when switching away from sidebar/hybrid
 *
 * Call this when layout.navigation changes in Step 4.
 */
export function syncSidebarState(): void {
  if (wizardState.layout.navigation === 'topbar') {
    wizardState.layout.sidebarDefaultState = undefined;
  } else if (
    !wizardState.layout.sidebarDefaultState &&
    (wizardState.layout.navigation === 'sidebar' || wizardState.layout.navigation === 'hybrid')
  ) {
    wizardState.layout.sidebarDefaultState = 'expanded'; // Set default
  }
}

// ========================================================================
// Utility Functions
// ========================================================================

/**
 * Get human-readable label for framework
 */
export function getFrameworkLabel(framework: Framework): string {
  return framework === 'tailwind' ? 'Tailwind CSS' : 'Bootstrap';
}

/**
 * Get human-readable label for category
 */
export function getCategoryLabel(category: Category): string {
  const labels: Record<Category, string> = {
    'admin-dashboard': 'Admin Dashboard',
    'company-profile': 'Company Profile',
    'landing-page': 'Landing Page',
    'saas-application': 'SaaS Application',
    'blog-content-site': 'Blog / Content Site',
  };
  return labels[category];
}

/**
 * Get human-readable label for page
 */
export function getPageLabel(page: Page): string {
  const labels: Record<Page, string> = {
    login: 'Login',
    register: 'Register',
    'forgot-password': 'Forgot Password',
    dashboard: 'Dashboard',
    'user-management': 'User Management',
    settings: 'Settings',
    charts: 'Charts / Analytics',
    tables: 'Tables / Data List',
    profile: 'Profile',
    about: 'About',
    contact: 'Contact',
  };
  return labels[page];
}

/**
 * Get description for each wizard step
 */
export function getStepDescription(step: number): string {
  const descriptions: Record<number, string> = {
    1: 'Choose your CSS framework foundation',
    2: 'Select the primary use case for your template',
    3: 'Pick the pages to include in your template',
    4: 'Configure navigation and layout structure',
    5: 'Define your color scheme and visual identity',
    6: 'Set spacing density and border styles',
    7: 'Select UI components to include',
    8: 'Choose your animation and interaction level',
    9: 'Define responsive design approach',
    10: 'Set code style and verbosity preferences',
    11: 'Choose output maturity level',
  };
  return descriptions[step] || '';
}

/**
 * Get step title
 */
export function getStepTitle(step: number): string {
  const titles: Record<number, string> = {
    1: 'Framework Selection',
    2: 'Template Category',
    3: 'Page Selection',
    4: 'Layout & Navigation',
    5: 'Theme & Visual Identity',
    6: 'UI Density & Style',
    7: 'Components',
    8: 'Interaction Level',
    9: 'Responsiveness',
    10: 'Code Preferences',
    11: 'Output Intent',
  };
  return titles[step] || '';
}
