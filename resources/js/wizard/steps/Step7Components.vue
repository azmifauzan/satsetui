<script setup lang="ts">
import { wizardState, Component, ChartLibrary, shouldShowChartLibrary, syncChartLibrary } from '../wizardState';
import { watch } from 'vue';

const componentOptions: { value: Component; label: string; description: string }[] = [
  { value: 'buttons', label: 'Buttons', description: 'Primary, Secondary, Outline, Icon buttons' },
  { value: 'forms', label: 'Forms', description: 'Text inputs, Select, Checkbox, Radio, Textarea' },
  { value: 'modals', label: 'Modals', description: 'Dialog boxes, confirmation prompts' },
  { value: 'dropdowns', label: 'Dropdowns', description: 'Menu dropdowns, select alternatives' },
  { value: 'alerts', label: 'Alerts / Toasts', description: 'Success, error, warning, info notifications' },
  { value: 'cards', label: 'Cards', description: 'Content containers with header/body/footer' },
  { value: 'tabs', label: 'Tabs', description: 'Horizontal/vertical tab navigation' },
  { value: 'charts', label: 'Charts', description: 'Data visualizations (requires library selection)' },
];

const chartLibraryOptions: { value: ChartLibrary; label: string; description: string }[] = [
  { value: 'chartjs', label: 'Chart.js', description: 'Simple, flexible, popular' },
  { value: 'echarts', label: 'Apache ECharts', description: 'Powerful, feature-rich, enterprise-grade' },
];

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

watch(() => wizardState.components, () => {
  syncChartLibrary();
}, { deep: true });
</script>

<template>
  <div class="space-y-8">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        Components
      </h2>
      <p class="text-slate-600 dark:text-slate-400">
        Select UI components to include in your template. Choose at least one component.
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
        Chart Library
        <span class="text-sm font-normal text-slate-600 dark:text-slate-400">(Required for Charts)</span>
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
    <div v-if="wizardState.components.length === 0" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
      <p class="text-yellow-800 dark:text-yellow-200 text-sm">
        ⚠️ Please select at least one component to continue
      </p>
    </div>
    <div v-else-if="shouldShowChartLibrary && !wizardState.chartLibrary" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
      <p class="text-yellow-800 dark:text-yellow-200 text-sm">
        ⚠️ Please select a chart library since you've chosen to include charts
      </p>
    </div>
    <div v-else class="text-sm text-slate-600 dark:text-slate-400">
      Selected: <strong>{{ wizardState.components.length }}</strong> component(s)
    </div>
  </div>
</template>




