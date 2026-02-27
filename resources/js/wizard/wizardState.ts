/**
 * Wizard State Management
 *
 * Central reactive state for the 3-step template wizard.
 * This state is the source of truth for all wizard selections.
 *
 * IMPORTANT:
 * - This structure MUST match /app/Blueprints/template-blueprint.schema.json
 * - Any changes here require corresponding schema updates
 * - State is client-side only until final submission
 * - No external state management library (Vue's reactivity is sufficient)
 *
 * Architecture Role:
 * Wizard UI → [wizardState] → Blueprint JSON → Backend (per-page MCP) → LLM
 *
 * 3-Step Wizard Structure:
 * Step 1: Framework, Category & Output Format
 * Step 2: Visual Design & Content (pages, layout, theme, ui, components)
 * Step 3: LLM Model Selection
 *
 * Auto-Selected (Best Defaults - Not shown in wizard):
 * - responsiveness: 'fully-responsive'
 * - interaction: 'moderate'
 * - codeStyle: 'documented'
 *
 * Credit Calculation:
 * - Base credits are for MAX_BASE_PAGES pages and MAX_BASE_COMPONENTS components
 * - Additional pages/components incur extra credits
 * - Formula: CEIL(subtotal × (1 + errorMargin) × (1 + profitMargin))
 * - Margins are admin-configurable (default: 10% error, 5% profit)
 */

import { reactive, computed, ComputedRef } from 'vue';

// ========================================================================
// Credit Calculation Constants
// ========================================================================

/**
 * Maximum pages included in base credit cost
 * 
 * IMPORTANT: This includes all pages (regular + component showcase pages).
 * Example: 3 regular pages + 2 component showcase pages = 5 pages total.
 */
export const MAX_BASE_PAGES = 5;

/**
 * Maximum components included in base credit cost
 * 
 * DEPRECATED: Components are now showcase pages and counted in MAX_BASE_PAGES.
 * Kept for backward compatibility only.
 */
export const MAX_BASE_COMPONENTS = 6;

/**
 * Additional credits per extra page beyond MAX_BASE_PAGES
 * 
 * Applies to ALL pages including component showcase pages.
 */
export const CREDITS_PER_EXTRA_PAGE = 1;

/**
 * Additional credits per extra component beyond MAX_BASE_COMPONENTS
 * 
 * DEPRECATED: Components are now pages. This is not used in calculation.
 * Kept for backward compatibility only.
 */
export const CREDITS_PER_EXTRA_COMPONENT = 0.5;

/**
 * Default error margin percentage (admin-configurable)
 */
export const DEFAULT_ERROR_MARGIN = 0.10; // 10%

/**
 * Default profit margin percentage (admin-configurable)
 */
export const DEFAULT_PROFIT_MARGIN = 0.05; // 5%

// ========================================================================
// Type Definitions
// ========================================================================

/**
 * Step 1: CSS Framework Selection
 */
export type Framework = 'tailwind' | 'bootstrap' | 'pure-css';

/**
 * Step 1: Template Category (predefined or custom)
 */
export type PredefinedCategory =
  | 'admin-dashboard'
  | 'company-profile'
  | 'landing-page'
  | 'saas-application'
  | 'blog-content-site'
  | 'e-commerce'
  | 'mobile-apps'
  | 'dashboard';

export type Category = PredefinedCategory | 'custom';

/**
 * Step 1: Output Format (predefined or custom)
 */
export type PredefinedOutputFormat = 'html-css' | 'react' | 'vue' | 'angular' | 'svelte';
export type OutputFormat = PredefinedOutputFormat | 'custom';

/**
 * Step 2: Page Selection (multi-select, predefined or custom)
 */
export type PredefinedPage =
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

export type Page = PredefinedPage | string; // Allow custom pages

/**
 * Custom page definition
 */
export interface CustomPage {
  id: string;
  name: string;
  description: string;
}

/**
 * Custom navigation item definition
 */
export interface CustomNavItem {
  id: string;
  label: string;
  icon?: string;
  route: string;
}

/**
 * Project information for consistent branding
 */
export interface ProjectInfo {
  companyName?: string;
  companyDescription?: string;
  appName?: string;
  storeName?: string;
  storeDescription?: string;
  blogName?: string;
  blogTopic?: string;
}

/**
 * Step 2: Layout & Navigation Configuration
 */
export type NavigationType = 'sidebar' | 'topbar' | 'hybrid';
export type SidebarState = 'collapsed' | 'expanded';
export type FooterStyle = 'minimal' | 'full';

export interface LayoutConfig {
  navigation: NavigationType;
  sidebarDefaultState?: SidebarState; // Only applicable for sidebar/hybrid
  breadcrumbs: boolean;
  footer: FooterStyle;
  customNavItems: CustomNavItem[]; // Custom navigation items
}

/**
 * Step 2: Theme & Visual Identity Configuration
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
 * Step 2: UI Density & Style Configuration
 */
export type UiDensity = 'compact' | 'comfortable' | 'spacious';
export type BorderRadius = 'sharp' | 'rounded';

export interface UiConfig {
  density: UiDensity;
  borderRadius: BorderRadius;
}

/**
 * Step 2: Components (multi-select, predefined or custom)
 */
export type PredefinedComponent =
  | 'buttons'
  | 'forms'
  | 'modals'
  | 'dropdowns'
  | 'alerts'
  | 'cards'
  | 'tabs'
  | 'charts';

export type Component = PredefinedComponent | string; // Allow custom components

/**
 * Custom component definition
 */
export interface CustomComponent {
  id: string;
  name: string;
  description: string;
}

export type ChartLibrary = 'chartjs' | 'echarts';

/**
 * Framework Configuration (for JS framework outputs)
 *
 * Only relevant when outputFormat is react, vue, angular, or svelte.
 * Controls language, state management, routing, and build tool options.
 */
