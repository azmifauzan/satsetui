<script setup lang="ts">
import { wizardState, canGoBack, canProceedToNext, isLastStep, isCurrentStepValid, previousStep, nextStep, getStepTitle } from './wizardState';
import { useI18n } from '@/lib/i18n';

const emit = defineEmits<{
  (e: 'submit'): void;
}>();

const { t } = useI18n();
const allSteps = Array.from({ length: 5 }, (_, i) => i + 1);

function goToPreviousStep() {
  if (canGoBack.value) {
    previousStep();
  }
}

function goToNextStep() {
  if (canProceedToNext.value) {
    nextStep();
  }
}

function handleGenerate() {
  emit('submit');
}
</script>

<template>
  <div class="bg-white dark:bg-slate-800 border-t-2 border-slate-200 dark:border-slate-700 px-6 py-4 sticky bottom-0 z-10">
    <div class="container mx-auto max-w-6xl">
      <!-- Progress Indicators -->
      <div class="mb-4">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-slate-600 dark:text-slate-400">
            {{ t.wizard?.stepOf || 'Step' }} {{ wizardState.currentStep }} {{ t.common?.of || 'of' }} 5
          </span>
          <span class="text-sm text-slate-500 dark:text-slate-500">
            {{ getStepTitle(wizardState.currentStep) }}
          </span>
        </div>
        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
          <div
            class="bg-blue-600 h-full transition-all duration-300 ease-out"
            :style="{ width: `${(wizardState.currentStep / 5) * 100}%` }"
          />
        </div>
      </div>

      <!-- Navigation Buttons -->
      <div class="flex items-center justify-between">
        <button
          @click="goToPreviousStep"
          :disabled="!canGoBack"
          :class="[
            'px-6 py-2.5 rounded-lg font-medium transition-all flex items-center gap-2',
            canGoBack
              ? 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600'
              : 'bg-slate-50 dark:bg-slate-900 text-slate-400 dark:text-slate-600 cursor-not-allowed'
          ]"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          {{ t.common?.previous || 'Previous' }}
        </button>

        <div class="flex items-center gap-2">
          <button
            v-for="step in allSteps"
            :key="step"
            @click="wizardState.currentStep = step"
            :class="[
              'w-8 h-8 rounded-full font-medium text-sm transition-all',
              step === wizardState.currentStep
                ? 'bg-blue-600 text-white scale-110'
                : step < wizardState.currentStep
                ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900/50'
                : 'bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-600'
            ]"
          >
            <span v-if="step < wizardState.currentStep">
              <svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </span>
            <span v-else>{{ step }}</span>
          </button>
        </div>

        <button
          v-if="!isLastStep"
          @click="goToNextStep"
          :disabled="!canProceedToNext"
          :class="[
            'px-6 py-2.5 rounded-lg font-medium transition-all flex items-center gap-2',
            canProceedToNext
              ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-500/30'
              : 'bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-500 cursor-not-allowed'
          ]"
        >
          {{ t.common?.next || 'Next' }}
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <button
          v-else
          @click="handleGenerate"
          :disabled="!isCurrentStepValid"
          :class="[
            'px-8 py-2.5 rounded-lg font-medium transition-all flex items-center gap-2',
            isCurrentStepValid
              ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 shadow-lg shadow-blue-500/30'
              : 'bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-500 cursor-not-allowed'
          ]"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          {{ t.wizard?.generateTemplate || 'Generate Template' }}
        </button>
      </div>
    </div>
  </div>
</template>




