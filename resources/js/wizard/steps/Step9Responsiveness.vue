<script setup lang="ts">
import { wizardState, ResponsivenessType } from '../wizardState';

const responsivenessOptions: { value: ResponsivenessType; label: string; description: string; bestFor: string }[] = [
  {
    value: 'desktop-first',
    label: 'Desktop-First',
    description: 'Optimized for desktop, scales down to mobile',
    bestFor: 'Internal tools, admin panels'
  },
  {
    value: 'mobile-first',
    label: 'Mobile-First',
    description: 'Optimized for mobile, scales up to desktop',
    bestFor: 'Public sites, consumer apps'
  },
  {
    value: 'fully-responsive',
    label: 'Fully Responsive',
    description: 'Equal optimization for all screen sizes',
    bestFor: 'Multi-device applications'
  },
];
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        Responsiveness
      </h2>
      <p class="text-slate-600 dark:text-slate-400">
        Define the responsive design approach for your template.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <button
        v-for="resp in responsivenessOptions"
        :key="resp.value"
        @click="wizardState.responsiveness = resp.value"
        :class="[
          'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg',
          wizardState.responsiveness === resp.value
            ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
            : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
        ]"
      >
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
            {{ resp.label }}
          </h3>
          <div
            :class="[
              'w-6 h-6 rounded-full border-2 flex items-center justify-center',
              wizardState.responsiveness === resp.value
                ? 'border-blue-600 bg-blue-600'
                : 'border-slate-300 dark:border-slate-600'
            ]"
          >
            <svg
              v-if="wizardState.responsiveness === resp.value"
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
        <p class="text-slate-600 dark:text-slate-400 text-sm mb-3">
          {{ resp.description }}
        </p>
        <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
          <p class="text-xs text-slate-500 dark:text-slate-500">
            <strong>Best for:</strong> {{ resp.bestFor }}
          </p>
        </div>
      </button>
    </div>
  </div>
</template>




