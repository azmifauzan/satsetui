<script setup lang="ts">
import { wizardState, Framework, Category, PredefinedCategory, OutputFormat, PredefinedOutputFormat, isFrameworkOutput, frameworkCreditMultiplier, STATE_MANAGEMENT_OPTIONS, COMPATIBLE_STYLING_OPTIONS, DEFAULT_STYLING_FOR_FRAMEWORK, isStylingCompatible, type FrameworkLanguage, type FrameworkStyling, type StateManagement, type BuildTool } from '../wizardState';
import { useI18n } from '@/lib/i18n';
import { computed, ref, watch } from 'vue';

const { t } = useI18n();

// Custom category input refs
const showCustomCategoryInput = ref(false);

const frameworks = computed(() => [
  {
    value: 'tailwind' as Framework,
    label: t.value.wizard?.steps?.framework?.tailwind || 'Tailwind CSS',
    description: t.value.wizard?.steps?.framework?.tailwindDesc || 'Framework utility-first, sangat dapat disesuaikan, pendekatan modern',
  },
  {
    value: 'bootstrap' as Framework,
    label: t.value.wizard?.steps?.framework?.bootstrap || 'Bootstrap',
    description: t.value.wizard?.steps?.framework?.bootstrapDesc || 'Berbasis komponen, prototyping cepat, ekosistem luas',
  },
  {
    value: 'pure-css' as Framework,
    label: t.value.wizard?.steps?.framework?.pureCss || 'Pure CSS',
    description: t.value.wizard?.steps?.framework?.pureCssDesc || 'CSS murni, tanpa framework, kontrol penuh, ringan',
  },
]);

const predefinedCategories = computed(() => [
  {
    value: 'admin-dashboard' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.adminLabel || 'Admin Dashboard',
    description: t.value.wizard?.steps?.category?.adminDesc || 'Internal tools, data management, CRUD operations',
    icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  },
  {
    value: 'company-profile' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.companyLabel || 'Company Profile',
    description: t.value.wizard?.steps?.category?.companyDesc || 'Public-facing, content showcase, about/services pages',
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
  },
  {
    value: 'landing-page' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.landingLabel || 'Landing Page',
    description: t.value.wizard?.steps?.category?.landingDesc || 'Marketing-focused, conversion-optimized, hero sections',
    icon: 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
  },
  {
    value: 'saas-application' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.saasLabel || 'SaaS Application',
    description: t.value.wizard?.steps?.category?.saasDesc || 'User accounts, feature sections, pricing pages',
    icon: 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
  },
  {
    value: 'blog-content-site' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.blogLabel || 'Blog / Content Site',
    description: t.value.wizard?.steps?.category?.blogDesc || 'Article listings, reading experience, categories',
    icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
  },
  {
    value: 'e-commerce' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.ecommerceLabel || 'E-Commerce',
    description: t.value.wizard?.steps?.category?.ecommerceDesc || 'Product catalogs, shopping cart, checkout pages',
    icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
  },
]);

const predefinedOutputFormats = computed(() => [
  {
    value: 'html-css' as PredefinedOutputFormat,
    label: t.value.wizard?.steps?.outputFormat?.htmlCss || 'HTML + CSS',
    description: t.value.wizard?.steps?.outputFormat?.htmlCssDesc || 'Pure HTML dengan CSS murni, tanpa framework JS',
    icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
    isFramework: false,
  },
  {
    value: 'react' as PredefinedOutputFormat,
    label: t.value.wizard?.steps?.outputFormat?.react || 'React JS',
    description: t.value.wizard?.steps?.outputFormat?.reactDesc || 'React components dengan JSX dan hooks',
    icon: 'react',
    isFramework: true,
  },
  {
    value: 'vue' as PredefinedOutputFormat,
    label: t.value.wizard?.steps?.outputFormat?.vue || 'Vue.js',
    description: t.value.wizard?.steps?.outputFormat?.vueDesc || 'Vue 3 components dengan Composition API',
    icon: 'vue',
    isFramework: true,
  },
  {
    value: 'angular' as PredefinedOutputFormat,
    label: t.value.wizard?.steps?.outputFormat?.angular || 'Angular',
    description: t.value.wizard?.steps?.outputFormat?.angularDesc || 'Angular components dengan TypeScript',
    icon: 'angular',
    isFramework: true,
  },
  {
    value: 'svelte' as PredefinedOutputFormat,
    label: t.value.wizard?.steps?.outputFormat?.svelte || 'Svelte',
    description: t.value.wizard?.steps?.outputFormat?.svelteDesc || 'Svelte components dengan compile-time optimization',
    icon: 'svelte',
    isFramework: true,
  },
]);

