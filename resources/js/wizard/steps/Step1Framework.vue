<script setup lang="ts">
import { wizardState, Framework } from '../wizardState';

const frameworks: { value: Framework; label: string; description: string }[] = [
  {
    value: 'tailwind',
    label: 'Tailwind CSS',
    description: 'Utility-first framework, highly customizable, modern approach',
  },
  {
    value: 'bootstrap',
    label: 'Bootstrap',
    description: 'Component-based, rapid prototyping, extensive ecosystem',
  },
];

function selectFramework(framework: Framework) {
  wizardState.framework = framework;
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        Choose CSS Framework
      </h2>
      <p class="text-slate-600 dark:text-slate-400">
        Select the CSS framework foundation for your template. This decision affects all subsequent component structures and styling patterns.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
</template>