export type FrameworkLanguage = 'javascript' | 'typescript';
export type FrameworkStyling = 'tailwind' | 'bootstrap' | 'css-modules' | 'styled-components';
export type StateManagement = 'none' | 'zustand' | 'pinia' | 'redux' | 'ngrx' | 'svelte-store';
export type BuildTool = 'vite' | 'webpack' | 'turbopack';

export interface FrameworkConfig {
  language: FrameworkLanguage;
  styling: FrameworkStyling;
  router: boolean;
  stateManagement: StateManagement;
  buildTool: BuildTool;
}

/**
 * Credit multiplier per output format.
 * Framework outputs are more complex and use more LLM tokens.
 */
export const FRAMEWORK_CREDIT_MULTIPLIER: Record<string, number> = {
  'html-css': 1.0,
  'react': 1.3,
  'vue': 1.3,
  'svelte': 1.2,
  'angular': 1.5,
  'custom': 1.0,
};

/**
 * State management options per framework
 */
export const STATE_MANAGEMENT_OPTIONS: Record<string, { value: StateManagement; label: string }[]> = {
  'react': [
    { value: 'none', label: 'None' },
    { value: 'zustand', label: 'Zustand' },
    { value: 'redux', label: 'Redux Toolkit' },
  ],
  'vue': [
    { value: 'none', label: 'None' },
    { value: 'pinia', label: 'Pinia' },
  ],
  'svelte': [
    { value: 'none', label: 'None' },
    { value: 'svelte-store', label: 'Svelte Stores' },
  ],
  'angular': [
    { value: 'none', label: 'None' },
    { value: 'ngrx', label: 'NgRx' },
  ],
};

/**
 * Compatible styling options per CSS framework.
 *
 * - Tailwind: All styling options available
 * - Bootstrap: All styling options available (via CDN or package)
 * - Pure CSS: Only CSS Modules and Styled Components (no CSS framework)
 */
export const COMPATIBLE_STYLING_OPTIONS: Record<Framework, FrameworkStyling[]> = {
  'tailwind': ['tailwind', 'bootstrap', 'css-modules', 'styled-components'],
  'bootstrap': ['bootstrap', 'tailwind', 'css-modules', 'styled-components'],
  'pure-css': ['css-modules', 'styled-components'],
};

/**
 * Default frameworkConfig.styling for each CSS framework selection.
 * Auto-applied when the user changes the CSS framework.
 */
export const DEFAULT_STYLING_FOR_FRAMEWORK: Record<Framework, FrameworkStyling> = {
  'tailwind': 'tailwind',
  'bootstrap': 'bootstrap',
  'pure-css': 'css-modules',
};

/**
 * Check if a styling option is compatible with the selected CSS framework.
 */
export function isStylingCompatible(framework: Framework, styling: FrameworkStyling): boolean {
  return (COMPATIBLE_STYLING_OPTIONS[framework] ?? []).includes(styling);
}

/**
 * Auto-Selected: Interaction Level (not shown in wizard)
 */
export type InteractionLevel = 'static' | 'moderate' | 'rich';

/**
 * Auto-Selected: Responsiveness (not shown in wizard)
 */
export type ResponsivenessType = 'desktop-first' | 'mobile-first' | 'fully-responsive';

/**
 * Auto-Selected: Code Style (not shown in wizard)
 */
export type CodeStyle = 'minimal' | 'verbose' | 'documented';

/**
 * Credit breakdown structure with margins
 */
export interface CreditBreakdown {
  baseCredits: number;       // Model base cost
  extraPageCredits: number;  // Additional page costs
  extraComponentCredits: number; // Additional component costs
  subtotal: number;          // Sum before margins
  errorMargin: number;       // Error margin percentage (from admin)
  profitMargin: number;      // Profit margin percentage (from admin)
  errorMarginAmount: number; // Calculated error margin amount
  profitMarginAmount: number; // Calculated profit margin amount
  total: number;             // Final total (ceiling)
}

/**
 * Wizard mode: satset (quick defaults) or expert (full customization)
 */
export type WizardMode = 'satset' | 'expert';

/**
 * Complete wizard state interface
 */
export interface WizardState {
  // Meta
  currentStep: number;
  wizardMode: WizardMode;

  // Step 1: Framework, Category & Output Format
  framework: Framework;
  category: Category;
  customCategoryName: string; // Custom category name when category = 'custom'
  customCategoryDescription: string; // Custom category description
  outputFormat: OutputFormat;
  customOutputFormat: string; // Custom output format description when outputFormat = 'custom'
  frameworkConfig: FrameworkConfig; // JS framework configuration (react/vue/svelte/angular)

  // Project Information (for consistent branding)
  projectInfo: ProjectInfo;

  // Step 2: Visual Design & Content
  pages: Page[];
  customPages: CustomPage[]; // User-added custom pages
  layout: LayoutConfig;
  theme: ThemeConfig;
  ui: UiConfig;
  components: Component[];
  customComponents: CustomComponent[]; // User-added custom components
  chartLibrary?: ChartLibrary; // Required if 'charts' in components

  // Step 3: LLM Model Selection
  llmModel: string; // Model type ('satset' or 'expert')
  modelCredits: number; // Base credits required for selected model
  templateName: string; // User-provided template name for easy identification

  // Auto-Selected Values (not shown in wizard UI)
  responsiveness: ResponsivenessType;
  interaction: InteractionLevel;
  codeStyle: CodeStyle;

  // Credit Calculation
  creditBreakdown: CreditBreakdown;
  calculatedCredits: number; // Final calculated credits including margins

