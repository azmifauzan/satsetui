<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue';
import { wizardState, syncCalculatedCredits, extraPageCredits } from '../wizardState';
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
  estimation_confidence?: string;
  estimation_sample_count?: number;
  estimation_source?: string;
  original_estimate?: number;
}

// Get user credits from page props
const userCredits = computed(() => page.props.auth?.user?.credits || 0);

// Models from API
const availableModels = ref<LlmModel[]>([]);
const isLoadingModels = ref(true);

// Fetch models from API with wizard context for better estimates
async function fetchModels() {
  try {
    isLoadingModels.value = true;
    
    // Build query params with wizard context
    const params = new URLSearchParams();
    if (wizardState.category) {
      params.append('category', wizardState.category);
    }
    if (wizardState.framework) {
      params.append('framework', wizardState.framework);
    }
    
    // Calculate total pages (regular + custom + component showcase)
    const totalPages = wizardState.pages.length + 
                       wizardState.customPages.length + 
                       wizardState.components.length + 
                       wizardState.customComponents.length;
    params.append('page_count', totalPages.toString());
    
    // Total components selected
    const totalComponents = wizardState.components.length + wizardState.customComponents.length;
    params.append('component_count', totalComponents.toString());
    
    const response = await axios.get(`/api/llm/models?${params.toString()}`);
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
  syncCalculatedCredits();
});

// Watch for changes that affect credit calculation
// Also re-fetch models when context changes for better estimates
watch([() => wizardState.pages, () => wizardState.customPages, () => wizardState.components, () => wizardState.customComponents], () => {
  syncCalculatedCredits();
  // Re-fetch models with updated context for learned estimates
  fetchModels();
}, { deep: true });

watch([() => wizardState.modelCredits], () => {
  syncCalculatedCredits();
}, { deep: true });

// Computed: all models sorted by credit (smallest to largest)
const allModels = computed(() => {
  return [...availableModels.value].sort((a, b) => a.credits_required - b.credits_required);
});

function selectModel(modelId: string, creditsRequired: number) {
  // Check if user has enough credits for total calculated credits (with margins)
  const totalRequired = getTotalCreditsForModel(creditsRequired);
  if (totalRequired > 0 && userCredits.value < totalRequired) {
    return; // Don't allow selection
  }
  
  wizardState.llmModel = modelId;
  wizardState.modelCredits = creditsRequired;
  syncCalculatedCredits();
}

function isModelDisabled(creditsRequired: number): boolean {
  const totalRequired = getTotalCreditsForModel(creditsRequired);
  return totalRequired > 0 && userCredits.value < totalRequired;
}

function getTotalCreditsForModel(creditsRequired: number): number {
  // Components are now showcase pages, so only use extraPageCredits
  const subtotal = creditsRequired + extraPageCredits.value;
  // Apply error margin (10%) and profit margin (5%)
  const errorMargin = 0.10;
  const profitMargin = 0.05;
  return Math.ceil(subtotal * (1 + errorMargin) * (1 + profitMargin));
}

// Computed: Total counts for summary
// Total pages includes: regular pages + custom pages + component showcase pages
const totalPageCount = computed(() => {
  const regularPages = wizardState.pages.length;
  const customPages = wizardState.customPages.length;
  const componentPages = wizardState.components.length; // Each component becomes a showcase page
  const customComponentPages = wizardState.customComponents.length;
  
  return regularPages + customPages + componentPages + customComponentPages;
});

const totalComponentCount = computed(() => {
  return wizardState.components.length + wizardState.customComponents.length;
});

const allPages = computed(() => {
  const regularPages = wizardState.pages;
  const customPages = wizardState.customPages.map(p => p.name);
  const componentPages = wizardState.components.map(c => {
    const displayName = c.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    return `${displayName} (Showcase)`;
  });
  const customComponentPages = wizardState.customComponents.map(c => `${c.name} (Showcase)`);
  
  return [...regularPages, ...customPages, ...componentPages, ...customComponentPages];
});
</script>

