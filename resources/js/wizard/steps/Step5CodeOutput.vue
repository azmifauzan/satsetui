<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { wizardState, OutputFormat } from '../wizardState';
import { useI18n } from '@/lib/i18n';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const { t } = useI18n();
const page = usePage();

// LLM Model interface
interface LlmModel {
  id: string;
  name: string;
  description: string;
  credits_required: number;
  is_free: boolean;
}

// Get user credits from page props
const userCredits = computed(() => page.props.auth?.user?.credits || 0);

// Models from API
const availableModels = ref<LlmModel[]>([]);
const isLoadingModels = ref(true);

// Fetch models from API
async function fetchModels() {
  try {
    isLoadingModels.value = true;
    const response = await axios.get('/api/llm/models');
    availableModels.value = response.data.models;
    
    // Auto-select most expensive available model if none selected
    if (!wizardState.llmModel && availableModels.value.length > 0) {
      // Sort by credits descending to get most expensive first
      const sortedByExpensive = [...availableModels.value]
        .filter(m => m.is_free || userCredits.value >= m.credits_required)
        .sort((a, b) => b.credits_required - a.credits_required);
      
      if (sortedByExpensive.length > 0) {
        const mostExpensive = sortedByExpensive[0];
        selectModel(mostExpensive.id, mostExpensive.credits_required);
      }
    }
  } catch (error) {
    console.error('Failed to fetch LLM models:', error);
  } finally {
    isLoadingModels.value = false;
  }
}

onMounted(() => {
  fetchModels();
});

const outputFormatOptions = computed(() => [
  {
    value: 'html-css' as OutputFormat,
    label: t.value.wizard?.steps?.outputFormat?.htmlCss || 'HTML + CSS',
    description: t.value.wizard?.steps?.outputFormat?.htmlCssDesc || 'Pure HTML dengan CSS murni, tanpa framework JS',
    icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
  },
  {
    value: 'react' as OutputFormat,
    label: t.value.wizard?.steps?.outputFormat?.react || 'React JS',
    description: t.value.wizard?.steps?.outputFormat?.reactDesc || 'React components dengan JSX dan hooks',
    icon: 'M12 14l9-5-9-5-9 5 9 5z',
  },
  {
    value: 'vue' as OutputFormat,
    label: t.value.wizard?.steps?.outputFormat?.vue || 'Vue.js',
    description: t.value.wizard?.steps?.outputFormat?.vueDesc || 'Vue 3 components dengan Composition API',
    icon: 'M12 2L2 19.5h20L12 2z',
  },
  {
    value: 'angular' as OutputFormat,
    label: t.value.wizard?.steps?.outputFormat?.angular || 'Angular',
    description: t.value.wizard?.steps?.outputFormat?.angularDesc || 'Angular components dengan TypeScript',
    icon: 'M3 3l18 18M9 9l12 12',
  },
  {
    value: 'svelte' as OutputFormat,
    label: t.value.wizard?.steps?.outputFormat?.svelte || 'Svelte',
    description: t.value.wizard?.steps?.outputFormat?.svelteDesc || 'Svelte components dengan compile-time optimization',
    icon: 'M13 10V3L4 14h7v7l9-11h-7z',
  },
]);

// Computed: free models (but there are none now)
const freeModels = computed(() => {
  return availableModels.value.filter(m => m.is_free);
});

// Computed: all models sorted by credit (smallest to largest)
const allModels = computed(() => {
  return [...availableModels.value].sort((a, b) => a.credits_required - b.credits_required);
});

function selectOutputFormat(format: OutputFormat) {
  wizardState.outputFormat = format;
}

function selectModel(modelId: string, creditsRequired: number) {
  // Check if user has enough credits
  if (creditsRequired > 0 && userCredits.value < creditsRequired) {
    return; // Don't allow selection
  }
  
  wizardState.llmModel = modelId;
  wizardState.modelCredits = creditsRequired;
}

function isModelDisabled(creditsRequired: number): boolean {
  return creditsRequired > 0 && userCredits.value < creditsRequired;
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
            {{ userCredits }} {{ t.common?.credits || 'kredit' }}
          </span>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoadingModels" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
        <p class="mt-2 text-slate-600 dark:text-slate-400">{{ t.wizard?.steps?.llmModel?.loadingModels || 'Memuat model...' }}</p>
      </div>

      <!-- All Models Section -->
      <div v-else-if="allModels.length > 0" class="space-y-4">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
          <span class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded">
            {{ t.wizard?.steps?.llmModel?.allModelsLabel || 'SEMUA MODEL' }}
          </span>
          {{ t.wizard?.steps?.llmModel?.selectModel || 'Pilih Model yang Sesuai' }}
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <button
            v-for="model in allModels"
            :key="model.id"
            @click="selectModel(model.id, model.credits_required)"
            :disabled="isModelDisabled(model.credits_required)"
            :class="[
              'p-5 rounded-xl border-2 text-left transition-all',
              isModelDisabled(model.credits_required)
                ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-900 border-slate-200 dark:border-slate-800'
                : wizardState.llmModel === model.id
                  ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 hover:shadow-lg'
                  : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400 hover:shadow-lg'
            ]"
          >
            <div class="flex items-center justify-between mb-3">
              <h4 class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ model.name }}
              </h4>
              <div
                :class="[
                  'w-6 h-6 rounded-full border-2 flex items-center justify-center',
                  wizardState.llmModel === model.id && !isModelDisabled(model.credits_required)
                    ? 'border-blue-600 bg-blue-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="wizardState.llmModel === model.id && !isModelDisabled(model.credits_required)"
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
            <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
              <p class="text-xs font-semibold text-blue-700 dark:text-blue-300">
                💎 {{ model.credits_required }} {{ t.common?.credits || 'kredit' }}
              </p>
              <span 
                v-if="isModelDisabled(model.credits_required)"
                class="text-xs px-2 py-1 rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300"
              >
                {{ t.wizard?.steps?.llmModel?.insufficientCredits || 'Kredit tidak cukup' }}
              </span>
              <span 
                v-else-if="model.credits_required <= 5"
                class="text-xs px-2 py-1 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300"
              >
                {{ t.wizard?.steps?.llmModel?.economical || 'Ekonomis' }}
              </span>
            </div>
          </button>
        </div>
      </div>

      <!-- No Models Available -->
      <div v-else class="text-center py-8">
        <p class="text-slate-600 dark:text-slate-400">{{ t.wizard?.steps?.llmModel?.noModelsAvailable || 'Tidak ada model yang tersedia saat ini.' }}</p>
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