  // Simplified Wizard (single-step) additions
  colorScheme: string;       // Color preset ID
  stylePreset: string;       // Style preset ID  
  fontFamily: string;        // Font family ID
  navStyle: string;          // Navigation style: top, sidebar, both
  themeMode: string;         // Generated output theme mode: light, dark, both
  logoFile: File | null;     // Optional logo upload
  customInstructions: string; // Optional custom text instructions
}

/**
 * Default wizard state
 *
 * These defaults represent the most common use case:
 * - Tailwind CSS (popular, utility-first)
 * - Admin Dashboard (most requested category)
 * - HTML + CSS (pure, no framework dependencies)
 * - Essential pages (login, dashboard)
 * - Sidebar navigation (standard for dashboards)
 * - Light mode with blue theme (neutral, professional)
 * - Comfortable density (balanced)
 * - Auto-selected: fully responsive, moderate interactions, documented code
 */
export const wizardState = reactive<WizardState>({
  currentStep: 1,
  wizardMode: 'satset',

  // Step 1: Framework, Category & Output Format
  framework: 'tailwind',
  category: 'admin-dashboard',
  customCategoryName: '',
  customCategoryDescription: '',
  outputFormat: 'html-css',
  customOutputFormat: '',
  frameworkConfig: {
    language: 'typescript',
    styling: 'tailwind',
    router: true,
    stateManagement: 'none',
    buildTool: 'vite',
  },

  // Project Information (for consistent branding)
  projectInfo: {
    companyName: '',
    companyDescription: '',
    appName: '',
    storeName: '',
    storeDescription: '',
    blogName: '',
    blogTopic: '',
  },

  // Step 2: Visual Design & Content
  pages: ['login', 'dashboard'],
  customPages: [],
  layout: {
    navigation: 'sidebar',
    sidebarDefaultState: 'expanded',
    breadcrumbs: true,
    footer: 'minimal',
    customNavItems: [],
  },
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
  components: [],
  customComponents: [],
  chartLibrary: undefined,

  // Step 3: LLM Model Selection
  llmModel: 'satset', // Default to Satset model (fast & affordable), can be changed to 'expert'
  modelCredits: 0,
  templateName: '', // User-provided name

  // Simplified Wizard additions
  colorScheme: 'blue',
  stylePreset: 'modern',
  fontFamily: 'inter',
  navStyle: 'top',
  themeMode: 'dark',
  logoFile: null,
  customInstructions: '',

  // Auto-Selected Values (best defaults, not shown to user)
  responsiveness: 'fully-responsive',
  interaction: 'moderate',
  codeStyle: 'documented',

  // Credit Calculation
  creditBreakdown: {
    baseCredits: 0,
    extraPageCredits: 0,
    extraComponentCredits: 0,
    subtotal: 0,
    errorMargin: DEFAULT_ERROR_MARGIN,
    profitMargin: DEFAULT_PROFIT_MARGIN,
    errorMarginAmount: 0,
    profitMarginAmount: 0,
    total: 0,
  },
  calculatedCredits: 0,
});

// ========================================================================
// Computed Properties (Validation and Dependencies)
// ========================================================================

/**
 * Calculate total number of pages (predefined + custom + component showcase pages)
 * 
 * Component showcase pages: Each selected component becomes a dedicated showcase page
 * (like AdminLTE UI Elements pages)
 */
export const totalPagesCount: ComputedRef<number> = computed(() => {
  const regularPages = wizardState.pages.length;
  const customPages = wizardState.customPages.length;
  const componentPages = wizardState.components.length; // Each component = 1 showcase page
  const customComponentPages = wizardState.customComponents.length; // Each custom component = 1 showcase page
  
  return regularPages + customPages + componentPages + customComponentPages;
});

/**
 * Calculate total number of components (predefined + custom)
 * NOTE: This is for display/reference only. Components are now counted as pages.
 */
export const totalComponentsCount: ComputedRef<number> = computed(() => {
  return wizardState.components.length + wizardState.customComponents.length;
});

/**
 * Calculate extra credits for pages beyond MAX_BASE_PAGES
 * 
 * IMPORTANT: Component showcase pages are included in totalPagesCount,
 * so they are automatically counted here as pages.
 */
export const extraPageCredits: ComputedRef<number> = computed(() => {
  const extraPages = Math.max(0, totalPagesCount.value - MAX_BASE_PAGES);
  return extraPages * CREDITS_PER_EXTRA_PAGE;
});

/**
 * Extra component credits - DEPRECATED
 * 
 * Components are now showcase pages, so they're counted in extraPageCredits.
 * Keeping this at 0 for backward compatibility.
 */
export const extraComponentCredits: ComputedRef<number> = computed(() => {
  return 0;
});

/**
 * Check if the current output format is a JS framework (not HTML+CSS)
 */
export const isFrameworkOutput: ComputedRef<boolean> = computed(() => {
  return ['react', 'vue', 'angular', 'svelte'].includes(wizardState.outputFormat);
});

/**
 * Get the credit multiplier for the current output format
 */
export const frameworkCreditMultiplier: ComputedRef<number> = computed(() => {
  return FRAMEWORK_CREDIT_MULTIPLIER[wizardState.outputFormat] ?? 1.0;
});

/**
 * Calculate subtotal (base + extras, before margins)
 * 
 * Since components are now pages, we only use extraPageCredits.
 * Framework output formats apply a credit multiplier.
 */
export const creditSubtotal: ComputedRef<number> = computed(() => {
  const base = wizardState.modelCredits + extraPageCredits.value;
  return base * frameworkCreditMultiplier.value;
});

/**
 * Calculate total credits with margins
 * Formula: CEIL(subtotal × (1 + errorMargin) × (1 + profitMargin))
 */