function selectFramework(framework: Framework) {
  wizardState.framework = framework;
  // Auto-sync frameworkConfig.styling when CSS framework changes
  if (isFrameworkOutput.value) {
    const currentStyling = wizardState.frameworkConfig.styling;
    if (!isStylingCompatible(framework, currentStyling)) {
      wizardState.frameworkConfig.styling = DEFAULT_STYLING_FOR_FRAMEWORK[framework];
    }
  }
}

function selectCategory(category: Category) {
  wizardState.category = category;
  if (category === 'custom') {
    showCustomCategoryInput.value = true;
  } else {
    showCustomCategoryInput.value = false;
    wizardState.customCategoryName = '';
    wizardState.customCategoryDescription = '';
  }
}

function selectOutputFormat(format: OutputFormat) {
  wizardState.outputFormat = format;
  if (format !== 'custom') {
    wizardState.customOutputFormat = '';
  }
  // Reset framework config state management when switching frameworks
  if (['react', 'vue', 'angular', 'svelte'].includes(format)) {
    const options = STATE_MANAGEMENT_OPTIONS[format] ?? [];
    const currentValid = options.some(o => o.value === wizardState.frameworkConfig.stateManagement);
    if (!currentValid) {
      wizardState.frameworkConfig.stateManagement = 'none';
    }
    // Auto-sync styling from CSS framework on first framework output selection
    const currentStyling = wizardState.frameworkConfig.styling;
    if (!isStylingCompatible(wizardState.framework, currentStyling)) {
      wizardState.frameworkConfig.styling = DEFAULT_STYLING_FOR_FRAMEWORK[wizardState.framework];
    }
  }
}

// Framework config helpers
const currentStateManagementOptions = computed(() => {
  if (!isFrameworkOutput.value) return [];
  return STATE_MANAGEMENT_OPTIONS[wizardState.outputFormat] ?? [];
});

const stylingOptions = computed(() => {
  const fc = t.value.wizard?.steps?.outputFormat?.frameworkConfig;
  const compatible = COMPATIBLE_STYLING_OPTIONS[wizardState.framework] ?? ['tailwind', 'bootstrap', 'css-modules', 'styled-components'];
  const allOptions: { value: FrameworkStyling; label: string }[] = [
    { value: 'tailwind', label: fc?.tailwind || 'Tailwind CSS' },
    { value: 'bootstrap', label: fc?.bootstrap || 'Bootstrap' },
    { value: 'css-modules', label: fc?.cssModules || 'CSS Modules' },
    { value: 'styled-components', label: fc?.styledComponents || 'Styled Components' },
  ];
  return allOptions.filter(o => compatible.includes(o.value));
});

const buildToolOptions = computed(() => {
  const fc = t.value.wizard?.steps?.outputFormat?.frameworkConfig;
  return [
    { value: 'vite' as BuildTool, label: fc?.vite || 'Vite', description: fc?.viteDesc || 'Fast, modern, instant HMR' },
    { value: 'webpack' as BuildTool, label: fc?.webpack || 'Webpack', description: fc?.webpackDesc || 'Mature, flexible configuration' },
    { value: 'turbopack' as BuildTool, label: fc?.turbopack || 'Turbopack', description: fc?.turbopackDesc || 'Incremental bundler by Vercel' },
  ];
});

