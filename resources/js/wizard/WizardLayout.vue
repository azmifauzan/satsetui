<script setup lang="ts">
import { useI18n } from '@/lib/i18n';
import UnifiedWizardStep from './steps/UnifiedWizardStep.vue';

defineProps<{
  title?: string;
  description?: string;
}>();

const emit = defineEmits<{
  submit: [];
}>();

const { currentLang } = useI18n();
</script>

<template>
  <div class="pb-32">
    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8 max-w-6xl">
      <!-- Header -->
      <div class="mb-6 p-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
              {{ title || (currentLang === 'en' ? 'SatsetUI Wizard' : 'Wizard SatsetUI') }}
            </h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
              {{ description || (currentLang === 'en' ? 'Configure your project in one step — sat-set!' : 'Konfigurasi proyek kamu dalam satu langkah — sat-set!') }}
            </p>
          </div>
          <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">⚡ Sat-set!</span>
          </div>
        </div>
      </div>

      <!-- Wizard Content -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-8">
        <UnifiedWizardStep />
      </div>

      <!-- Help Text -->
      <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div class="text-sm">
            <p class="font-medium text-blue-900 dark:text-blue-200 mb-1">
              {{ currentLang === 'en' ? '💡 Wizard-First Design' : '💡 Desain Wizard-First' }}
            </p>
            <p class="text-blue-800/80 dark:text-blue-300/80">
              {{ currentLang === 'en' 
                ? 'All decisions are made through this wizard — no free-form prompts. Same selections always produce the same results.' 
                : 'Semua keputusan dibuat melalui wizard ini — tanpa prompt bebas. Pilihan yang sama selalu menghasilkan hasil yang sama.' 
              }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