<template>
  <div class="space-y-6">
    <!-- Template Name Input -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
      <div class="flex items-start gap-3 mb-4">
        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
        </div>
        <div class="flex-1">
          <label for="templateName" class="block text-lg font-bold text-slate-900 dark:text-white mb-1">
            {{ t.wizard?.steps?.llmModel?.templateNameLabel || 'Nama Template' }}
          </label>
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            {{ t.wizard?.steps?.llmModel?.templateNameDesc || 'Berikan nama untuk template ini agar mudah diidentifikasi nanti' }}
          </p>
        </div>
      </div>
      
      <input
        id="templateName"
        v-model="wizardState.templateName"
        type="text"
        placeholder="Contoh: Dashboard Admin E-commerce"
        class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition-colors"
        maxlength="100"
      />
      <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
        {{ wizardState.templateName.length }}/100 karakter
      </p>
    </div>

    <!-- Template Summary -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900 rounded-2xl p-6 border border-blue-200 dark:border-slate-700">
      <div class="flex items-start gap-3 mb-4">
        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-600 dark:bg-blue-500 flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1">
            {{ t.wizard?.steps?.llmModel?.summaryTitle }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            {{ t.wizard?.steps?.llmModel?.summaryDesc }}
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Total Pages -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-600 dark:text-slate-400">
              {{ t.wizard?.steps?.llmModel?.totalPages }}
            </span>
            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
              {{ totalPageCount }}
            </span>
          </div>
          <div class="space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-500 dark:text-slate-500">
                {{ t.wizard?.steps?.llmModel?.predefinedPages }}
              </span>
              <span class="font-semibold text-slate-700 dark:text-slate-300">
                {{ wizardState.pages.length }}
              </span>
            </div>
            <div v-if="wizardState.customPages.length > 0" class="flex items-center justify-between text-xs">
              <span class="text-slate-500 dark:text-slate-500">
                {{ t.wizard?.steps?.llmModel?.customPages }}
              </span>
              <span class="font-semibold text-slate-700 dark:text-slate-300">
                {{ wizardState.customPages.length }}
              </span>
            </div>
            <div v-if="wizardState.components.length > 0" class="flex items-center justify-between text-xs">
              <span class="text-slate-500 dark:text-slate-500">
                {{ t.wizard?.steps?.llmModel?.componentShowcasePages }}
              </span>
              <span class="font-semibold text-slate-700 dark:text-slate-300">
                {{ wizardState.components.length }}
              </span>
            </div>
            <div v-if="wizardState.customComponents.length > 0" class="flex items-center justify-between text-xs">
              <span class="text-slate-500 dark:text-slate-500">
                {{ t.wizard?.steps?.llmModel?.customComponentPages }}
              </span>
              <span class="font-semibold text-slate-700 dark:text-slate-300">
                {{ wizardState.customComponents.length }}
              </span>
            </div>
          </div>
        </div>

        <!-- Total Components -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-600 dark:text-slate-400">
              {{ t.wizard?.steps?.llmModel?.totalComponents }}
            </span>
            <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
              {{ totalComponentCount }}
            </span>
          </div>
          <div class="space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-500 dark:text-slate-500">
                {{ t.wizard?.steps?.llmModel?.predefinedComponents }}
              </span>
              <span class="font-semibold text-slate-700 dark:text-slate-300">
                {{ wizardState.components.length }}
              </span>
            </div>
            <div v-if="wizardState.customComponents.length > 0" class="flex items-center justify-between text-xs">
              <span class="text-slate-500 dark:text-slate-500">
                {{ t.wizard?.steps?.llmModel?.customComponents }}
              </span>
              <span class="font-semibold text-slate-700 dark:text-slate-300">
                {{ wizardState.customComponents.length }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pages List -->
      <div class="mt-4 bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
          {{ t.wizard?.steps?.llmModel?.pagesList }}
        </h4>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="(page, index) in allPages"
            :key="index"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800"
          >
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
            </svg>
            {{ page }}
          </span>
        </div>
      </div>
    </div>

    <!-- LLM Model Selection -->
    <div class="space-y-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.llmModel?.title }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400 mb-4">
          {{ t.wizard?.steps?.llmModel?.description }}
        </p>
        
        <!-- Credits & Ready Status -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
          <div class="flex items-center gap-1.5 text-sm">
            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
              <path d="M7 3h10l4 4-9 14L3 7l4-4zm5 2L9 7h6l-3-2zm-6 3l3.5 8L5 8zm7.5 8L17 8l-3.5 8z"/>
            </svg>
            <span class="px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-semibold">
              {{ userCredits }} {{ t.common?.credits || 'kredit' }}
            </span>
          </div>
          <div class="flex-1 text-sm text-slate-600 dark:text-slate-400">
            {{ t.wizard?.steps?.llmModel?.readyDesc }}
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoadingModels" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
        <p class="mt-2 text-slate-600 dark:text-slate-400">{{ t.wizard?.steps?.llmModel?.loadingModels }}</p>
      </div>

      <!-- All Models Section -->
      <div v-else-if="allModels.length > 0" class="space-y-4">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
          <span class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded">
            {{ t.wizard?.steps?.llmModel?.allModelsLabel }}
          </span>
          {{ t.wizard?.steps?.llmModel?.selectModel }}
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
                  'w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0',
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
            
            <p class="text-slate-600 dark:text-slate-400 text-sm mb-4">
              {{ model.description }}
            </p>
            
            <!-- Total Credits -->
            <div class="flex items-center justify-center gap-2 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 mb-3">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7 3h10l4 4-9 14L3 7l4-4zm5 2L9 7h6l-3-2zm-6 3l3.5 8L5 8zm7.5 8L17 8l-3.5 8z"/>
              </svg>
              <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                {{ getTotalCreditsForModel(model.credits_required) }}
              </span>
              <span class="text-sm text-blue-600 dark:text-blue-400">
                {{ t.common?.credits || 'kredit' }}
              </span>
            </div>
            
            <!-- Status Badge -->
            <div class="flex items-center justify-between">
              <span 
                v-if="isModelDisabled(model.credits_required)"
                class="text-xs px-2 py-1 rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300"
              >
                {{ t.wizard?.steps?.llmModel?.insufficientCredits }}
              </span>
              <span 
                v-else-if="model.credits_required <= 5"
                class="text-xs px-2 py-1 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300"
              >
                {{ t.wizard?.steps?.llmModel?.economical }}
              </span>
            </div>
          </button>
        </div>
      </div>

      <!-- No Models Available -->
      <div v-else class="text-center py-8">
        <p class="text-slate-600 dark:text-slate-400">{{ t.wizard?.steps?.llmModel?.noModelsAvailable }}</p>
      </div>
    </div>
  </div>
</template>
