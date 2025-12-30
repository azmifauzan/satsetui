<script setup lang="ts">
import { 
  wizardState, 
  Page, 
  PredefinedPage, 
  NavigationType, 
  SidebarState, 
  FooterStyle, 
  ThemeMode,
  BackgroundStyle,
  Component,
  PredefinedComponent,
  ChartLibrary,
  suggestedPages, 
  shouldShowSidebarState, 
  syncSidebarState, 
  addCustomPage, 
  removeCustomPage, 
  addCustomNavItem, 
  removeCustomNavItem, 
  shouldShowChartLibrary,
  syncChartLibrary,
  addCustomComponent,
  removeCustomComponent,
  totalPagesCount, 
  totalComponentsCount,
  MAX_BASE_PAGES, 
  MAX_BASE_COMPONENTS,
  extraPageCredits,
  extraComponentCredits
} from '../wizardState';
import { computed, watch, onMounted, ref } from 'vue';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

// Custom page input state
const showAddPageForm = ref(false);
const newPageName = ref('');
const newPageDescription = ref('');

// Custom navigation input state
const showAddNavForm = ref(false);
const newNavLabel = ref('');
const newNavRoute = ref('');
const newNavIcon = ref('');

// Custom component input state
const showAddComponentForm = ref(false);
const newComponentName = ref('');
const newComponentDescription = ref('');

// Auto-select suggested pages when entering this step or when category changes
watch(() => wizardState.category, () => {
  // Always apply suggested pages when category changes, regardless of current selection
  if (suggestedPages.value.length > 0) {
    wizardState.pages = [...suggestedPages.value];
  }
}, { immediate: true });

const allPages = computed(() => [
  { value: 'login' as PredefinedPage, label: t.value.wizard?.steps?.pages?.login || 'Login', category: t.value.wizard?.steps?.pages?.authCategory || 'Authentication' },
  { value: 'register' as PredefinedPage, label: t.value.wizard?.steps?.pages?.register || 'Register', category: t.value.wizard?.steps?.pages?.authCategory || 'Authentication' },
  { value: 'forgot-password' as PredefinedPage, label: t.value.wizard?.steps?.pages?.forgotPassword || 'Forgot Password', category: t.value.wizard?.steps?.pages?.authCategory || 'Authentication' },
  { value: 'dashboard' as PredefinedPage, label: t.value.wizard?.steps?.pages?.dashboard || 'Dashboard', category: t.value.wizard?.steps?.pages?.appCategory || 'Application' },
  { value: 'user-management' as PredefinedPage, label: t.value.wizard?.steps?.pages?.userManagement || 'User Management', category: t.value.wizard?.steps?.pages?.appCategory || 'Application' },
  { value: 'settings' as PredefinedPage, label: t.value.wizard?.steps?.pages?.settings || 'Settings', category: t.value.wizard?.steps?.pages?.appCategory || 'Application' },
  { value: 'charts' as PredefinedPage, label: t.value.wizard?.steps?.pages?.charts || 'Charts / Analytics', category: t.value.wizard?.steps?.pages?.appCategory || 'Application' },
  { value: 'tables' as PredefinedPage, label: t.value.wizard?.steps?.pages?.tables || 'Tables / Data List', category: t.value.wizard?.steps?.pages?.appCategory || 'Application' },
  { value: 'profile' as PredefinedPage, label: t.value.wizard?.steps?.pages?.profile || 'Profile', category: t.value.wizard?.steps?.pages?.appCategory || 'Application' },
  { value: 'about' as PredefinedPage, label: t.value.wizard?.steps?.pages?.about || 'About', category: t.value.wizard?.steps?.pages?.publicCategory || 'Public' },
  { value: 'contact' as PredefinedPage, label: t.value.wizard?.steps?.pages?.contact || 'Contact', category: t.value.wizard?.steps?.pages?.publicCategory || 'Public' },
]);

