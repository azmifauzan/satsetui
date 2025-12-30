<script setup lang="ts">
import { wizardState, ThemeMode, BackgroundStyle, UiDensity, BorderRadius, Component, PredefinedComponent, ChartLibrary, shouldShowChartLibrary, syncChartLibrary, addCustomComponent, removeCustomComponent, totalComponentsCount, MAX_BASE_COMPONENTS, extraComponentCredits } from '../wizardState';
import { watch, computed, ref } from 'vue';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

// Custom component input state
const showAddComponentForm = ref(false);
const newComponentName = ref('');
const newComponentDescription = ref('');

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

const densityOptions = computed(() => [
  { value: 'compact' as UiDensity, label: t.value.wizard?.steps?.theme?.compact || 'Compact', description: t.value.wizard?.steps?.theme?.compactDesc || 'Spasi ketat, font kecil, padat data' },
  { value: 'comfortable' as UiDensity, label: t.value.wizard?.steps?.theme?.comfortable || 'Comfortable', description: t.value.wizard?.steps?.theme?.comfortableDesc || 'Spasi seimbang, mudah dibaca' },
  { value: 'spacious' as UiDensity, label: t.value.wizard?.steps?.theme?.spacious || 'Spacious', description: t.value.wizard?.steps?.theme?.spaciousDesc || 'Whitespace murah hati, target sentuh besar' },
]);

const borderOptions = computed(() => [
  { value: 'sharp' as BorderRadius, label: t.value.wizard?.steps?.theme?.sharp || 'Sharp', description: t.value.wizard?.steps?.theme?.sharpDesc || '0-2px radius, modern/teknis' },
  { value: 'rounded' as BorderRadius, label: t.value.wizard?.steps?.theme?.rounded || 'Rounded', description: t.value.wizard?.steps?.theme?.roundedDesc || '4-8px radius, ramah/mudah didekati' },
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

function isSelected(component: Component): boolean {
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
    <!-- Theme & Visual Identity -->
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
          Warna Utama
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
          Warna Sekunder
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
          Mode Warna
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
          Gaya Background
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

    <!-- UI Density & Style -->
    <div class="space-y-8">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          Kepadatan & Gaya UI
        </h2>
        <p class="text-slate-600 dark:text-slate-400">
          Kontrol spasi, ukuran, dan bobot visual template Anda.
        </p>
      </div>

      <!-- Density -->
      <div class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          Kepadatan
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <button
            v-for="density in densityOptions"
            :key="density.value"
            @click="wizardState.ui.density = density.value"
            :class="[
              'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
              wizardState.ui.density === density.value
                ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
            ]"
          >
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-semibold text-slate-900 dark:text-white">{{ density.label }}</h4>
              <div
                :class="[
                  'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                  wizardState.ui.density === density.value
                    ? 'border-blue-600 bg-blue-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="wizardState.ui.density === density.value"
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
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ density.description }}</p>
          </button>
        </div>
      </div>

      <!-- Border Radius -->
      <div class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          Border Radius
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button
            v-for="border in borderOptions"
            :key="border.value"
            @click="wizardState.ui.borderRadius = border.value"
            :class="[
              'p-4 border-2 text-left transition-all hover:shadow-md',
              border.value === 'sharp' ? 'rounded' : 'rounded-xl',
              wizardState.ui.borderRadius === border.value
                ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
            ]"
          >
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-semibold text-slate-900 dark:text-white">{{ border.label }}</h4>
              <div
                :class="[
                  'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                  wizardState.ui.borderRadius === border.value
                    ? 'border-blue-600 bg-blue-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="wizardState.ui.borderRadius === border.value"
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
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ border.description }}</p>
          </button>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t-2 border-slate-200 dark:border-slate-700"></div>

    <!-- Components -->
    <div class="space-y-8">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          Komponen
        </h2>
        <p class="text-slate-600 dark:text-slate-400">
          Pilih komponen UI yang akan disertakan dalam template Anda. Pilih minimal satu komponen.
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
            isSelected(component.value)
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-start justify-between mb-2">
            <h3 class="font-semibold text-slate-900 dark:text-white">{{ component.label }}</h3>
            <div
              :class="[
                'w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0',
                isSelected(component.value)
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="isSelected(component.value)"
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
        <span v-if="totalComponentsCount > MAX_BASE_COMPONENTS" class="ml-2 px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded text-xs">
          +{{ extraComponentCredits }} {{ t.common?.credits || 'kredit' }} {{ t.wizard?.steps?.components?.extraCredits || '(komponen tambahan)' }}
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