export const totalCalculatedCredits: ComputedRef<number> = computed(() => {
  const subtotal = creditSubtotal.value;
  const errorMargin = wizardState.creditBreakdown.errorMargin;
  const profitMargin = wizardState.creditBreakdown.profitMargin;
  
  return Math.ceil(subtotal * (1 + errorMargin) * (1 + profitMargin));
});

/**
 * Update credit breakdown with current calculations
 * 
 * Components are now counted as showcase pages, so extraComponentCredits is always 0.
 */
export function updateCreditBreakdown(): void {
  const baseCredits = wizardState.modelCredits;
  const extraPages = extraPageCredits.value;
  const extraComponents = 0; // Components are now pages
  const multiplier = frameworkCreditMultiplier.value;
  const subtotal = (baseCredits + extraPages) * multiplier;
  
  const errorMargin = wizardState.creditBreakdown.errorMargin;
  const profitMargin = wizardState.creditBreakdown.profitMargin;
  
  const afterError = subtotal * (1 + errorMargin);
  const errorMarginAmount = afterError - subtotal;
  const profitMarginAmount = afterError * profitMargin;
  const total = Math.ceil(afterError * (1 + profitMargin));
  
  wizardState.creditBreakdown = {
    baseCredits,
    extraPageCredits: extraPages,
    extraComponentCredits: extraComponents,
    subtotal,
    errorMargin,
    profitMargin,
    errorMarginAmount: Math.round(errorMarginAmount * 100) / 100,
    profitMarginAmount: Math.round(profitMarginAmount * 100) / 100,
    total,
  };
  
  wizardState.calculatedCredits = total;
}

/**
 * Set margins from admin settings
 */
export function setMargins(errorMargin: number, profitMargin: number): void {
  wizardState.creditBreakdown.errorMargin = errorMargin;
  wizardState.creditBreakdown.profitMargin = profitMargin;
  updateCreditBreakdown();
}

/**
 * Sync calculated credits with state (legacy support)
 */
export function syncCalculatedCredits(): void {
  updateCreditBreakdown();
}

/**
 * Check if current step is valid
 *
 * 3-Step Validation:
 * - Step 1: Framework, Category, Output Format (custom needs name/description)
 * - Step 2: Pages, Layout, Theme, UI, Components (at least 1 page, valid settings)
 * - Step 3: LLM Model (model must be selected)
 */
export const isCurrentStepValid: ComputedRef<boolean> = computed(() => {
  switch (wizardState.currentStep) {
    case 1:
      // Step 1: Framework, Category & Output Format
      const validFramework = ['tailwind', 'bootstrap', 'pure-css'].includes(wizardState.framework);
      const validCategory = [
        'admin-dashboard',
        'company-profile',
        'landing-page',
        'saas-application',
        'blog-content-site',
        'e-commerce',
        'mobile-apps',
        'dashboard',
        'custom',
      ].includes(wizardState.category);
      const validOutputFormat = [
        'html-css',
        'react',
        'vue',
        'angular',
        'svelte',
        'custom',
      ].includes(wizardState.outputFormat);

      // If custom category, name is required
      if (wizardState.category === 'custom') {
        if (wizardState.customCategoryName.trim().length < 3) return false;
      }

      // If custom output format, description is required
      if (wizardState.outputFormat === 'custom') {
        if (wizardState.customOutputFormat.trim().length < 5) return false;
      }

      return validFramework && validCategory && validOutputFormat;

    case 2:
      // Step 2: Visual Design & Content
      // At least one page must be selected (predefined or custom)
      const hasPages = wizardState.pages.length > 0 || wizardState.customPages.length > 0;
      if (!hasPages) return false;

      // Validate custom pages have required fields
      const validCustomPages = wizardState.customPages.every(
        p => p.name.trim().length >= 2 && p.description.trim().length >= 5
      );
      if (!validCustomPages) return false;

      // Layout validation
      const { navigation, sidebarDefaultState, breadcrumbs, footer } = wizardState.layout;
      const hasNavigation = ['sidebar', 'topbar', 'hybrid'].includes(navigation);
      const hasFooter = ['minimal', 'full'].includes(footer);
      const hasBreadcrumbs = typeof breadcrumbs === 'boolean';

      // Sidebar state required for sidebar/hybrid navigation
      if (navigation === 'sidebar' || navigation === 'hybrid') {
        const hasSidebarState = sidebarDefaultState && ['collapsed', 'expanded'].includes(sidebarDefaultState);
        if (!hasSidebarState) return false;
      }

      // Theme validation
      const { primary, secondary, mode, background } = wizardState.theme;
      const validPrimary = /^#[0-9A-Fa-f]{6}$/.test(primary);
      const validSecondary = /^#[0-9A-Fa-f]{6}$/.test(secondary);
      const validMode = ['light', 'dark'].includes(mode);
      const validBackground = ['solid', 'gradient'].includes(background);

      // UI validation
      const { density, borderRadius } = wizardState.ui;
      const validDensity = ['compact', 'comfortable', 'spacious'].includes(density);
      const validBorderRadius = ['sharp', 'rounded'].includes(borderRadius);

      // Components are optional (Custom Modifications section)
      // Validate custom components have required fields if any exist
      const validCustomComponents = wizardState.customComponents.every(
        c => c.name.trim().length >= 2 && c.description.trim().length >= 5
      );
      if (!validCustomComponents) return false;

      // Chart library required if charts component is selected
      if (wizardState.components.includes('charts')) {
        const validChartLibrary = wizardState.chartLibrary && ['chartjs', 'echarts'].includes(wizardState.chartLibrary);
        if (!validChartLibrary) return false;
      }

      return (
        hasNavigation &&
        hasFooter &&
        hasBreadcrumbs &&
        validPrimary &&
        validSecondary &&
        validMode &&
        validBackground &&
        validDensity &&
        validBorderRadius
      );

    case 3:
      // Step 3: LLM Model Selection
      // Model must be selected (non-empty string)
      return wizardState.llmModel.trim().length > 0;

    default:
      return false;
  }
});