function setFrameworkLanguage(lang: FrameworkLanguage) {
  wizardState.frameworkConfig.language = lang;
}

function setFrameworkStyling(styling: FrameworkStyling) {
  wizardState.frameworkConfig.styling = styling;
}

function setStateManagement(sm: StateManagement) {
  wizardState.frameworkConfig.stateManagement = sm;
}

function toggleRouter() {
  wizardState.frameworkConfig.router = !wizardState.frameworkConfig.router;
}

function setBuildTool(tool: BuildTool) {
  wizardState.frameworkConfig.buildTool = tool;
}

// Watch for custom category selection
watch(() => wizardState.category, (newVal) => {
  showCustomCategoryInput.value = newVal === 'custom';
});

// Get framework logo component with proper SVG paths
function getFrameworkIcon(framework: string) {
  switch (framework) {
    case 'react':
      return {
        viewBox: '0 0 256 228',
        path: 'M210.483 73.824a171.49 171.49 0 0 0-8.24-2.597c.465-1.9.893-3.777 1.273-5.621 6.238-30.281 2.16-54.676-11.769-62.708-13.355-7.7-35.196.329-57.254 19.526a171.23 171.23 0 0 0-6.375 5.848 155.866 155.866 0 0 0-4.241-3.917C100.759 3.829 77.587-4.822 63.673 3.233 50.33 10.957 46.379 33.89 51.995 62.588a170.974 170.974 0 0 0 1.892 8.48c-3.28.932-6.445 1.924-9.474 2.98C17.309 83.498 0 98.307 0 113.668c0 15.865 18.582 31.778 46.812 41.427a145.52 145.52 0 0 0 6.921 2.165 167.467 167.467 0 0 0-2.01 9.138c-5.354 28.2-1.173 50.591 12.134 58.266 13.744 7.926 36.812-.22 59.273-19.855a145.567 145.567 0 0 0 5.342-4.923 168.064 168.064 0 0 0 6.92 6.314c21.758 18.722 43.246 26.282 56.54 18.586 13.731-7.949 18.194-32.003 12.4-61.268a145.016 145.016 0 0 0-1.535-6.842c1.62-.48 3.21-.985 4.77-1.518 28.924-9.871 48.432-25.586 48.432-42.048 0-15.576-17.881-30.362-45.996-40.002Zm-6.622 66.489c-1.357.421-2.738.828-4.143 1.22a174.31 174.31 0 0 0-9.166-22.664 174.19 174.19 0 0 0 8.632-22.106 157.31 157.31 0 0 1 12.19 3.888c13.323 4.891 22.5 11.568 22.5 18.673 0 7.623-10.024 15.074-30.013 21.009Zm-10.88 23.453c2.88 15.112.756 27.256-5.142 30.664-6.335 3.66-19.895-1.01-34.637-13.46a152.75 152.75 0 0 1-5.989-5.468 174.41 174.41 0 0 0 13.126-17.651 167.15 167.15 0 0 0 21.67-4.212c.77 3.316 1.452 6.696 2.057 10.127h-.024l-.06.001Zm-68.054 30.855c-9.283 9.283-18.05 15.692-25.147 18.793-6.137 2.68-10.712 2.777-13.354 1.245-5.656-3.27-8.165-16.096-5.052-33.617a152.06 152.06 0 0 1 1.744-7.921 174.664 174.664 0 0 0 22.007 3.737 172.477 172.477 0 0 0 13.236 17.209c-1.098 1.017-2.235 2.067-3.414 3.15l.033-.044-.053-.052Zm-34.134-59.054c-12.78-.086-24.044-1.49-32.863-4.1-7.633-2.259-13.14-5.23-15.53-8.222-2.132-2.66-2.633-5.106-1.516-7.62 2.387-5.376 14.049-10.866 30.252-14.44 3.037-.668 6.175-1.259 9.383-1.774a174.263 174.263 0 0 0 9.44 22.328 174.403 174.403 0 0 0-9.12 22.117c3.282-.552 6.627-1.028 10.016-1.431v-6.858h-.062Zm-20.984-45.29C47.577 72.96 41.39 59.37 42.62 48.03c1.094-10.096 6.326-15.912 14.286-19.502 8.533-3.847 19.832-2.816 31.807 3.508a152.41 152.41 0 0 1 6.44 3.882 174.46 174.46 0 0 0-12.939 17.518 176.67 176.67 0 0 0-21.878 4.182c-.472-2.014-.906-4.045-1.29-6.088l-.02-.105-.024.003Zm39.17 13.88a191.26 191.26 0 0 1 10.134-13.68 191.192 191.192 0 0 1 11.1 13.728 201.42 201.42 0 0 1-21.234-.048Zm42.58 54.63a191.75 191.75 0 0 1-10.435 13.936 191.99 191.99 0 0 1-21.402.107 191.46 191.46 0 0 1-10.788-13.62 194.074 194.074 0 0 1-5.46-10.14 193.92 193.92 0 0 1 5.336-10.268 191.47 191.47 0 0 1 10.755-13.767 191.99 191.99 0 0 1 21.38-.13 191.75 191.75 0 0 1 10.724 13.704 193.87 193.87 0 0 1 5.444 10.148 194.02 194.02 0 0 1-5.554 10.03Zm12.296-4.382a174.31 174.31 0 0 0-9.166 22.663 157.34 157.34 0 0 1-4.143-1.22c-19.989-5.936-30.013-13.387-30.013-21.008 0-7.105 9.177-13.782 22.5-18.674a157.28 157.28 0 0 1 12.189-3.887 174.19 174.19 0 0 0 8.633 22.126Zm-47.377 0a27.5 27.5 0 1 1 0-55 27.5 27.5 0 0 1 0 55Z'
      };
    case 'vue':
      return {
        viewBox: '0 0 256 221',
        path: 'M204.8 0H256L128 220.8 0 0h97.92L128 51.2 157.44 0h47.36Z M0 0l128 220.8L256 0h-51.2L128 132.48 51.2 0H0Z'
      };
    case 'angular':
      return {
        viewBox: '0 0 256 272',
        path: 'M.1 45.522L125.908.697l129.196 44.028-20.919 166.45-108.277 59.966-106.583-59.169L.1 45.522Z M128 26.432 128 243.1l-.048.027 83.932-46.565 16.466-131.47L128 26.432Z M128 26.432L47.382 65.39l14.79 118.153L128 231.59l.049-.027V26.432Z M128 86.107l-33.333 74.49h12.478l6.706-16.726h28.167l6.706 16.726h12.478L128 86.107Zm9.949 45.715h-19.9L128 106.625l9.949 25.197Z'
      };
    case 'svelte':
      return {
        viewBox: '0 0 256 308',
        path: 'M239.682 40.707C211.113-.182 154.69-12.301 113.895 13.69L42.247 59.356a82.198 82.198 0 0 0-37.135 55.056 86.566 86.566 0 0 0 8.536 55.576 82.425 82.425 0 0 0-12.296 30.719 87.596 87.596 0 0 0 14.964 66.244c28.574 40.893 84.997 53.007 125.787 27.016l71.648-45.664a82.182 82.182 0 0 0 37.135-55.057 86.601 86.601 0 0 0-8.53-55.577 82.409 82.409 0 0 0 12.29-30.718 87.573 87.573 0 0 0-14.963-66.244ZM106.89 270.841c-23.102 6.007-47.497-3.036-61.103-22.648a52.685 52.685 0 0 1-9.003-39.85 49.978 49.978 0 0 1 1.713-6.693l1.35-4.115 3.671 2.697a92.447 92.447 0 0 0 28.036 14.007l2.663.808-.245 2.659a16.067 16.067 0 0 0 2.89 10.656 17.143 17.143 0 0 0 18.397 6.828 15.786 15.786 0 0 0 4.403-1.935l71.67-45.672a14.922 14.922 0 0 0 6.734-9.977 15.923 15.923 0 0 0-2.713-12.011 17.156 17.156 0 0 0-18.404-6.832 15.78 15.78 0 0 0-4.396 1.933l-27.35 17.434a52.298 52.298 0 0 1-14.553 6.391c-23.101 6.007-47.497-3.036-61.101-22.649a52.681 52.681 0 0 1-9.004-39.849 49.428 49.428 0 0 1 22.34-33.114l71.664-45.677a52.218 52.218 0 0 1 14.563-6.398c23.101-6.007 47.497 3.036 61.101 22.648a52.685 52.685 0 0 1 9.004 39.85 50.559 50.559 0 0 1-1.713 6.692l-1.35 4.116-3.67-2.693a92.373 92.373 0 0 0-28.037-14.013l-2.664-.809.246-2.658a16.099 16.099 0 0 0-2.89-10.656 17.143 17.143 0 0 0-18.398-6.828 15.786 15.786 0 0 0-4.402 1.935l-71.67 45.674a14.898 14.898 0 0 0-6.73 9.975 15.9 15.9 0 0 0 2.709 12.012 17.156 17.156 0 0 0 18.404 6.832 15.841 15.841 0 0 0 4.402-1.935l27.345-17.427a52.147 52.147 0 0 1 14.552-6.397c23.101-6.006 47.497 3.037 61.102 22.65a52.681 52.681 0 0 1 9.003 39.848 49.453 49.453 0 0 1-22.34 33.12l-71.664 45.673a52.218 52.218 0 0 1-14.563 6.398Z'
      };
    default:
      return null;
  }
}
</script>

