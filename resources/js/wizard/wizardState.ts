/**
 * Wizard State Management
 *
 * Central reactive state for the 5-step template wizard.
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
 *
 * 5-Step Wizard Structure:
 * Step 1: Framework & Category (combined)
 * Step 2: Pages & Layout (combined)
 * Step 3: Theme & Styling (combined: theme + UI density + components)
 * Step 4: Responsiveness & Interactions (combined)
 * Step 5: Code Preferences & Output (combined)
 */

import { reactive, computed, ComputedRef } from 'vue';

/**
 * Step 1: Framework Selection
 */
export type Framework = 'tailwind' | 'bootstrap' | 'pure-css';

/**
 * Step 2: Template Category
 */
export type Category =
  | 'admin-dashboard'
  | 'company-profile'
  | 'landing-page'
  | 'saas-application'
  | 'blog-content-site'
  | 'e-commerce';

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
 * Step 10: Output Format
 */
export type OutputFormat = 'html-css' | 'react' | 'vue' | 'angular' | 'svelte';

/**
 * Step 11: LLM Model Selection
 */
export type LlmModel = 'gemini-flash' | 'gemini-pro' | 'gpt-4' | 'claude-3';
export type ModelTier = 'free' | 'premium';

/**
 * Complete wizard state interface
 */
export interface WizardState {
  // Meta
  currentStep: number;

  // Step 1: Framework & Category
  framework: Framework;
  category: Category;

  // Step 2: Pages & Layout
  pages: Page[];
  layout: LayoutConfig;

  // Step 3: Theme & Styling (theme + UI + components)
  theme: ThemeConfig;
  ui: UiConfig;
  components: Component[];
  chartLibrary?: ChartLibrary; // Required if 'charts' in components

  // Step 4: Responsiveness & Interactions
  responsiveness: ResponsivenessType;
  interaction: InteractionLevel;

  // Step 5: Output Format & LLM Model
  outputFormat: OutputFormat;
  llmModel: LlmModel;
  modelTier: ModelTier;
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

  // Step 1: Framework & Category
  framework: 'tailwind',
  category: 'admin-dashboard',

  // Step 2: Pages & Layout
  pages: ['login', 'dashboard'],
  layout: {
    navigation: 'sidebar',
    sidebarDefaultState: 'expanded',
    breadcrumbs: true,
    footer: 'minimal',
  },

  // Step 3: Theme & Styling
  theme: {
    primary: '#3B82F6', // Blue-500
    secondary: '#6366F1', // Indigo-500
    mode: 'light',
    background: 'solid',
  },
  ui: {
    density: 'comfortable',
    borderRadius: 'rounded',
  },
  components: ['buttons', 'forms', 'cards', 'alerts'],
  chartLibrary: undefined,

  // Step 4: Responsiveness & Interactions
  responsiveness: 'fully-responsive',
  interaction: 'moderate',

  // Step 5: Output Format & LLM Model
  outputFormat: 'vue',
  llmModel: 'gemini-flash',
  modelTier: 'free',
});

// ========================================================================
// Computed Properties (Validation and Dependencies)
// ========================================================================

/**
 * Check if current step is valid
 *
 * Each step has specific validation rules:
 * - Step 1: Framework & Category (always valid, has defaults)
 * - Step 2: Pages & Layout (at least one page, valid layout)
 * - Step 3: Theme & Styling (valid colors, UI settings, at least one component)
 * - Step 4: Responsiveness & Interactions (always valid, has defaults)
 * - Step 5: Code Preferences & Output (always valid, has defaults)
 */
export const isCurrentStepValid: ComputedRef<boolean> = computed(() => {
  switch (wizardState.currentStep) {
    case 1:
      // Step 1: Framework & Category
      return (
        ['tailwind', 'bootstrap', 'pure-css'].includes(wizardState.framework) &&
        [
          'admin-dashboard',
          'company-profile',
          'landing-page',
          'saas-application',
          'blog-content-site',
          'e-commerce',
        ].includes(wizardState.category)
      );

    case 2:
      // Step 2: Pages & Layout
      // At least one page must be selected
      if (wizardState.pages.length === 0) return false;

      // Layout must have navigation, breadcrumbs, footer
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

    case 3:
      // Step 3: Theme & Styling (theme + UI + components)
      const hexPattern = /^#[0-9A-Fa-f]{6}$/;
      const { primary, secondary, mode, background } = wizardState.theme;
      const { density, borderRadius } = wizardState.ui;

      // Validate theme colors
      const validTheme =
        hexPattern.test(primary) &&
        hexPattern.test(secondary) &&
        ['light', 'dark'].includes(mode) &&
        ['solid', 'gradient'].includes(background);

      // Validate UI preferences
      const validUI =
        ['compact', 'comfortable', 'spacious'].includes(density) &&
        ['sharp', 'rounded'].includes(borderRadius);

      // Validate components
      if (wizardState.components.length === 0) return false;
      if (wizardState.components.includes('charts')) {
        const validChart = !!wizardState.chartLibrary && ['chartjs', 'echarts'].includes(wizardState.chartLibrary);
        return validTheme && validUI && validChart;
      }

      return validTheme && validUI;

    case 4:
      // Step 4: Responsiveness & Interactions
      return (
        ['desktop-first', 'mobile-first', 'fully-responsive'].includes(wizardState.responsiveness) &&
        ['static', 'moderate', 'rich'].includes(wizardState.interaction)
      );

    case 5:
      // Step 5: Output Format & LLM Model
      return (
        ['html-css', 'react', 'vue', 'angular', 'svelte'].includes(wizardState.outputFormat) &&
        ['gemini-flash', 'gemini-pro', 'gpt-4', 'claude-3'].includes(wizardState.llmModel) &&
        ['free', 'premium'].includes(wizardState.modelTier)
      );

    default:
      return false;
  }
});

/**
 * Check if wizard can proceed to next step
 */
export const canProceedToNext: ComputedRef<boolean> = computed(() => {
  return isCurrentStepValid.value && wizardState.currentStep < 5;
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
  return wizardState.currentStep === 5;
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
    outputFormat: wizardState.outputFormat,
    llmModel: wizardState.llmModel,
    modelTier: wizardState.modelTier,
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
  if (step >= 1 && step <= 5) {
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
  wizardState.outputFormat = 'vue';
  wizardState.llmModel = 'gemini-flash';
  wizardState.modelTier = 'free';
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
  if (blueprint.outputFormat) wizardState.outputFormat = blueprint.outputFormat;
  if (blueprint.llmModel) wizardState.llmModel = blueprint.llmModel;
  if (blueprint.modelTier) wizardState.modelTier = blueprint.modelTier;
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
  if (framework === 'tailwind') return 'Tailwind CSS';
  if (framework === 'bootstrap') return 'Bootstrap';
  return 'Pure CSS';
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
    'e-commerce': 'E-Commerce',
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
    1: 'Choose your CSS framework and template category',
    2: 'Select pages and configure layout structure',
    3: 'Define theme, UI density, and components',
    4: 'Configure responsiveness and interaction level',
    5: 'Set code preferences and output intent',
  };
  return descriptions[step] || '';
}

/**
 * Get step title
 */
export function getStepTitle(step: number): string {
  const titles: Record<number, string> = {
    1: 'Framework & Category',
    2: 'Pages & Layout',
    3: 'Theme & Styling',
    4: 'Responsiveness & Interactions',
    5: 'Code Preferences & Output',
  };
  return titles[step] || '';
}