/**
 * Check if navigation to next step is allowed
 */
export const canProceedToNext: ComputedRef<boolean> = computed(() => {
  return isCurrentStepValid.value && wizardState.currentStep < 3;
});

/**
 * Check if navigation to previous step is allowed
 */
export const canGoBack: ComputedRef<boolean> = computed(() => {
  return wizardState.currentStep > 1;
});

/**
 * Check if wizard can be submitted (all steps valid)
 */
export const canSubmit: ComputedRef<boolean> = computed(() => {
  // Must be on last step and step must be valid
  return wizardState.currentStep === 3 && isCurrentStepValid.value;
});

/**
 * Check if current step is the last step
 */
export const isLastStep: ComputedRef<boolean> = computed(() => {
  return wizardState.currentStep === 3;
});

/**
 * Get all selected pages as a flat array (for generation)
 */
export const allSelectedPages: ComputedRef<string[]> = computed(() => {
  const predefined = [...wizardState.pages];
  const custom = wizardState.customPages.map(p => `custom:${p.name}`);
  return [...predefined, ...custom];
});

/**
 * Get all selected components as a flat array (for generation)
 */
export const allSelectedComponents: ComputedRef<string[]> = computed(() => {
  const predefined = [...wizardState.components];
  const custom = wizardState.customComponents.map(c => `custom:${c.name}`);
  return [...predefined, ...custom];
});

/**
 * Generate blueprint JSON for submission
 * This is a reactive value that updates whenever the state changes
 */
export const blueprintJSON: ComputedRef<Record<string, unknown>> = computed(() => {
  return generateBlueprintJson();
});

/**
 * Check if sidebar state selection should be shown
 */
export const shouldShowSidebarState: ComputedRef<boolean> = computed(() => {
  return wizardState.layout.navigation === 'sidebar' || wizardState.layout.navigation === 'hybrid';
});

/**
 * Get suggested pages based on selected category
 */
export const suggestedPages: ComputedRef<PredefinedPage[]> = computed(() => {
  switch (wizardState.category) {
    case 'admin-dashboard':
      return ['login', 'dashboard', 'user-management', 'settings', 'charts', 'tables'];
    case 'company-profile':
      return ['about', 'contact', 'profile'];
    case 'landing-page':
      return ['about', 'contact'];
    case 'saas-application':
      return ['login', 'register', 'dashboard', 'settings', 'profile'];
    case 'blog-content-site':
      return ['about', 'contact', 'profile'];
    case 'e-commerce':
      return ['login', 'register', 'dashboard', 'profile', 'settings'];
    case 'custom':
      return ['login', 'dashboard']; // Default minimal set for custom category
    default:
      return ['login', 'dashboard'];
  }
});

/**
 * Check if chart library selection should be shown
 * Show only when charts component is selected
 */
export const shouldShowChartLibrary: ComputedRef<boolean> = computed(() => {
  return wizardState.components.includes('charts');
});

// ========================================================================
// Navigation Functions
// ========================================================================

/**
 * Go to next step (if valid)
 */
export function nextStep(): boolean {
  if (canProceedToNext.value) {
    wizardState.currentStep++;
    // Update credits when moving to step 3
    if (wizardState.currentStep === 3) {
      updateCreditBreakdown();
    }
    return true;
  }
  return false;
}

/**
 * Go to previous step
 */
export function previousStep(): boolean {
  if (canGoBack.value) {
    wizardState.currentStep--;
    return true;
  }
  return false;
}

/**
 * Go to specific step (if navigation rules allow)
 *
 * Rules:
 * - Can always go to steps 1-currentStep (already visited)
 * - Can only go forward if current step is valid
 * - Cannot skip steps
 */
export function goToStep(step: number): boolean {
  if (step < 1 || step > 3) return false;

  // Going backwards is always allowed
  if (step <= wizardState.currentStep) {
    wizardState.currentStep = step;
    return true;
  }

  // Going forward: can only go to next step if current is valid
  if (step === wizardState.currentStep + 1 && isCurrentStepValid.value) {
    wizardState.currentStep = step;
    if (step === 3) {
      updateCreditBreakdown();
    }
    return true;
  }

  return false;
}

// ========================================================================
// Reset Functions
// ========================================================================

/**
 * Reset wizard to initial state
 */
