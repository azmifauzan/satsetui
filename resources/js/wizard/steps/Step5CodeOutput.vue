<script setup lang="ts">
import { computed } from 'vue';
import { wizardState, OutputFormat, LlmModel, ModelTier } from '../wizardState';
import { useI18n } from '@/lib/i18n';
import { usePage } from '@inertiajs/vue3';

const { t } = useI18n();
const page = usePage();

// Get user credits from page props
const userCredits = computed(() => page.props.auth?.user?.credits || 0);

const outputFormatOptions: { value: OutputFormat; label: string; description: string; icon: string }[] = [
  {
    value: 'html-css',
    label: 'HTML + CSS',
    description: 'Pure HTML dengan CSS murni, tanpa framework JS',
    icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
  },
  {
    value: 'react',
    label: 'React JS',
    description: 'React components dengan JSX dan hooks',
    icon: 'M12 14l9-5-9-5-9 5 9 5z',
  },
  {
    value: 'vue',
    label: 'Vue.js',
    description: 'Vue 3 components dengan Composition API',
    icon: 'M12 2L2 19.5h20L12 2z',
  },
  {
    value: 'angular',
    label: 'Angular',
    description: 'Angular components dengan TypeScript',
    icon: 'M3 3l18 18M9 9l12 12',
  },
  {
    value: 'svelte',
    label: 'Svelte',
    description: 'Svelte components dengan compile-time optimization',
    icon: 'M13 10V3L4 14h7v7l9-11h-7z',
  },
];

const freeModels: { value: LlmModel; label: string; description: string }[] = [
  {
    value: 'gemini-flash',
    label: 'Gemini Flash',
    description: 'Gratis untuk semua pengguna, generasi cepat',
  },
];

const premiumModels: { value: LlmModel; label: string; description: string; cost: string }[] = [
  {
    value: 'gemini-pro',
    label: 'Gemini Pro',
    description: 'Model premium Google, hasil lebih detail',
    cost: '10 credits',
  },
  {
    value: 'gpt-4',
    label: 'GPT-4',
    description: 'OpenAI GPT-4, kualitas tertinggi',
    cost: '20 credits',
  },
  {
    value: 'claude-3',
    label: 'Claude 3',
    description: 'Anthropic Claude, fokus keamanan & akurasi',
    cost: '15 credits',
  },
];

function selectOutputFormat(format: OutputFormat) {
  wizardState.outputFormat = format;
}

function selectModel(model: LlmModel, tier: ModelTier) {
  // Check if premium model and user has no credits
  if (tier === 'premium' && userCredits.value === 0) {
    return; // Don't allow selection
  }
  
  wizardState.llmModel = model;
  wizardState.modelTier = tier;
}

function isPremiumDisabled(): boolean {
  return userCredits.value === 0;
}
</script>

<template>
  <div class="space-y-10">
    <!-- Output Format Selection -->
    <div class="space-y-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.outputFormat?.title || 'Pilih Format Output' }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400">
          {{ t.wizard?.steps?.outputFormat?.description || 'Pilih framework atau format teknologi untuk template yang akan dihasilkan.' }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <button
          v-for="format in outputFormatOptions"
          :key="format.value"
          @click="selectOutputFormat(format.value)"
          :class="[
            'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg',
            wizardState.outputFormat === format.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="format.icon" />
              </svg>
            </div>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                wizardState.outputFormat === format.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.outputFormat === format.value"
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
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
            {{ format.label }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            {{ format.description }}
          </p>
        </button>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t-2 border-slate-200 dark:border-slate-700"></div>

    <!-- LLM Model Selection -->
    <div class="space-y-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.llmModel?.title || 'Pilih Model LLM' }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400 mb-2">
          {{ t.wizard?.steps?.llmModel?.description || 'Pilih model AI yang akan menghasilkan template Anda.' }}
        </p>
        <div class="flex items-center gap-2 text-sm">
          <span class="text-slate-600 dark:text-slate-400">
            {{ t.wizard?.steps?.llmModel?.credits || 'Kredit Anda:' }}
          </span>
          <span class="px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-semibold">
            {{ userCredits }} credits
          </span>
        </div>
      </div>

      <!-- Free Models -->
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
          <span class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded">
            GRATIS
          </span>
          Model Gratis
        </h3>
        
        <div class="grid grid-cols-1 gap-4">
          <button
            v-for="model in freeModels"
            :key="model.value"
            @click="selectModel(model.value, 'free')"
            :class="[
              'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg',
              wizardState.llmModel === model.value
                ? 'border-green-600 bg-green-50 dark:bg-green-900/20'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-green-400'
            ]"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h4 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">
                  {{ model.label }}
                </h4>
                <p class="text-slate-600 dark:text-slate-400 text-sm">
                  {{ model.description }}
                </p>
              </div>
              <div
                :class="[
                  'w-6 h-6 rounded-full border-2 flex items-center justify-center ml-4',
                  wizardState.llmModel === model.value
                    ? 'border-green-600 bg-green-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="wizardState.llmModel === model.value"
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
          </button>
        </div>
      </div>

      <!-- Premium Models -->
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
          <span class="px-2 py-1 text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded">
            PREMIUM
          </span>
          Model Premium
        </h3>

        <div v-if="isPremiumDisabled()" class="p-4 rounded-xl border-2 border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div>
              <h4 class="font-semibold text-amber-900 dark:text-amber-300 mb-1">
                {{ t.wizard?.steps?.llmModel?.noCreditTitle || 'Kredit Habis' }}
              </h4>
              <p class="text-amber-800 dark:text-amber-400 text-sm">
                {{ t.wizard?.steps?.llmModel?.noCreditDesc || 'Anda tidak memiliki kredit. Silakan isi ulang kredit untuk menggunakan model premium.' }}
              </p>
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <button
            v-for="model in premiumModels"
            :key="model.value"
            @click="selectModel(model.value, 'premium')"
            :disabled="isPremiumDisabled()"
            :class="[
              'p-5 rounded-xl border-2 text-left transition-all',
              isPremiumDisabled()
                ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-900 border-slate-200 dark:border-slate-800'
                : wizardState.llmModel === model.value
                  ? 'border-amber-600 bg-amber-50 dark:bg-amber-900/20 hover:shadow-lg'
                  : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-amber-400 hover:shadow-lg'
            ]"
          >
            <div class="flex items-center justify-between mb-3">
              <h4 class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ model.label }}
              </h4>
              <div
                :class="[
                  'w-6 h-6 rounded-full border-2 flex items-center justify-center',
                  wizardState.llmModel === model.value && !isPremiumDisabled()
                    ? 'border-amber-600 bg-amber-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="wizardState.llmModel === model.value && !isPremiumDisabled()"
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
              {{ model.description }}
            </p>
            <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
              <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">
                💎 {{ model.cost }}
              </p>
            </div>
          </button>
        </div>
      </div>

      <!-- Summary Box -->
      <div class="p-6 rounded-xl border-2 border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
          🎉 {{ t.wizard?.steps?.llmModel?.ready || 'Siap untuk Generate!' }}
        </h3>
        <p class="text-slate-600 dark:text-slate-400 text-sm">
          {{ t.wizard?.steps?.llmModel?.readyDesc || 'Anda telah menyelesaikan semua 5 langkah. Klik tombol "Generate Template" untuk membuat template kustom Anda.' }}
        </p>
      </div>
    </div>
  </div>
</template>




