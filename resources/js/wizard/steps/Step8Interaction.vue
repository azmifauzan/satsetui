<script setup lang="ts">
import { wizardState, InteractionLevel } from '../wizardState';

const interactionOptions: { value: InteractionLevel; label: string; description: string; examples: string }[] = [
  {
    value: 'static',
    label: 'Static',
    description: 'No animations, instant transitions, minimal interactivity',
    examples: 'Maximum performance, simplicity'
  },
  {
    value: 'moderate',
    label: 'Moderate',
    description: 'Hover effects, smooth transitions, basic feedback',
    examples: 'Recommended for most applications'
  },
  {
    value: 'rich',
    label: 'Rich',
    description: 'Animations, micro-interactions, loading skeletons, parallax',
    examples: 'Marketing sites, premium feel'
  },
];
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        Interaction Level
      </h2>
      <p class="text-slate-600 dark:text-slate-400">
        Define animation and interaction richness for your template.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <button
        v-for="interaction in interactionOptions"
        :key="interaction.value"
        @click="wizardState.interaction = interaction.value"
        :class="[
          'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg',
          wizardState.interaction === interaction.value
            ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 scale-105'
            : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
        ]"
      >
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xl font-semibold text-slate-900 dark:text-white">
            {{ interaction.label }}
          </h3>
          <div
            :class="[
              'w-6 h-6 rounded-full border-2 flex items-center justify-center',
              wizardState.interaction === interaction.value
                ? 'border-blue-600 bg-blue-600'
                : 'border-slate-300 dark:border-slate-600'
            ]"
          >
            <svg
              v-if="wizardState.interaction === interaction.value"
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
        <p class="text-slate-600 dark:text-slate-400 text-sm mb-2">
          {{ interaction.description }}
        </p>
        <p class="text-slate-500 dark:text-slate-500 text-xs italic">
          {{ interaction.examples }}
        </p>
      </button>
    </div>
  </div>
</template>