const pagesByCategory = computed(() => {
  const grouped: Record<string, typeof allPages.value> = {};
  allPages.value.forEach(page => {
    if (!grouped[page.category]) {
      grouped[page.category] = [];
    }
    grouped[page.category].push(page);
  });
  return grouped;
});

const navigationOptions = computed(() => [
  { value: 'sidebar' as NavigationType, label: t.value.wizard?.steps?.layout?.sidebar || 'Sidebar', description: t.value.wizard?.steps?.layout?.sidebarDesc || 'Menu vertikal, ideal untuk banyak item' },
  { value: 'topbar' as NavigationType, label: t.value.wizard?.steps?.layout?.topbar || 'Top Navigation', description: t.value.wizard?.steps?.layout?.topbarDesc || 'Menu horizontal, minimalis' },
  { value: 'hybrid' as NavigationType, label: t.value.wizard?.steps?.layout?.hybrid || 'Hybrid (Sidebar + Topbar)', description: t.value.wizard?.steps?.layout?.hybridDesc || 'Kombinasi keduanya' },
]);

const sidebarStates = computed(() => [
  { value: 'expanded' as SidebarState, label: t.value.wizard?.steps?.layout?.expanded || 'Expanded by Default' },
  { value: 'collapsed' as SidebarState, label: t.value.wizard?.steps?.layout?.collapsed || 'Collapsed by Default' },
]);

const footerOptions = computed(() => [
  { value: 'minimal' as FooterStyle, label: t.value.wizard?.steps?.layout?.minimalFooter || 'Minimal (copyright only)' },
  { value: 'full' as FooterStyle, label: t.value.wizard?.steps?.layout?.fullFooter || 'Full (with links)' },
]);

function togglePage(page: Page) {
  const index = wizardState.pages.indexOf(page);
  if (index > -1) {
    wizardState.pages.splice(index, 1);
  } else {
    wizardState.pages.push(page);
  }
}

function isSelected(page: Page): boolean {
  return wizardState.pages.includes(page);
}

function isSuggested(page: Page): boolean {
  return suggestedPages.value.includes(page as PredefinedPage);
}

function selectSuggested() {
  wizardState.pages = [...suggestedPages.value];
}

function selectNavigation(nav: NavigationType) {
  wizardState.layout.navigation = nav;
  syncSidebarState();
}

// Custom page functions
function handleAddCustomPage() {
  if (newPageName.value.trim().length >= 2 && newPageDescription.value.trim().length >= 5) {
    addCustomPage(newPageName.value, newPageDescription.value);
    newPageName.value = '';
    newPageDescription.value = '';
    showAddPageForm.value = false;
  }
}

function handleRemoveCustomPage(id: string) {
  removeCustomPage(id);
}

// Custom navigation functions
function handleAddCustomNav() {
  if (newNavLabel.value.trim().length >= 2 && newNavRoute.value.trim().length >= 1) {
    addCustomNavItem(newNavLabel.value, newNavRoute.value, newNavIcon.value || undefined);
    newNavLabel.value = '';
    newNavRoute.value = '';
    newNavIcon.value = '';
    showAddNavForm.value = false;
  }
}

function handleRemoveCustomNav(id: string) {
  removeCustomNavItem(id);
}

watch(() => wizardState.layout.navigation, () => {
  syncSidebarState();
});

// Theme presets and options
const presetColors = [
  { name: 'Blue', value: '#3B82F6' },
  { name: 'Green', value: '#10B981' },
  { name: 'Purple', value: '#8B5CF6' },
  { name: 'Red', value: '#EF4444' },
  { name: 'Orange', value: '#F59E0B' },
  { name: 'Pink', value: '#EC4899' },
  { name: 'Indigo', value: '#6366F1' },
  { name: 'Teal', value: '#14B8A6' },
];

