<script setup lang="ts">
import { wizardState, OutputIntent } from '../wizardState';

const outputIntentOptions: { value: OutputIntent; label: string; description: string; includes: string[] }[] = [
  {
    value: 'mvp',
    label: 'MVP-Ready Scaffold',
    description: 'Quick start with placeholder content and basic functionality',
    includes: ['Basic structure', 'Placeholder content', 'Simple functionality', 'Fast prototyping']
  },
  {
    value: 'production',
    label: 'Production-Ready Base',
    description: 'Robust code with error handling and accessibility considered',
    includes: ['Error handling', 'Accessibility (ARIA)', 'Validation', 'Best practices']
  },
  {
    value: 'design-system',
    label: 'Design System Starter',
    description: 'Component library with documentation and reusable patterns',
    includes: ['Component docs', 'Reusable patterns', 'Style guide', 'Standards']
  },
];
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        Output Intent
      </h2>
      <p class="text-slate-600 dark:text-slate-400">
        Choose the expected maturity level of the generated code.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <button
        v-for="intent in outputIntentOptions"
        :key="intent.value"
        @click="wizardState.outputIntent = intent.value"
        :class="[
          'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg',
          wizardState.outputIntent === intent.value
            ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
            : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
        ]"
      >
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
            {{ intent.label }}
          </h3>
          <div
            :class="[
              'w-6 h-6 rounded-full border-2 flex items-center justify-center',
              wizardState.outputIntent === intent.value
                ? 'border-blue-600 bg-blue-600'
                : 'border-slate-300 dark:border-slate-600'
            ]"
          >
            <svg
              v-if="wizardState.outputIntent === intent.value"
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
          {{ intent.description }}
        </p>
        <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
          <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Includes:</p>
          <ul class="space-y-1">
            <li v-for="item in intent.includes" :key="item" class="text-xs text-slate-500 dark:text-slate-500 flex items-start">
              <svg class="w-3 h-3 text-blue-500 mr-1 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              {{ item }}
            </li>
          </ul>
        </div>
      </button>
    </div>

    <!-- Summary Box -->
    <div class="p-6 rounded-xl border-2 border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
        🎉 Ready to Generate!
      </h3>
      <p class="text-slate-600 dark:text-slate-400 text-sm">
        You've completed all 11 steps. Click "Generate Template" to create your custom template based on your selections.
      </p>
    </div>
  </div>
</template>




