<script setup lang="ts">
import { wizardState } from './wizardState';
import { computed } from 'vue';
import { useI18n } from '@/lib/i18n';

// Import all step components (3 steps)
import Step1FrameworkCategoryOutput from './steps/Step1FrameworkCategoryOutput.vue';
import Step2VisualDesignContent from './steps/Step2VisualDesignContent.vue';
import Step3LlmModel from './steps/Step3LlmModel.vue';

defineProps<{
  title?: string;
  description?: string;
}>();

const { t } = useI18n();

const currentStepComponent = computed(() => {
  const components: Record<number, any> = {
    1: Step1FrameworkCategoryOutput,
    2: Step2VisualDesignContent,
    3: Step3LlmModel,
  };
  return components[wizardState.currentStep] || null;
});

const currentStepTitle = computed(() => {
  const titles: Record<number, string> = {
    1: t.value.wizard?.stepTitles?.step1 || 'Framework, Category & Output',
    2: t.value.wizard?.stepTitles?.step2 || 'Visual Design & Content',
    3: t.value.wizard?.stepTitles?.step3 || 'LLM Model Selection',
  };
  return titles[wizardState.currentStep] || '';
});

const currentStepDescription = computed(() => {
  const descriptions: Record<number, string> = {
    1: t.value.wizard?.stepDescriptions?.step1 || 'Choose your CSS framework, template category, and output format',
    2: t.value.wizard?.stepDescriptions?.step2 || 'Configure pages, layout, theme, UI density, and components',
    3: t.value.wizard?.stepDescriptions?.step3 || 'Select the LLM model for generation and review credits',
  };
  return descriptions[wizardState.currentStep] || '';
});
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 pb-32">
    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8 max-w-6xl">
      <!-- Progress Header -->
      <div class="mb-6 p-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
              {{ title || t.wizard?.title || 'Template Wizard' }}
            </h1>
            <p v-if="description" class="text-sm text-slate-600 dark:text-slate-400 mt-1">
              {{ description }}
            </p>
          </div>
          <div class="text-right">
            <div class="text-xs text-slate-500 dark:text-slate-500">{{ t.wizard?.stepOf || 'Step' }}</div>
            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
              {{ wizardState.currentStep }} / 3
            </div>
          </div>
        </div>
        
        <!-- Step Info -->
        <div class="flex items-center gap-2 text-sm bg-slate-50 dark:bg-slate-900 rounded-lg p-3">
          <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <span class="font-semibold text-slate-900 dark:text-white">{{ currentStepTitle }}</span>
          <span class="text-slate-400 dark:text-slate-600">•</span>
          <span class="text-slate-600 dark:text-slate-400">{{ currentStepDescription }}</span>
        </div>
      </div>

      <!-- Step Content -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-8">
        <component :is="currentStepComponent" />
      </div>

      <!-- Help Text -->
      <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div class="text-sm">
            <p class="font-medium text-blue-900 dark:text-blue-200 mb-1">
              💡 {{ t.wizard?.wizardFirst || 'Wizard-First Design' }}
            </p>
            <p class="text-blue-700 dark:text-blue-300">
              {{ t.wizard?.wizardFirstDescription || 'No free-form prompts - every choice is explicit and deterministic' }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>




