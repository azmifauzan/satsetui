<script setup lang="ts">
import { wizardState, UiDensity, BorderRadius } from '../wizardState';

const densityOptions: { value: UiDensity; label: string; description: string }[] = [
  { value: 'compact', label: 'Compact', description: 'Tight spacing, small fonts, data-dense' },
  { value: 'comfortable', label: 'Comfortable', description: 'Balanced spacing, readable' },
  { value: 'spacious', label: 'Spacious', description: 'Generous whitespace, large touch targets' },
];

const borderOptions: { value: BorderRadius; label: string; description: string }[] = [
  { value: 'sharp', label: 'Sharp', description: '0-2px radius, modern/technical' },
  { value: 'rounded', label: 'Rounded', description: '4-8px radius, friendly/approachable' },
];
</script>

<template>
  <div class="space-y-8">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        UI Density & Style
      </h2>
      <p class="text-slate-600 dark:text-slate-400">
        Control spacing, sizing, and visual weight of your template.
      </p>
    </div>

    <!-- Density -->
    <div class="space-y-3">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
        Density
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

    <!-- Visual Preview -->
    <div class="p-6 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
      <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">Preview</h3>
      <div class="space-y-3">
        <button
          :class="[
            'px-4 py-2 bg-blue-600 text-white font-medium transition-all',
            wizardState.ui.density === 'compact' ? 'text-sm px-3 py-1.5' : '',
            wizardState.ui.density === 'comfortable' ? 'text-base px-4 py-2' : '',
            wizardState.ui.density === 'spacious' ? 'text-lg px-6 py-3' : '',
            wizardState.ui.borderRadius === 'sharp' ? 'rounded' : 'rounded-lg'
          ]"
        >
          Button Example
        </button>
        <div
          :class="[
            'border-2 border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900',
            wizardState.ui.density === 'compact' ? 'p-2' : '',
            wizardState.ui.density === 'comfortable' ? 'p-4' : '',
            wizardState.ui.density === 'spacious' ? 'p-6' : '',
            wizardState.ui.borderRadius === 'sharp' ? 'rounded' : 'rounded-lg'
          ]"
        >
          <p class="text-slate-600 dark:text-slate-400">Card content with current density and border settings</p>
        </div>
      </div>
    </div>
  </div>
</template>