<template>
  <div class="space-y-10">
    <!-- Framework Selection -->
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        {{ t.wizard?.steps?.framework?.title }}
      </h2>
      <p class="text-slate-600 dark:text-slate-400 mb-6">
        {{ t.wizard?.steps?.framework?.description }}
      </p>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
          v-for="fw in frameworks"
          :key="fw.value"
          @click="selectFramework(fw.value)"
          :class="[
            'p-6 rounded-xl border-2 text-left transition-all hover:shadow-lg',
            wizardState.framework === fw.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-start justify-between mb-3">
            <h3 class="text-xl font-semibold text-slate-900 dark:text-white">
              {{ fw.label }}
            </h3>
            <div
              :class="[
                'w-6 h-6 rounded-full border-2 flex items-center justify-center',
                wizardState.framework === fw.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.framework === fw.value"
                class="w-4 h-4 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </div>
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            {{ fw.description }}
          </p>
        </button>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-slate-200 dark:border-slate-700"></div>

    <!-- Category Selection -->
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        {{ t.wizard?.steps?.category?.title }}
      </h2>
      <p class="text-slate-600 dark:text-slate-400 mb-6">
        {{ t.wizard?.steps?.category?.description }}
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Predefined Categories -->
        <button
          v-for="cat in predefinedCategories"
          :key="cat.value"
          @click="selectCategory(cat.value)"
          :class="[
            'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg',
            wizardState.category === cat.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="cat.icon" />
              </svg>
            </div>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                wizardState.category === cat.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.category === cat.value"
                class="w-3 h-3 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </div>
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
            {{ cat.label }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            {{ cat.description }}
          </p>
        </button>

        <!-- Custom Category Option -->
        <button
          @click="selectCategory('custom')"
          :class="[
            'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg border-dashed',
            wizardState.category === 'custom'
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                wizardState.category === 'custom'
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.category === 'custom'"
                class="w-3 h-3 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </div>
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
            {{ t.wizard?.steps?.category?.custom }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            {{ t.wizard?.steps?.category?.customDesc }}
          </p>
        </button>
      </div>

      <!-- Custom Category Input -->
      <div v-if="wizardState.category === 'custom'" class="mt-6 p-6 bg-slate-50 dark:bg-slate-800/50 rounded-xl border-2 border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
          {{ t.wizard?.steps?.category?.customInputTitle }}
        </h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t.wizard?.steps?.category?.customNameLabel }} *
            </label>
            <input
              v-model="wizardState.customCategoryName"
              type="text"
              :placeholder="t.wizard?.steps?.category?.customNamePlaceholder"
              class="w-full px-4 py-3 border-2 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 transition-all"
            />
            <p v-if="wizardState.customCategoryName.length > 0 && wizardState.customCategoryName.length < 3" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ t.wizard?.steps?.category?.customMinChars }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t.wizard?.steps?.category?.customDescLabel }}
            </label>
            <textarea
              v-model="wizardState.customCategoryDescription"
              rows="3"
              :placeholder="t.wizard?.steps?.category?.customDescPlaceholder"
              class="w-full px-4 py-3 border-2 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 transition-all resize-none"
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-slate-200 dark:border-slate-700"></div>

    <!-- Output Format Selection -->
    <div class="space-y-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.outputFormat?.title }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400">
          {{ t.wizard?.steps?.outputFormat?.description }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Predefined Output Formats -->
        <button
          v-for="format in predefinedOutputFormats"
          :key="format.value"
          @click="selectOutputFormat(format.value)"
          :class="[
            'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg',
            wizardState.outputFormat === format.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <!-- Framework-specific icons -->
              <svg v-if="format.isFramework && getFrameworkIcon(format.value)" 
                   class="w-6 h-6" 
                   :class="{
                     'text-blue-400': format.value === 'react',
                     'text-green-500': format.value === 'vue',
                     'text-red-600': format.value === 'angular',
                     'text-orange-500': format.value === 'svelte'
                   }"
                   fill="currentColor" 
                   :viewBox="getFrameworkIcon(format.value)?.viewBox">
                <path :d="getFrameworkIcon(format.value)?.path" />
              </svg>
              <!-- Generic icon for HTML/CSS -->
              <svg v-else class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="format.icon" />
              </svg>
            </div>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                wizardState.outputFormat === format.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.outputFormat === format.value"
                class="w-3 h-3 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </div>
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
            {{ format.label }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            {{ format.description }}
          </p>
        </button>

        <!-- Custom Output Format Option -->
        <button
          @click="selectOutputFormat('custom')"
          :class="[
            'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg',
            wizardState.outputFormat === 'custom'
              ? 'border-purple-600 bg-purple-50 dark:bg-purple-900/20'
              : 'border-dashed border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 hover:border-purple-400'
          ]"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                wizardState.outputFormat === 'custom'
                  ? 'border-purple-600 bg-purple-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.outputFormat === 'custom'"
                class="w-3 h-3 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </div>
          <h3 class="text-lg font-semibold text-purple-700 dark:text-purple-300 mb-2">
            {{ t.wizard?.steps?.outputFormat?.custom }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            {{ t.wizard?.steps?.outputFormat?.customDesc }}
          </p>
        </button>
      </div>

      <!-- Custom Output Format Input -->
      <div
        v-if="wizardState.outputFormat === 'custom'"
        class="p-5 rounded-xl border-2 border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20"
      >
        <h3 class="text-lg font-semibold text-purple-700 dark:text-purple-300 mb-3 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          {{ t.wizard?.steps?.outputFormat?.customInputTitle }}
        </h3>
        <textarea
          v-model="wizardState.customOutputFormat"
          :placeholder="t.wizard?.steps?.outputFormat?.customPlaceholder"
          class="w-full h-32 px-4 py-3 border border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none"
        ></textarea>
        <p class="mt-2 text-sm text-purple-600 dark:text-purple-400">
          {{ t.wizard?.steps?.outputFormat?.customHint }}
        </p>
      </div>

      <!-- Framework Configuration Panel -->
      <div
        v-if="isFrameworkOutput"
        class="p-6 rounded-xl border-2 border-blue-200 dark:border-blue-800 bg-blue-50/50 dark:bg-blue-900/10 space-y-6"
      >
        <div class="flex items-center gap-3 mb-1">
          <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.title }}
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.description }}
            </p>
          </div>
          <!-- Credit multiplier badge -->
          <div class="ml-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full"
                  :class="frameworkCreditMultiplier > 1 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              {{ frameworkCreditMultiplier }}x {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.creditMultiplier }}
            </span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Language -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.language }}
            </label>
            <div class="grid grid-cols-2 gap-3">
              <button
                @click="setFrameworkLanguage('typescript')"
                :class="[
                  'px-4 py-3 rounded-lg border-2 text-left transition-all text-sm',
                  wizardState.frameworkConfig.language === 'typescript'
                    ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                    : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
                ]"
              >
                <div class="font-semibold text-slate-900 dark:text-white">
                  {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.typescript }}
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.typescriptDesc }}
                </div>
              </button>
              <button
                @click="setFrameworkLanguage('javascript')"
                :class="[
                  'px-4 py-3 rounded-lg border-2 text-left transition-all text-sm',
                  wizardState.frameworkConfig.language === 'javascript'
                    ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                    : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
                ]"
              >
                <div class="font-semibold text-slate-900 dark:text-white">
                  {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.javascript }}
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.javascriptDesc }}
                </div>
              </button>
            </div>
          </div>

          <!-- Styling -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.styling }}
            </label>
            <div class="grid grid-cols-2 gap-3">
              <button
                v-for="style in stylingOptions"
                :key="style.value"
                @click="setFrameworkStyling(style.value)"
                :class="[
                  'px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all',
                  wizardState.frameworkConfig.styling === style.value
                    ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                    : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:border-blue-400'
                ]"
              >
                {{ style.label }}
              </button>
            </div>
            <!-- CSS framework sync info -->
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
              <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.stylingHint }}
            </p>
          </div>

          <!-- Router Toggle -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.router }}
            </label>
            <button
              @click="toggleRouter"
              :class="[
                'w-full px-4 py-3 rounded-lg border-2 text-left transition-all text-sm flex items-center justify-between',
                wizardState.frameworkConfig.router
                  ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                  : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800'
              ]"
            >
              <span class="text-slate-700 dark:text-slate-300">
                {{ wizardState.frameworkConfig.router
                  ? t.wizard?.steps?.outputFormat?.frameworkConfig?.routerEnabled
                  : t.wizard?.steps?.outputFormat?.frameworkConfig?.routerDisabled
                }}
              </span>
              <!-- Toggle switch -->
              <div
                :class="[
                  'relative w-11 h-6 rounded-full transition-colors',
                  wizardState.frameworkConfig.router ? 'bg-green-500' : 'bg-slate-300 dark:bg-slate-600'
                ]"
              >
                <div
                  :class="[
                    'absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform',
                    wizardState.frameworkConfig.router ? 'translate-x-5.5' : 'translate-x-0.5'
                  ]"
                ></div>
              </div>
            </button>
          </div>

          <!-- State Management -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.stateManagement }}
            </label>
            <div class="grid grid-cols-2 gap-3">
              <button
                v-for="sm in currentStateManagementOptions"
                :key="sm.value"
                @click="setStateManagement(sm.value)"
                :class="[
                  'px-4 py-2.5 rounded-lg border-2 text-sm font-medium transition-all',
                  wizardState.frameworkConfig.stateManagement === sm.value
                    ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                    : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:border-blue-400'
                ]"
              >
                {{ sm.value === 'none' ? (t.wizard?.steps?.outputFormat?.frameworkConfig?.none || 'None') : sm.label }}
              </button>
            </div>
          </div>
        </div>

        <!-- Build Tool (full width) -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            {{ t.wizard?.steps?.outputFormat?.frameworkConfig?.buildTool }}
          </label>
          <div class="grid grid-cols-3 gap-3">
            <button
              v-for="bt in buildToolOptions"
              :key="bt.value"
              @click="setBuildTool(bt.value)"
              :class="[
                'px-4 py-3 rounded-lg border-2 text-left transition-all text-sm',
                wizardState.frameworkConfig.buildTool === bt.value
                  ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                  : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
              ]"
            >
              <div class="font-semibold text-slate-900 dark:text-white">{{ bt.label }}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ bt.description }}</div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
