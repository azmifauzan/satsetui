<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { useI18n } from '@/lib/i18n';

interface PageProgress {
  status: 'pending' | 'generating' | 'completed' | 'failed';
  content: string | null;
  error: string | null;
  processing_time: number;
}

interface Generation {
  id: number;
  user_id: number;
  project_id: number;
  model_used: string;
  credits_used: number;
  status: string;
  current_status: string;
  current_page_index: number;
  total_pages: number;
  progress_data: Record<string, PageProgress>;
  mcp_prompt: string;
  generated_content: string | null;
  error_message: string | null;
  processing_time: number;
  started_at: string;
  completed_at: string | null;
  created_at: string;
  updated_at: string;
  project: {
    id: number;
    name: string;
    blueprint: any;
    status: string;
    generated_at: string;
  };
}

interface Props {
  generation: Generation;
}

const props = defineProps<Props>();
const { t } = useI18n();

const activeTab = ref<'code' | 'preview' | 'blueprint' | 'prompt'>('code');
const generationData = ref<Generation>(props.generation);
const isGenerating = ref(false);
const progressInterval = ref<number | null>(null);
const refinementPrompt = ref('');
const isRefining = ref(false);

// Get the generated content
const generatedContent = computed(() => {
  return generationData.value.generated_content || 'Generating...';
});

const modelDisplayName = computed(() => {
  return generationData.value.model_used === 'gemini-pro' ? 'Gemini Pro (Premium)' : 'Gemini Flash (Free)';
});

const processingTimeFormatted = computed(() => {
  const ms = generationData.value.processing_time;
  if (ms < 1000) return `${ms}ms`;
  return `${(ms / 1000).toFixed(2)}s`;
});

const progressPercentage = computed(() => {
  if (!generationData.value.total_pages) return 0;
  return Math.round((generationData.value.current_page_index / generationData.value.total_pages) * 100);
});

const pagesList = computed(() => {
  return Object.entries(generationData.value.progress_data || {});
});

const isCompleted = computed(() => {
  return generationData.value.status === 'completed';
});

const isFailed = computed(() => {
  return generationData.value.status === 'failed';
});

// Start progressive generation
async function startProgressiveGeneration() {
  if (isGenerating.value || isCompleted.value) return;
  
  isGenerating.value = true;
  
  try {
    // Call generateNext endpoint repeatedly using axios
    while (generationData.value.current_page_index < generationData.value.total_pages) {
      const response = await axios.post(`/generation/${generationData.value.id}/next`);

      const result = response.data;

      if (!result.success) {
        console.error('Generation failed:', result.error);
        break;
      }

      // Refresh data
      await fetchProgress();

      if (result.completed) {
        break;
      }

      // Small delay between pages
      await new Promise(resolve => setTimeout(resolve, 500));
    }
  } catch (error) {
    console.error('Error during generation:', error);
  } finally {
    isGenerating.value = false;
    await fetchProgress(); // Final refresh
  }
}

// Fetch progress
async function fetchProgress() {
  try {
    const response = await axios.get(`/generation/${generationData.value.id}/progress`);
    const data = response.data;
    
    // Update generation data
    generationData.value = {
      ...generationData.value,
      status: data.status,
      current_status: data.current_status,
      current_page_index: data.current_page_index,
      total_pages: data.total_pages,
      progress_data: data.progress_data,
      completed_at: data.completed_at,
    };

    // Reload full data when completed to get generated_content
    if (data.status === 'completed' && !generationData.value.generated_content) {
      router.reload({ only: ['generation'] });
    }
  } catch (error) {
    console.error('Error fetching progress:', error);
  }
}

// Refine generation
async function refineGeneration() {
  if (!refinementPrompt.value.trim() || isRefining.value) return;
  
  isRefining.value = true;
  
  try {
    const response = await axios.post(`/generation/${generationData.value.id}/refine`, {
      prompt: refinementPrompt.value,
    });

    const result = response.data;

    if (result.success) {
      generationData.value.generated_content = result.content;
      refinementPrompt.value = '';
    } else {
      alert(result.error || 'Refinement failed');
    }
  } catch (error) {
    console.error('Error refining:', error);
    alert('An error occurred during refinement');
  } finally {
    isRefining.value = false;
  }
}

function copyToClipboard(text: string) {
  navigator.clipboard.writeText(text);
}

