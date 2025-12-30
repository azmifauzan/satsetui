<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import WizardLayout from '@/wizard/WizardLayout.vue';
import WizardNavigation from '@/wizard/WizardNavigation.vue';
import { wizardState, blueprintJSON } from '@/wizard/wizardState';
import { useI18n } from '@/lib/i18n';

interface Props {
  auth?: {
    user: {
      name: string;
      email: string;
    };
  };
}

const props = defineProps<Props>();
const { t } = useI18n();

const isGenerating = ref(false);
const showBlueprintPreview = ref(false);
const generationError = ref<string | null>(null);
const generationProgress = ref({
  currentPage: '',
  percentage: 0,
  currentIndex: 0,
  totalPages: 0,
});

async function handleGenerate() {
  isGenerating.value = true;
  generationError.value = null;
  
  try {
    const blueprint = blueprintJSON.value;
    
    console.log('Submitting Blueprint:', blueprint);
    
    // Submit to backend API using axios (handles CSRF automatically)
    const response = await axios.post('/generation/generate', {
      blueprint,
      project_name: `Template ${new Date().toLocaleString()}`,
      model_name: wizardState.llmModel // Send selected model
    });

    const result = response.data;

    if (!result.success) {
      throw new Error(result.error || 'Failed to start generation');
    }

    const generationId = result.generation_id;
    generationProgress.value.totalPages = result.total_pages;

    // Start progressive generation
    await progressiveGenerate(generationId);

    // Redirect to result page after completion
    router.visit(`/generation/${generationId}`);
    
  } catch (error) {
    console.error('Generation exception:', error);
    generationError.value = error instanceof Error ? error.message : 'An unexpected error occurred. Please try again.';
    isGenerating.value = false;
  }
}

async function progressiveGenerate(generationId: number) {
  try {
    // Keep generating until all pages are done
    while (generationProgress.value.currentIndex < generationProgress.value.totalPages) {
      // Call next generation using axios
      const response = await axios.post(`/generation/${generationId}/next`);

      const result = response.data;

      if (!result.success) {
        throw new Error(result.error || 'Generation failed');
      }

      // Update progress
      generationProgress.value.currentPage = result.page;
      generationProgress.value.currentIndex = result.current_index;
      generationProgress.value.percentage = result.progress_percentage;

      if (result.completed) {
        break;
      }

      // Small delay between pages
      await new Promise(resolve => setTimeout(resolve, 300));
    }
  } catch (error) {
    throw error;
  }
}
</script>

<template>
  <AppLayout>
    <Head :title="t.wizard?.title || 'Template Wizard'" />
    
    <div class="relative">
      <!-- Blueprint Preview Toggle Button (Fixed) -->
      <button
        @click="showBlueprintPreview = !showBlueprintPreview"
        class="fixed top-20 right-6 z-30 px-4 py-2 bg-slate-900 dark:bg-slate-700 text-white rounded-lg shadow-lg hover:bg-slate-800 dark:hover:bg-slate-600 transition-colors text-sm font-medium"
      >
        {{ showBlueprintPreview ? (t.wizard?.hideBlueprint || 'Hide Blueprint') : (t.wizard?.showBlueprint || 'Show Blueprint') }}
      </button>

      <!-- Blueprint Preview Sidebar -->
      <div
        v-if="showBlueprintPreview"
        class="fixed right-0 top-16 bottom-0 w-96 bg-slate-900 dark:bg-slate-950 border-l border-slate-700 dark:border-slate-800 z-20 overflow-auto shadow-2xl"
      >
        <div class="p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">{{ t.wizard?.blueprintPreview || 'Blueprint Preview' }}</h3>
            <button
              @click="showBlueprintPreview = false"
              class="p-1 hover:bg-slate-800 dark:hover:bg-slate-900 rounded transition-colors"
            >
              <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap break-words bg-slate-950 dark:bg-black p-4 rounded-lg overflow-auto">{{ JSON.stringify(blueprintJSON, null, 2) }}</pre>
        </div>
      </div>
      
      <!-- Main Content -->
      <div :class="['transition-all duration-300', showBlueprintPreview ? 'mr-96' : 'mr-0']">
        <WizardLayout
          :title="t.wizard?.title || 'Template Wizard'"
          :description="t.wizard?.description || 'Create your custom frontend template'"
          @submit="handleGenerate"
        />

        <!-- Navigation Footer -->
        <WizardNavigation @submit="handleGenerate" />
      </div>

      <!-- Loading Overlay -->
      <div
        v-if="isGenerating"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center"
      >
        <div class="bg-white dark:bg-slate-800 rounded-xl p-8 shadow-2xl max-w-md w-full mx-4">
          <div class="flex flex-col items-center">
            <div class="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
              {{ t.wizard?.generating || 'Generating Template...' }}
            </h3>
            
            <!-- Progress Bar -->
            <div class="w-full mt-4 mb-2">
              <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-slate-600 dark:text-slate-400">
                  {{ generationProgress.currentPage ? `${t.wizard?.generatingPage || 'Menghasilkan halaman'} ${generationProgress.currentPage}...` : t.wizard?.startingGeneration || 'Memulai generasi...' }}
                </span>
                <span class="font-medium text-slate-900 dark:text-white">
                  {{ generationProgress.percentage }}%
                </span>
              </div>
              <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                <div
                  class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                  :style="{ width: `${generationProgress.percentage}%` }"
                ></div>
              </div>
              <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 text-center">
                {{ t.wizard?.pageOf || 'Halaman' }} {{ generationProgress.currentIndex }} {{ t.common?.of || 'dari' }} {{ generationProgress.totalPages }}
              </div>
            </div>

            <p class="text-slate-600 dark:text-slate-400 text-center text-sm mt-2">
              {{ t.wizard?.generatingDescription || 'Please wait while we generate your template' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Error Alert -->
      <div
        v-if="generationError"
        class="fixed bottom-6 right-6 z-50 max-w-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 shadow-lg"
      >
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div class="flex-1">
            <h4 class="font-medium text-red-900 dark:text-red-200">{{ t.wizard?.generationFailed || 'Generasi Gagal' }}</h4>
            <p class="text-sm text-red-700 dark:text-red-300 mt-1">{{ generationError }}</p>
          </div>
          <button
            @click="generationError = null"
            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-200"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>





