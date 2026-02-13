<script setup lang="ts">
import { wizardState } from './wizardState';
import { useI18n } from '@/lib/i18n';
import { computed, watch } from 'vue';

interface LlmModel {
  id: string;
  name: string;
  description: string;
  credits_required: number;
}

interface Props {
  models: LlmModel[];
  userCredits: number;
}

const props = withDefaults(defineProps<Props>(), {
  models: () => [],
  userCredits: 0,
});

// Set default model when models load
watch(() => props.models, (newModels) => {
  if (newModels.length > 0 && !wizardState.llmModel) {
    wizardState.llmModel = newModels[0].id;
  }
}, { immediate: true });

const selectedModel = computed(() => {
  return props.models.find(m => m.id === wizardState.llmModel) || props.models[0];
});

const emit = defineEmits<{
  (e: 'submit'): void;
}>();

const { currentLang } = useI18n();

const isValid = computed(() => {
  return !!wizardState.category;
});

function handleGenerate() {
  emit('submit');
}
</script>

<template>
  <div class="bg-white dark:bg-slate-800 border-t-2 border-slate-200 dark:border-slate-700 px-6 py-4 sticky bottom-0 z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
    <div class="container mx-auto max-w-6xl">
      <div class="flex items-center justify-between gap-4">
        <!-- Model Selector -->
        <div v-if="props.models.length > 0" class="flex items-center gap-3 px-4 py-2 bg-slate-100 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
            {{ currentLang === 'en' ? 'AI Model:' : 'Model AI:' }}
          </label>
          <select
            v-model="wizardState.llmModel"
            class="px-4 py-2 bg-white dark:bg-slate-800 border-2 border-blue-200 dark:border-blue-700 rounded-lg text-sm font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm"
          >
            <option v-for="model in props.models" :key="model.id" :value="model.id">
              {{ model.name }} ({{ model.credits_required }} {{ currentLang === 'en' ? 'credits' : 'kredit' }})
            </option>
          </select>
          <div class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">{{ props.userCredits }}</span>
            <span class="text-xs text-blue-600 dark:text-blue-400">{{ currentLang === 'en' ? 'available' : 'tersedia' }}</span>
          </div>
        </div>

        <!-- Summary -->
        <div class="text-sm text-slate-600 dark:text-slate-400 flex-1">
          <span v-if="wizardState.category" class="inline-flex items-center gap-1.5">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
            <span class="font-medium text-slate-900 dark:text-white">{{ wizardState.category }}</span>
            <span v-if="wizardState.colorScheme" class="text-slate-400 dark:text-slate-500">·</span>
            <span v-if="wizardState.colorScheme">{{ wizardState.colorScheme }}</span>
            <span v-if="wizardState.components.length > 0" class="text-slate-400 dark:text-slate-500">·</span>
            <span v-if="wizardState.components.length > 0">{{ wizardState.components.length }} {{ currentLang === 'en' ? 'components' : 'komponen' }}</span>
          </span>
          <span v-else class="text-slate-400 dark:text-slate-500">
            {{ currentLang === 'en' ? 'Select a category to get started' : 'Pilih kategori untuk memulai' }}
          </span>
        </div>

        <!-- Generate Button -->
        <button
          @click="handleGenerate"
          :disabled="!isValid"
          :class="[
            'px-8 py-3 rounded-lg font-semibold transition-all flex items-center gap-2 text-base',
            isValid
              ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 shadow-lg shadow-blue-500/30 hover:shadow-xl hover:scale-[1.02]'
              : 'bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-500 cursor-not-allowed'
          ]"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          {{ currentLang === 'en' ? 'Generate Satset' : 'Generate Satset' }}
        </button>
      </div>
    </div>
  </div>
</template>