function downloadCode() {
  const blob = new Blob([generatedContent.value], { type: 'text/plain' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `${generationData.value.project.name}.txt`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

onMounted(() => {
  // If not completed, start progressive generation
  if (!isCompleted.value && !isFailed.value) {
    startProgressiveGeneration();
    
    // Poll progress every 2 seconds
    progressInterval.value = window.setInterval(fetchProgress, 2000);
  }
});

onUnmounted(() => {
  if (progressInterval.value) {
    clearInterval(progressInterval.value);
  }
});
</script>

<template>
  <AppLayout>
    <Head title="Generation Result" />

    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">
              {{ generationData.project.name }}
            </h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">
              Generated with {{ modelDisplayName }}
            </p>
          </div>
          <div class="flex items-center gap-3">
            <button
              v-if="isCompleted"
              @click="copyToClipboard(generatedContent)"
              class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              Copy
            </button>
            <button
              v-if="isCompleted"
              @click="downloadCode"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Download
            </button>
          </div>
        </div>

        <!-- Progress Bar -->
        <div v-if="!isCompleted && !isFailed" class="mt-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-600 dark:text-slate-400">
              {{ generationData.current_status }}
            </span>
            <span class="text-sm font-medium text-slate-900 dark:text-white">
              {{ progressPercentage }}%
            </span>
          </div>
          <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
            <div
              class="bg-blue-600 h-2 rounded-full transition-all duration-500"
              :style="{ width: `${progressPercentage}%` }"
            ></div>
          </div>
          <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
            Page {{ generationData.current_page_index }} of {{ generationData.total_pages }}
          </div>
        </div>

        <!-- Stats -->
        <div class="mt-4 flex items-center gap-6 text-sm">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-slate-600 dark:text-slate-400">{{ processingTimeFormatted }}</span>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-slate-600 dark:text-slate-400">{{ generationData.credits_used }} credit{{ generationData.credits_used !== 1 ? 's' : '' }} used</span>
          </div>
          <div class="flex items-center gap-2">
            <div :class="[
              'w-2 h-2 rounded-full',
              isCompleted ? 'bg-green-500' : isFailed ? 'bg-red-500' : 'bg-yellow-500 animate-pulse'
            ]"></div>
            <span class="text-slate-600 dark:text-slate-400 capitalize">{{ generationData.status }}</span>
          </div>
        </div>
      </div>

      <!-- Pages Progress List -->
      <div v-if="pagesList.length > 0" class="mb-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
        <h3 class="text-sm font-medium text-slate-900 dark:text-white mb-3">Pages</h3>
        <div class="space-y-2">
          <div
            v-for="[pageName, pageData] in pagesList"
            :key="pageName"
            class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
          >
            <div class="flex items-center gap-3">
              <div v-if="pageData.status === 'completed'" class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <div v-else-if="pageData.status === 'generating'" class="w-5 h-5 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></div>
              <div v-else-if="pageData.status === 'failed'" class="w-5 h-5 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <svg class="w-3 h-3 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
              <div v-else class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-600"></div>
              <span class="text-sm font-medium text-slate-900 dark:text-white capitalize">{{ pageName }}</span>
            </div>
            <span v-if="pageData.processing_time > 0" class="text-xs text-slate-500 dark:text-slate-400">
              {{ pageData.processing_time < 1000 ? `${pageData.processing_time}ms` : `${(pageData.processing_time / 1000).toFixed(2)}s` }}
            </span>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-slate-200 dark:border-slate-700 mb-6">
        <nav class="flex gap-6">
          <button
            @click="activeTab = 'code'"
            :class="[
              'pb-3 border-b-2 font-medium transition-colors',
              activeTab === 'code'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
            ]"
          >
            Generated Code
          </button>
          <button
            @click="activeTab = 'preview'"
            :class="[
              'pb-3 border-b-2 font-medium transition-colors',
              activeTab === 'preview'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
            ]"
          >
            Preview
          </button>
          <button
            @click="activeTab = 'prompt'"
            :class="[
              'pb-3 border-b-2 font-medium transition-colors',
              activeTab === 'prompt'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
            ]"
          >
            MCP Prompt
          </button>
          <button
            @click="activeTab = 'blueprint'"
            :class="[
              'pb-3 border-b-2 font-medium transition-colors',
              activeTab === 'blueprint'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
            ]"
          >
            Blueprint
          </button>
        </nav>
      </div>

      <!-- Content -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <!-- Code Tab -->
        <div v-if="activeTab === 'code'">
          <pre class="bg-slate-900 dark:bg-black rounded-lg p-6 overflow-auto max-h-[600px] text-sm"><code class="text-green-400 font-mono whitespace-pre-wrap">{{ generatedContent }}</code></pre>
          
          <!-- Refinement Section -->
          <div v-if="isCompleted" class="mt-6 border-t border-slate-200 dark:border-slate-700 pt-6">
            <h3 class="text-sm font-medium text-slate-900 dark:text-white mb-3">Refine Code</h3>
            <div class="flex gap-3">
              <input
                v-model="refinementPrompt"
                type="text"
                placeholder="Enter refinement instructions (e.g., 'Make it more responsive', 'Add dark mode support')"
                class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600"
                @keyup.enter="refineGeneration"
              />
              <button
                @click="refineGeneration"
                :disabled="!refinementPrompt.trim() || isRefining"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
              >
                <svg v-if="isRefining" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>{{ isRefining ? 'Refining...' : 'Refine' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Preview Tab -->
        <div v-if="activeTab === 'preview'">
          <div v-if="isCompleted" class="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden bg-white">
            <iframe
              :srcdoc="generatedContent"
              class="w-full h-[600px]"
              sandbox="allow-scripts"
            ></iframe>
          </div>
          <div v-else class="text-center text-slate-500 dark:text-slate-400 py-12">
            Preview will be available after generation completes
          </div>
        </div>

        <!-- Prompt Tab -->
        <div v-if="activeTab === 'prompt'">
          <pre class="bg-slate-900 dark:bg-black rounded-lg p-6 overflow-auto max-h-[600px] text-sm"><code class="text-yellow-400 font-mono whitespace-pre-wrap">{{ generationData.mcp_prompt }}</code></pre>
        </div>

        <!-- Blueprint Tab -->
        <div v-if="activeTab === 'blueprint'">
          <pre class="bg-slate-900 dark:bg-black rounded-lg p-6 overflow-auto max-h-[600px] text-sm"><code class="text-blue-400 font-mono">{{ JSON.stringify(generationData.project.blueprint, null, 2) }}</code></pre>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