const modes = computed(() => [
  { value: 'light' as ThemeMode, label: t.value.wizard?.steps?.theme?.lightMode || 'Light Mode', icon: 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z' },
  { value: 'dark' as ThemeMode, label: t.value.wizard?.steps?.theme?.darkMode || 'Dark Mode', icon: 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z' },
]);

const backgrounds = computed(() => [
  { value: 'solid' as BackgroundStyle, label: t.value.wizard?.steps?.theme?.solid || 'Solid' },
  { value: 'gradient' as BackgroundStyle, label: t.value.wizard?.steps?.theme?.gradient || 'Subtle Gradient' },
]);

const componentOptions = computed(() => [
  { value: 'buttons' as Component, label: t.value.wizard?.steps?.components?.buttons || 'Buttons', description: t.value.wizard?.steps?.components?.buttonsDesc || 'Primary, Secondary, Outline, Icon buttons' },
  { value: 'forms' as Component, label: t.value.wizard?.steps?.components?.forms || 'Forms', description: t.value.wizard?.steps?.components?.formsDesc || 'Text inputs, Select, Checkbox, Radio, Textarea' },
  { value: 'modals' as Component, label: t.value.wizard?.steps?.components?.modals || 'Modals', description: t.value.wizard?.steps?.components?.modalsDesc || 'Dialog boxes, prompt konfirmasi' },
  { value: 'dropdowns' as Component, label: t.value.wizard?.steps?.components?.dropdowns || 'Dropdowns', description: t.value.wizard?.steps?.components?.dropdownsDesc || 'Menu dropdown, alternatif select' },
  { value: 'alerts' as Component, label: t.value.wizard?.steps?.components?.alerts || 'Alerts / Toasts', description: t.value.wizard?.steps?.components?.alertsDesc || 'Notifikasi success, error, warning, info' },
  { value: 'cards' as Component, label: t.value.wizard?.steps?.components?.cards || 'Cards', description: t.value.wizard?.steps?.components?.cardsDesc || 'Container konten dengan header/body/footer' },
  { value: 'tabs' as Component, label: t.value.wizard?.steps?.components?.tabs || 'Tabs', description: t.value.wizard?.steps?.components?.tabsDesc || 'Navigasi tab horizontal/vertikal' },
  { value: 'charts' as Component, label: t.value.wizard?.steps?.components?.charts || 'Charts', description: t.value.wizard?.steps?.components?.chartsDesc || 'Visualisasi data (memerlukan library)' },
]);

const chartLibraryOptions = computed(() => [
  { value: 'chartjs' as ChartLibrary, label: 'Chart.js', description: t.value.wizard?.steps?.components?.chartjsDesc || 'Sederhana, fleksibel, populer' },
  { value: 'echarts' as ChartLibrary, label: 'Apache ECharts', description: t.value.wizard?.steps?.components?.echartsDesc || 'Powerful, kaya fitur, enterprise-grade' },
]);

function toggleComponent(component: Component) {
  const index = wizardState.components.indexOf(component);
  if (index > -1) {
    wizardState.components.splice(index, 1);
  } else {
    wizardState.components.push(component);
  }
  syncChartLibrary();
}

function isComponentSelected(component: Component): boolean {
  return wizardState.components.includes(component);
}

// Custom component functions
function handleAddCustomComponent() {
  if (newComponentName.value.trim().length >= 2 && newComponentDescription.value.trim().length >= 5) {
    addCustomComponent(newComponentName.value, newComponentDescription.value);
    newComponentName.value = '';
    newComponentDescription.value = '';
    showAddComponentForm.value = false;
  }
}

function handleRemoveCustomComponent(id: string) {
  removeCustomComponent(id);
}

watch(() => wizardState.components, () => {
  syncChartLibrary();
}, { deep: true });
</script>

<template>
  <div class="space-y-10">
    <!-- Theme & Visual Identity (MOVED TO TOP) -->
    <div class="space-y-8">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.theme?.title || 'Tema & Identitas Visual' }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400">
          {{ t.wizard?.steps?.theme?.description || 'Tentukan skema warna dan preferensi mode visual Anda.' }}
        </p>
      </div>

      <!-- Primary Color -->
      <div class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          {{ t.wizard?.steps?.theme?.primaryColor || 'Warna Utama' }}
        </h3>
        <div class="flex flex-wrap gap-3 items-center">
          <button
            v-for="color in presetColors"
            :key="color.value"
            @click="wizardState.theme.primary = color.value"
            :class="[
              'w-12 h-12 rounded-lg transition-all hover:scale-110',
              wizardState.theme.primary === color.value
                ? 'ring-4 ring-blue-500 ring-offset-2 dark:ring-offset-slate-900'
                : 'hover:ring-2 hover:ring-slate-300'
            ]"
            :style="{ backgroundColor: color.value }"
            :title="color.name"
          />
          <div class="flex items-center gap-2 ml-2">
            <input
              v-model="wizardState.theme.primary"
              type="color"
              class="w-12 h-12 rounded-lg cursor-pointer"
            />
            <input
              v-model="wizardState.theme.primary"
              type="text"
              class="px-3 py-2 border-2 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono text-sm uppercase w-24"
              pattern="^#[0-9A-Fa-f]{6}$"
            />
          </div>
        </div>
      </div>

      <!-- Secondary Color -->
      <div class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          {{ t.wizard?.steps?.theme?.secondaryColor || 'Warna Sekunder' }}
        </h3>
        <div class="flex flex-wrap gap-3 items-center">
          <button
            v-for="color in presetColors"
            :key="color.value"
            @click="wizardState.theme.secondary = color.value"
            :class="[
              'w-12 h-12 rounded-lg transition-all hover:scale-110',
              wizardState.theme.secondary === color.value
                ? 'ring-4 ring-blue-500 ring-offset-2 dark:ring-offset-slate-900'
                : 'hover:ring-2 hover:ring-slate-300'
            ]"
            :style="{ backgroundColor: color.value }"
            :title="color.name"
          />
          <div class="flex items-center gap-2 ml-2">
            <input
              v-model="wizardState.theme.secondary"
              type="color"
              class="w-12 h-12 rounded-lg cursor-pointer"
            />
            <input
              v-model="wizardState.theme.secondary"
              type="text"
              class="px-3 py-2 border-2 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono text-sm uppercase w-24"
              pattern="^#[0-9A-Fa-f]{6}$"
            />
          </div>
        </div>
      </div>

      <!-- Color Mode -->
      <div class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          {{ t.wizard?.steps?.theme?.colorMode || 'Mode Warna' }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button
            v-for="mode in modes"
            :key="mode.value"
            @click="wizardState.theme.mode = mode.value"
            :class="[
              'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
              wizardState.theme.mode === mode.value
                ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
            ]"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="mode.icon" />
                  </svg>
                </div>
                <span class="font-medium text-slate-900 dark:text-white">{{ mode.label }}</span>
              </div>
              <div
                :class="[
                  'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                  wizardState.theme.mode === mode.value
                    ? 'border-blue-600 bg-blue-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="wizardState.theme.mode === mode.value"
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
          </button>
        </div>
      </div>

      <!-- Background Style -->
      <div class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          {{ t.wizard?.steps?.theme?.backgroundStyle || 'Gaya Background' }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button
            v-for="bg in backgrounds"
            :key="bg.value"
            @click="wizardState.theme.background = bg.value"
            :class="[
              'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
              wizardState.theme.background === bg.value
                ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
            ]"
          >
            <div class="flex items-center justify-between">
              <span class="font-medium text-slate-900 dark:text-white">{{ bg.label }}</span>
              <div
                :class="[
                  'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                  wizardState.theme.background === bg.value
                    ? 'border-blue-600 bg-blue-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="wizardState.theme.background === bg.value"
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
          </button>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t-2 border-slate-200 dark:border-slate-700"></div>

    <!-- Pages Selection -->
    <div class="space-y-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.pages?.title || 'Pilih Halaman' }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400 mb-4">
          {{ t.wizard?.steps?.pages?.description || 'Pilih halaman-halaman yang akan disertakan dalam template Anda. Pilih minimal satu halaman.' }}
        </p>
        <button
          @click="selectSuggested"
          class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors text-sm font-medium"
        >
          Gunakan Halaman yang Disarankan untuk {{ wizardState.category }}
        </button>
      </div>

      <div v-for="(pages, category) in pagesByCategory" :key="category" class="space-y-3">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
          {{ category }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <button
            v-for="page in pages"
            :key="page.value"
            @click="togglePage(page.value)"
            :class="[
              'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md relative',
              isSelected(page.value)
                ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
            ]"
          >
            <div class="flex items-center justify-between">
              <span class="text-slate-900 dark:text-white font-medium">
                {{ page.label }}
              </span>
              <div class="flex items-center gap-2">
                <span
                  v-if="isSuggested(page.value) && !isSelected(page.value)"
                  class="text-xs px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded"
                >
                  Disarankan
                </span>
                <div
                  :class="[
                    'w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0',
                    isSelected(page.value)
                      ? 'border-blue-600 bg-blue-600'
                      : 'border-slate-300 dark:border-slate-600'
                  ]"
                >
                  <svg
                    v-if="isSelected(page.value)"
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
            </div>
          </button>
        </div>
      </div>

      <div v-if="wizardState.pages.length === 0 && wizardState.customPages.length === 0" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
        <p class="text-yellow-800 dark:text-yellow-200 text-sm">
          ⚠️ {{ t.wizard?.steps?.pages?.selectAtLeastOne || 'Silakan pilih minimal satu halaman untuk melanjutkan' }}
        </p>
      </div>
      <div v-else class="text-sm text-slate-600 dark:text-slate-400">
        {{ t.wizard?.steps?.pages?.selectedCount || 'Dipilih' }}: <strong>{{ totalPagesCount }}</strong> {{ t.wizard?.steps?.pages?.pagesLabel || 'halaman' }}
        <span v-if="totalPagesCount > MAX_BASE_PAGES" class="ml-2 px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded text-xs">
          +{{ extraPageCredits }} {{ t.common?.credits || 'kredit' }} {{ t.wizard?.steps?.pages?.extraCredits || '(halaman tambahan)' }}
        </span>
      </div>

      <!-- Custom Pages Section -->
      <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
            {{ t.wizard?.steps?.pages?.customPagesTitle || 'Halaman Kustom' }}
          </h3>
          <button
            @click="showAddPageForm = !showAddPageForm"
            class="px-4 py-2 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors text-sm font-medium flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            {{ t.wizard?.steps?.pages?.addCustomPage || 'Tambah Halaman' }}
          </button>
        </div>

        <!-- Add Custom Page Form -->
        <div v-if="showAddPageForm" class="mb-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-600">
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                {{ t.wizard?.steps?.pages?.customPageName || 'Nama Halaman' }} *
              </label>
              <input
                v-model="newPageName"
                type="text"
                :placeholder="t.wizard?.steps?.pages?.customPageNamePlaceholder || 'Contoh: Produk, Galeri, FAQ, Testimoni'"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                {{ t.wizard?.steps?.pages?.customPageDesc || 'Deskripsi' }} *
              </label>
              <textarea
                v-model="newPageDescription"
                rows="2"
                :placeholder="t.wizard?.steps?.pages?.customPageDescPlaceholder || 'Jelaskan konten dan fungsi halaman ini'"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500 resize-none"
              ></textarea>
            </div>
            <div class="flex gap-2">
              <button
                @click="handleAddCustomPage"
                :disabled="newPageName.trim().length < 2 || newPageDescription.trim().length < 5"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
              >
                {{ t.common?.save || 'Simpan' }}
              </button>
              <button
                @click="showAddPageForm = false; newPageName = ''; newPageDescription = ''"
                class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 text-sm font-medium"
              >
                {{ t.common?.cancel || 'Batal' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Custom Pages List -->
        <div v-if="wizardState.customPages.length > 0" class="space-y-2">
          <div
            v-for="page in wizardState.customPages"
            :key="page.id"
            class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800"
          >
            <div>
              <span class="font-medium text-purple-900 dark:text-purple-100">{{ page.name }}</span>
              <span class="ml-2 px-2 py-0.5 text-xs bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200 rounded">{{ t.wizard?.steps?.pages?.customLabel || 'Kustom' }}</span>
              <p class="text-sm text-purple-600 dark:text-purple-300 mt-1">{{ page.description }}</p>
            </div>
            <button
              @click="handleRemoveCustomPage(page.id)"
              class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t-2 border-slate-200 dark:border-slate-700"></div>

    <!-- Layout Configuration -->
    <div class="space-y-8">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.layout?.title || 'Layout & Navigasi' }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400">
          {{ t.wizard?.steps?.layout?.description || 'Konfigurasi pola navigasi struktural dan elemen layout untuk template Anda.' }}
        </p>
      </div>

      <!-- Navigation Style -->
      <div class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          Gaya Navigasi
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <button
            v-for="nav in navigationOptions"
            :key="nav.value"
            @click="selectNavigation(nav.value)"
            :class="[
              'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
              wizardState.layout.navigation === nav.value
                ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
            ]"
          >
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-semibold text-slate-900 dark:text-white">{{ nav.label }}</h4>
              <div
                :class="[
                  'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                  wizardState.layout.navigation === nav.value
                    ? 'border-blue-600 bg-blue-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="wizardState.layout.navigation === nav.value"
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
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ nav.description }}</p>
          </button>
        </div>
      </div>

      <!-- Sidebar Default State (conditional) -->
      <div v-if="shouldShowSidebarState" class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          Status Default Sidebar
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button
            v-for="state in sidebarStates"
            :key="state.value"
            @click="wizardState.layout.sidebarDefaultState = state.value"
            :class="[
              'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
              wizardState.layout.sidebarDefaultState === state.value
                ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
            ]"
          >
            <div class="flex items-center justify-between">
              <span class="font-medium text-slate-900 dark:text-white">{{ state.label }}</span>
              <div
                :class="[
                  'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                  wizardState.layout.sidebarDefaultState === state.value
                    ? 'border-blue-600 bg-blue-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="wizardState.layout.sidebarDefaultState === state.value"
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
          </button>
        </div>
      </div>

      <!-- Breadcrumbs Toggle -->
      <div class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          Elemen Tambahan
        </h3>
        <label class="flex items-center justify-between p-4 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer hover:border-blue-400 transition-all">
          <div>
            <div class="font-medium text-slate-900 dark:text-white">Aktifkan Breadcrumbs</div>
            <div class="text-sm text-slate-600 dark:text-slate-400">Tampilkan breadcrumb navigasi pada halaman</div>
          </div>
          <input
            v-model="wizardState.layout.breadcrumbs"
            type="checkbox"
            class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
          />
        </label>
      </div>

      <!-- Footer Style -->
      <div class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          Gaya Footer
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button
            v-for="footer in footerOptions"
            :key="footer.value"
            @click="wizardState.layout.footer = footer.value"
            :class="[
              'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
              wizardState.layout.footer === footer.value
                ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
            ]"
          >
            <div class="flex items-center justify-between">
              <span class="font-medium text-slate-900 dark:text-white">{{ footer.label }}</span>
              <div
                :class="[
                  'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                  wizardState.layout.footer === footer.value
                    ? 'border-blue-600 bg-blue-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="wizardState.layout.footer === footer.value"
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
          </button>
        </div>
      </div>

      <!-- Custom Navigation Items Section -->
      <div class="space-y-3 pt-6 border-t border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ t.wizard?.steps?.layout?.customNavTitle || 'Item Navigasi Kustom' }}
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              {{ t.wizard?.steps?.layout?.customNavDesc || 'Tambahkan menu navigasi khusus selain halaman yang dipilih' }}
            </p>
          </div>
          <button
            @click="showAddNavForm = !showAddNavForm"
            class="px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors text-sm font-medium flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            {{ t.wizard?.steps?.layout?.addNavItem || 'Tambah Menu' }}
          </button>
        </div>

        <!-- Add Custom Nav Form -->
        <div v-if="showAddNavForm" class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-600">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                {{ t.wizard?.steps?.layout?.navLabel || 'Label Menu' }} *
              </label>
              <input
                v-model="newNavLabel"
                type="text"
                :placeholder="t.wizard?.steps?.layout?.navLabelPlaceholder || 'Contoh: Produk'"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-green-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                {{ t.wizard?.steps?.layout?.navRoute || 'Route/Path' }} *
              </label>
              <input
                v-model="newNavRoute"
                type="text"
                :placeholder="t.wizard?.steps?.layout?.navRoutePlaceholder || 'Contoh: /products'"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-green-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                {{ t.wizard?.steps?.layout?.navIcon || 'Icon (opsional)' }}
              </label>
              <input
                v-model="newNavIcon"
                type="text"
                :placeholder="t.wizard?.steps?.layout?.navIconPlaceholder || 'SVG path atau nama icon'"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-green-500"
              />
            </div>
          </div>
          <div class="flex gap-2 mt-3">
            <button
              @click="handleAddCustomNav"
              :disabled="newNavLabel.trim().length < 2 || newNavRoute.trim().length < 1"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
            >
              {{ t.common?.save || 'Simpan' }}
            </button>
            <button
              @click="showAddNavForm = false; newNavLabel = ''; newNavRoute = ''; newNavIcon = ''"
              class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 text-sm font-medium"
            >
              {{ t.common?.cancel || 'Batal' }}
            </button>
          </div>
        </div>

        <!-- Custom Nav Items List -->
        <div v-if="wizardState.layout.customNavItems.length > 0" class="space-y-2">
          <div
            v-for="nav in wizardState.layout.customNavItems"
            :key="nav.id"
            class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded bg-green-200 dark:bg-green-800 flex items-center justify-center">
                <svg class="w-4 h-4 text-green-700 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </div>
              <div>
                <span class="font-medium text-green-900 dark:text-green-100">{{ nav.label }}</span>
                <span class="ml-2 text-sm text-green-600 dark:text-green-400">{{ nav.route }}</span>
              </div>
            </div>
            <button
              @click="handleRemoveCustomNav(nav.id)"
              class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t-2 border-slate-200 dark:border-slate-700"></div>

    <!-- Components -->
    <div class="space-y-8">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.components?.title || 'Komponen' }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400">
          {{ t.wizard?.steps?.components?.description || 'Pilih komponen UI yang akan disertakan dalam template Anda. Pilih minimal satu komponen.' }}
        </p>
      </div>

      <!-- Component Selection -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <button
          v-for="component in componentOptions"
          :key="component.value"
          @click="toggleComponent(component.value)"
          :class="[
            'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
            isComponentSelected(component.value)
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-start justify-between mb-2">
            <h3 class="font-semibold text-slate-900 dark:text-white">{{ component.label }}</h3>
            <div
              :class="[
                'w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0',
                isComponentSelected(component.value)
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="isComponentSelected(component.value)"
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
          <p class="text-sm text-slate-600 dark:text-slate-400">{{ component.description }}</p>
        </button>
      </div>

      <!-- Chart Library Selection (conditional) -->
      <div v-if="shouldShowChartLibrary" class="space-y-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border-2 border-blue-200 dark:border-blue-800">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          Library Chart
          <span class="text-sm font-normal text-slate-600 dark:text-slate-400">(Wajib untuk Charts)</span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button
            v-for="library in chartLibraryOptions"
            :key="library.value"
            @click="wizardState.chartLibrary = library.value"
            :class="[
              'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md bg-white dark:bg-slate-800',
              wizardState.chartLibrary === library.value
                ? 'border-blue-600 ring-2 ring-blue-500'
                : 'border-slate-200 dark:border-slate-700 hover:border-blue-400'
            ]"
          >
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-semibold text-slate-900 dark:text-white">{{ library.label }}</h4>
              <div
                :class="[
                  'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                  wizardState.chartLibrary === library.value
                    ? 'border-blue-600 bg-blue-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="wizardState.chartLibrary === library.value"
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
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ library.description }}</p>
          </button>
        </div>
      </div>

      <!-- Validation Message -->
      <div v-if="wizardState.components.length === 0 && wizardState.customComponents.length === 0" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
        <p class="text-yellow-800 dark:text-yellow-200 text-sm">
          ⚠️ {{ t.wizard?.steps?.components?.selectAtLeastOne || 'Silakan pilih minimal satu komponen untuk melanjutkan' }}
        </p>
      </div>
      <div v-else-if="shouldShowChartLibrary && !wizardState.chartLibrary" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
        <p class="text-yellow-800 dark:text-yellow-200 text-sm">
          ⚠️ {{ t.wizard?.steps?.components?.chartLibraryRequired || 'Silakan pilih library chart karena Anda telah memilih untuk menyertakan charts' }}
        </p>
      </div>
      <div v-else class="text-sm text-slate-600 dark:text-slate-400">
        {{ t.wizard?.steps?.components?.selectedCount || 'Dipilih' }}: <strong>{{ totalComponentsCount }}</strong> {{ t.wizard?.steps?.components?.componentsLabel || 'komponen' }}
        <span class="ml-2 text-xs text-purple-600 dark:text-purple-400">
          ({{ t.wizard?.steps?.components?.componentPagesNote || 'Setiap komponen = 1 halaman showcase' }})
        </span>
      </div>

      <!-- Custom Components Section -->
      <div class="pt-6 border-t border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ t.wizard?.steps?.components?.customTitle || 'Komponen Kustom' }}
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              {{ t.wizard?.steps?.components?.customDesc || 'Tambahkan komponen khusus yang tidak tersedia di pilihan standar' }}
            </p>
          </div>
          <button
            @click="showAddComponentForm = !showAddComponentForm"
            class="px-4 py-2 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors text-sm font-medium flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            {{ t.wizard?.steps?.components?.addCustom || 'Tambah Komponen' }}
          </button>
        </div>

        <!-- Add Custom Component Form -->
        <div v-if="showAddComponentForm" class="mb-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-600">
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                {{ t.wizard?.steps?.components?.customName || 'Nama Komponen' }} *
              </label>
              <input
                v-model="newComponentName"
                type="text"
                :placeholder="t.wizard?.steps?.components?.customNamePlaceholder || 'Contoh: DataTable, FileUploader, DateRangePicker'"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                {{ t.wizard?.steps?.components?.customDescLabel || 'Deskripsi Komponen' }} *
              </label>
              <textarea
                v-model="newComponentDescription"
                rows="2"
                :placeholder="t.wizard?.steps?.components?.customDescPlaceholder || 'Jelaskan fungsionalitas dan fitur komponen ini'"
                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500 resize-none"
              ></textarea>
            </div>
            <div class="flex gap-2">
              <button
                @click="handleAddCustomComponent"
                :disabled="newComponentName.trim().length < 2 || newComponentDescription.trim().length < 5"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
              >
                {{ t.common?.save || 'Simpan' }}
              </button>
              <button
                @click="showAddComponentForm = false; newComponentName = ''; newComponentDescription = ''"
                class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 text-sm font-medium"
              >
                {{ t.common?.cancel || 'Batal' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Custom Components List -->
        <div v-if="wizardState.customComponents.length > 0" class="space-y-2">
          <div
            v-for="component in wizardState.customComponents"
            :key="component.id"
            class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800"
          >
            <div>
              <span class="font-medium text-purple-900 dark:text-purple-100">{{ component.name }}</span>
              <span class="ml-2 px-2 py-0.5 text-xs bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200 rounded">{{ t.wizard?.steps?.components?.customLabel || 'Kustom' }}</span>
              <p class="text-sm text-purple-600 dark:text-purple-300 mt-1">{{ component.description }}</p>
            </div>
            <button
              @click="handleRemoveCustomComponent(component.id)"
              class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>