export function resetWizard(): void {
  wizardState.currentStep = 1;
  wizardState.wizardMode = 'satset';
  wizardState.framework = 'tailwind';
  wizardState.category = 'admin-dashboard';
  wizardState.customCategoryName = '';
  wizardState.customCategoryDescription = '';
  wizardState.outputFormat = 'vue';
  wizardState.customOutputFormat = '';
  wizardState.pages = ['login', 'dashboard'];
  wizardState.customPages = [];
  wizardState.layout = {
    navigation: 'sidebar',
    sidebarDefaultState: 'expanded',
    breadcrumbs: true,
    footer: 'minimal',
    customNavItems: [],
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
  wizardState.components = [];
  wizardState.customComponents = [];
  wizardState.chartLibrary = undefined;
  wizardState.llmModel = '';
  wizardState.modelCredits = 0;
  wizardState.responsiveness = 'fully-responsive';
  wizardState.interaction = 'moderate';
  wizardState.codeStyle = 'documented';
  wizardState.creditBreakdown = {
    baseCredits: 0,
    extraPageCredits: 0,
    extraComponentCredits: 0,
    subtotal: 0,
    errorMargin: DEFAULT_ERROR_MARGIN,
    profitMargin: DEFAULT_PROFIT_MARGIN,
    errorMarginAmount: 0,
    profitMarginAmount: 0,
    total: 0,
  };
  wizardState.calculatedCredits = 0;
}

/**
 * Load wizard state from existing blueprint
 *
 * Used when editing a saved template or loading from URL.
 *
 * @param blueprint Blueprint JSON object
 */
export function loadFromBlueprint(blueprint: Partial<WizardState>): void {
  if (blueprint.wizardMode) wizardState.wizardMode = blueprint.wizardMode;
  if (blueprint.framework) wizardState.framework = blueprint.framework;
  if (blueprint.category) wizardState.category = blueprint.category;
  if (blueprint.customCategoryName) wizardState.customCategoryName = blueprint.customCategoryName;
  if (blueprint.customCategoryDescription) wizardState.customCategoryDescription = blueprint.customCategoryDescription;
  if (blueprint.outputFormat) wizardState.outputFormat = blueprint.outputFormat;
  if (blueprint.customOutputFormat) wizardState.customOutputFormat = blueprint.customOutputFormat;
  if (blueprint.pages) wizardState.pages = blueprint.pages;
  if (blueprint.customPages) wizardState.customPages = blueprint.customPages;
  if (blueprint.layout) {
    wizardState.layout = {
      ...wizardState.layout,
      ...blueprint.layout,
      customNavItems: blueprint.layout.customNavItems || [],
    };
  }
  if (blueprint.theme) wizardState.theme = { ...wizardState.theme, ...blueprint.theme };
  if (blueprint.ui) wizardState.ui = { ...wizardState.ui, ...blueprint.ui };
  if (blueprint.components) wizardState.components = blueprint.components;
  if (blueprint.customComponents) wizardState.customComponents = blueprint.customComponents;
  if (blueprint.chartLibrary) wizardState.chartLibrary = blueprint.chartLibrary;
  if (blueprint.llmModel) wizardState.llmModel = blueprint.llmModel;
  if (blueprint.modelCredits !== undefined) wizardState.modelCredits = blueprint.modelCredits;
  if (blueprint.creditBreakdown) {
    wizardState.creditBreakdown = { ...wizardState.creditBreakdown, ...blueprint.creditBreakdown };
  }
  if (blueprint.calculatedCredits !== undefined) wizardState.calculatedCredits = blueprint.calculatedCredits;
  
  // Auto-selected values (apply defaults if not in blueprint)
  wizardState.responsiveness = blueprint.responsiveness || 'fully-responsive';
  wizardState.interaction = blueprint.interaction || 'moderate';
  wizardState.codeStyle = blueprint.codeStyle || 'documented';
}

// ========================================================================
// Sync Functions
// ========================================================================

/**
 * Sync chart library when charts component is toggled
 */
export function syncChartLibrary(): void {
  if (wizardState.components.includes('charts') && !wizardState.chartLibrary) {
    wizardState.chartLibrary = 'chartjs';
  } else if (!wizardState.components.includes('charts')) {
    wizardState.chartLibrary = undefined;
  }
}

/**
 * Auto-clear sidebar state when switching away from sidebar/hybrid
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
// Custom Item Management Functions
// ========================================================================

/**
 * Generate unique ID for custom items
 */
function generateId(): string {
  return `custom_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
}

/**
 * Add a custom page
 */
export function addCustomPage(name: string, description: string): void {
  wizardState.customPages.push({
    id: generateId(),
    name: name.trim(),
    description: description.trim(),
  });
  updateCreditBreakdown();
}

/**
 * Remove a custom page by ID
 */
export function removeCustomPage(id: string): void {
  const index = wizardState.customPages.findIndex(p => p.id === id);
  if (index > -1) {
    wizardState.customPages.splice(index, 1);
    updateCreditBreakdown();
  }
}

/**
 * Add a custom navigation item
 */
export function addCustomNavItem(label: string, route: string, icon?: string): void {
  wizardState.layout.customNavItems.push({
    id: generateId(),
    label: label.trim(),
    route: route.trim(),
    icon: icon?.trim(),
  });
}

/**
 * Remove a custom navigation item by ID
 */
export function removeCustomNavItem(id: string): void {
  const index = wizardState.layout.customNavItems.findIndex(n => n.id === id);
  if (index > -1) {
    wizardState.layout.customNavItems.splice(index, 1);
  }
}

/**
 * Add a custom component
 */
export function addCustomComponent(name: string, description: string): void {
  wizardState.customComponents.push({
    id: generateId(),
    name: name.trim(),
    description: description.trim(),
  });
  updateCreditBreakdown();
}

/**
 * Remove a custom component by ID
 */
export function removeCustomComponent(id: string): void {
  const index = wizardState.customComponents.findIndex(c => c.id === id);
  if (index > -1) {
    wizardState.customComponents.splice(index, 1);
    updateCreditBreakdown();
  }
}

// ========================================================================
// Mode Switching Functions
// ========================================================================

/**
 * Satset mode defaults per category.
 * In satset mode, everything is pre-configured with best defaults.
 */
const SATSET_CATEGORY_DEFAULTS: Record<string, { pages: string[]; navStyle: string; colorScheme: string; stylePreset: string; components: string[] }> = {
  'landing-page': {
    pages: ['home', 'about', 'features', 'pricing', 'contact'],
    navStyle: 'top',
    colorScheme: 'blue',
    stylePreset: 'modern',
    components: ['hero', 'features', 'testimonials', 'pricing', 'contact'],
  },
  'company-profile': {
    pages: ['home', 'about', 'services', 'team', 'contact'],
    navStyle: 'top',
    colorScheme: 'purple',
    stylePreset: 'elegant',
    components: ['hero', 'features', 'gallery', 'contact'],
  },
  'mobile-apps': {
    pages: ['login', 'home', 'profile', 'settings'],
    navStyle: 'top',
    colorScheme: 'blue',
    stylePreset: 'modern',
    components: ['cards', 'forms'],
  },
  'e-commerce': {
    pages: ['home', 'products', 'product-detail', 'cart', 'checkout', 'login'],
    navStyle: 'top',
    colorScheme: 'green',
    stylePreset: 'modern',
    components: ['cards', 'forms', 'gallery'],
  },
  'dashboard': {
    pages: ['login', 'dashboard', 'tables', 'charts', 'settings'],
    navStyle: 'sidebar',
    colorScheme: 'blue',
    stylePreset: 'modern',
    components: ['charts', 'tables', 'cards', 'forms'],
  },
  'blog-content-site': {
    pages: ['home', 'blog', 'post-detail', 'about', 'contact'],
    navStyle: 'top',
    colorScheme: 'amber',
    stylePreset: 'minimal',
    components: [],
  },
};

/**
 * Apply Satset mode defaults based on current category.
 * Resets all custom inputs and applies best defaults.
 */
export function applySatsetDefaults(): void {
  const cat = wizardState.category;
  const defaults = SATSET_CATEGORY_DEFAULTS[cat] || SATSET_CATEGORY_DEFAULTS['landing-page']!;

  // Framework & output
  wizardState.framework = 'tailwind';
  wizardState.outputFormat = 'html-css';
  wizardState.customCategoryName = '';
  wizardState.customCategoryDescription = '';
  wizardState.customOutputFormat = '';

  // Pages (category defaults only, no custom)
  wizardState.pages = [...defaults.pages];
  wizardState.customPages = [];

  // Theme defaults
  const colorMap: Record<string, { primary: string; secondary: string }> = {
    blue: { primary: '#3B82F6', secondary: '#6366F1' },
    green: { primary: '#10B981', secondary: '#059669' },
    purple: { primary: '#8B5CF6', secondary: '#7C3AED' },
    red: { primary: '#EF4444', secondary: '#DC2626' },
    amber: { primary: '#F59E0B', secondary: '#D97706' },
    slate: { primary: '#64748B', secondary: '#475569' },
  };
  wizardState.colorScheme = defaults.colorScheme;
  wizardState.theme.primary = colorMap[defaults.colorScheme]?.primary ?? '#3B82F6';
  wizardState.theme.secondary = colorMap[defaults.colorScheme]?.secondary ?? '#6366F1';
  wizardState.theme.mode = 'light';
  wizardState.theme.background = 'solid';
  wizardState.stylePreset = defaults.stylePreset;
  wizardState.fontFamily = 'inter';
  wizardState.navStyle = defaults.navStyle;
  wizardState.themeMode = 'dark';
  wizardState.logoFile = null;

  // Layout
  const navMapping: Record<string, NavigationType> = { top: 'topbar', sidebar: 'sidebar', both: 'hybrid' };
  wizardState.layout.navigation = navMapping[defaults.navStyle] ?? 'topbar';
  wizardState.layout.sidebarDefaultState = defaults.navStyle === 'sidebar' ? 'expanded' : undefined;
  wizardState.layout.breadcrumbs = true;
  wizardState.layout.footer = 'minimal';
  wizardState.layout.customNavItems = [];

  // UI
  wizardState.ui.density = 'comfortable';
  wizardState.ui.borderRadius = 'rounded';

  // Components: always empty in satset mode (no pre-selected components)
  wizardState.components = [];
  wizardState.customComponents = [];
  wizardState.chartLibrary = undefined;

  // Clear custom instructions
  wizardState.customInstructions = '';

  // Model defaults to 'satset'
  wizardState.llmModel = 'satset';

  // Framework config defaults
  wizardState.frameworkConfig = {
    language: 'typescript',
    styling: 'tailwind',
    router: true,
    stateManagement: 'none',
    buildTool: 'vite',
  };
}

/**
 * Switch wizard mode.
 * - Satset → Expert: keeps current state, reveals all options, sets model to 'expert'
 * - Expert → Satset: applies satset defaults for current category, sets model to 'satset'
 */
export function switchWizardMode(mode: WizardMode): void {
  if (wizardState.wizardMode === mode) return;

  wizardState.wizardMode = mode;

  if (mode === 'satset') {
    applySatsetDefaults();
  } else {
    // Switching to expert: clear components so all start unselected
    wizardState.components = [];
    wizardState.customComponents = [];
    wizardState.llmModel = 'expert';
  }
}

// ========================================================================
// Blueprint Export Functions
// ========================================================================

/**
 * Generate blueprint JSON from current wizard state
 *
 * This creates the complete blueprint structure to be sent to the backend.
 * Auto-selected values are included in the output.
 */
export function generateBlueprintJson(): Record<string, unknown> {
  return {
    // Wizard mode
    wizardMode: wizardState.wizardMode,

    // Step 1: Framework, Category & Output Format
    framework: wizardState.framework,
    category: wizardState.category,
    ...(wizardState.category === 'custom' && {
      customCategoryName: wizardState.customCategoryName,
      customCategoryDescription: wizardState.customCategoryDescription,
    }),
    outputFormat: wizardState.outputFormat,
    ...(wizardState.outputFormat === 'custom' && {
      customOutputFormat: wizardState.customOutputFormat,
    }),

    // Framework configuration (only for JS framework outputs)
    ...(isFrameworkOutput.value && {
      frameworkConfig: {
        language: wizardState.frameworkConfig.language,
        styling: wizardState.frameworkConfig.styling,
        router: wizardState.frameworkConfig.router,
        stateManagement: wizardState.frameworkConfig.stateManagement,
        buildTool: wizardState.frameworkConfig.buildTool,
      },
    }),

    // Project Information (for consistent branding across all pages)
    projectInfo: {
      ...(wizardState.projectInfo.companyName && { companyName: wizardState.projectInfo.companyName }),
      ...(wizardState.projectInfo.companyDescription && { companyDescription: wizardState.projectInfo.companyDescription }),
      ...(wizardState.projectInfo.appName && { appName: wizardState.projectInfo.appName }),
      ...(wizardState.projectInfo.storeName && { storeName: wizardState.projectInfo.storeName }),
      ...(wizardState.projectInfo.storeDescription && { storeDescription: wizardState.projectInfo.storeDescription }),
      ...(wizardState.projectInfo.blogName && { blogName: wizardState.projectInfo.blogName }),
      ...(wizardState.projectInfo.blogTopic && { blogTopic: wizardState.projectInfo.blogTopic }),
    },

    // Step 2: Visual Design & Content
    pages: wizardState.pages,
    ...(wizardState.customPages.length > 0 && {
      customPages: wizardState.customPages.map(p => ({
        name: p.name,
        description: p.description,
      })),
    }),
    layout: {
      navigation: wizardState.layout.navigation,
      ...(wizardState.layout.navigation !== 'topbar' && {
        sidebarDefaultState: wizardState.layout.sidebarDefaultState,
      }),
      breadcrumbs: wizardState.layout.breadcrumbs,
      footer: wizardState.layout.footer,
      ...(wizardState.layout.customNavItems.length > 0 && {
        customNavItems: wizardState.layout.customNavItems.map(n => ({
          label: n.label,
          route: n.route,
          ...(n.icon && { icon: n.icon }),
        })),
      }),
    },
    theme: {
      primary: wizardState.theme.primary,
      secondary: wizardState.theme.secondary,
      mode: wizardState.theme.mode,
      background: wizardState.theme.background,
    },
    ui: {
      density: wizardState.ui.density,
      borderRadius: wizardState.ui.borderRadius,
    },
    components: wizardState.components,
    ...(wizardState.customComponents.length > 0 && {
      customComponents: wizardState.customComponents.map(c => ({
        name: c.name,
        description: c.description,
      })),
    }),
    ...(wizardState.components.includes('charts') && {
      chartLibrary: wizardState.chartLibrary,
    }),

    // Step 3: LLM Model Selection
    llmModel: wizardState.llmModel,

    // Simplified wizard fields
    colorScheme: wizardState.colorScheme,
    stylePreset: wizardState.stylePreset,
    fontFamily: wizardState.fontFamily,
    navStyle: wizardState.navStyle,
    themeMode: wizardState.themeMode,
    ...(wizardState.customInstructions && {
      customInstructions: wizardState.customInstructions,
    }),

    // Auto-Selected Values (included in blueprint for LLM)
    autoSelected: {
      responsiveness: wizardState.responsiveness,
      interaction: wizardState.interaction,
      codeStyle: wizardState.codeStyle,
    },

    // Credit Breakdown (for tracking/billing)
    creditBreakdown: {
      baseCredits: wizardState.creditBreakdown.baseCredits,
      extraPageCredits: wizardState.creditBreakdown.extraPageCredits,
      extraComponentCredits: wizardState.creditBreakdown.extraComponentCredits,
      subtotal: wizardState.creditBreakdown.subtotal,
      errorMargin: wizardState.creditBreakdown.errorMargin,
      profitMargin: wizardState.creditBreakdown.profitMargin,
      total: wizardState.creditBreakdown.total,
    },
  };
}

// ========================================================================
// Utility Functions
// ========================================================================

/**
 * Get human-readable label for framework
 */
export function getFrameworkLabel(framework: Framework): string {
  const labels: Record<Framework, string> = {
    tailwind: 'Tailwind CSS',
    bootstrap: 'Bootstrap',
    'pure-css': 'Pure CSS',
  };
  return labels[framework] || framework;
}

/**
 * Get human-readable label for category
 */
export function getCategoryLabel(category: Category): string {
  if (category === 'custom') {
    return wizardState.customCategoryName || 'Custom';
  }
  const labels: Record<PredefinedCategory, string> = {
    'admin-dashboard': 'Admin Dashboard',
    'company-profile': 'Company Profile',
    'landing-page': 'Landing Page',
    'saas-application': 'SaaS Application',
    'blog-content-site': 'Blog / Content Site',
    'e-commerce': 'E-Commerce',
  };
  return labels[category as PredefinedCategory] || category;
}

/**
 * Get human-readable label for output format
 */
export function getOutputFormatLabel(format: OutputFormat): string {
  if (format === 'custom') {
    return wizardState.customOutputFormat || 'Custom';
  }
  const labels: Record<PredefinedOutputFormat, string> = {
    'html-css': 'HTML + CSS',
    react: 'React',
    vue: 'Vue.js',
    angular: 'Angular',
    svelte: 'Svelte',
  };
  return labels[format as PredefinedOutputFormat] || format;
}

/**
 * Get human-readable label for page
 */
export function getPageLabel(page: Page): string {
  // Check if it's a custom page
  if (page.startsWith('custom:')) {
    return page.replace('custom:', '');
  }
  const labels: Record<PredefinedPage, string> = {
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
  return labels[page as PredefinedPage] || page;
}

/**
 * Get step number by name (for programmatic navigation)
 */
export function getStepNumber(stepName: string): number {
  const steps: Record<string, number> = {
    'framework-category-output': 1,
    'visual-design-content': 2,
    'llm-model': 3,
  };
  return steps[stepName] || 1;
}

/**
 * Get human-readable step title by step number
 * Uses hard-coded defaults (i18n should be handled in components)
 */
export function getStepTitle(step: number): string {
  const titles: Record<number, string> = {
    1: 'Framework, Category & Output',
    2: 'Visual Design & Content',
    3: 'LLM Model Selection',
  };
  return titles[step] || '';
}
