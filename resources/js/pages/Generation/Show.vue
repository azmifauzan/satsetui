<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useI18n } from '@/lib/i18n';

interface Generation {
  id: number;
  user_id: number;
  project_id: number;
  model_used: string;
  credits_used: number;
  status: string;
  mcp_prompt: string;
  generated_content: string | null;
  error_message: string | null;
  processing_time: number;
  started_at: string;
  completed_at: string;
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

const activeTab = ref<'preview' | 'code' | 'blueprint' | 'prompt'>('code');

// Get the generated content
const generatedContent = computed(() => {
  return props.generation.generated_content || 'No content generated';
});

const modelDisplayName = computed(() => {
  return props.generation.model_used === 'gemini-pro' ? 'Gemini Pro (Premium)' : 'Gemini Flash (Free)';
});

const processingTimeFormatted = computed(() => {
  const ms = props.generation.processing_time;
  if (ms < 1000) return `${ms}ms`;
  return `${(ms / 1000).toFixed(2)}s`;
});

function copyToClipboard(text: string) {
  navigator.clipboard.writeText(text);
  // You could add a toast notification here
}

function downloadCode() {
  const blob = new Blob([generatedContent.value], { type: 'text/plain' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `${props.generation.project.name}.txt`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}
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
              {{ generation.project.name }}
            </h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">
              Generated with {{ modelDisplayName }}
            </p>
          </div>
          <div class="flex items-center gap-3">
            <button
              @click="copyToClipboard(generatedContent)"
              class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              Copy
            </button>
            <button
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
            <span class="text-slate-600 dark:text-slate-400">{{ generation.credits_used }} credit{{ generation.credits_used !== 1 ? 's' : '' }} used</span>
          </div>
          <div class="flex items-center gap-2">
            <div :class="[
              'w-2 h-2 rounded-full',
              generation.status === 'completed' ? 'bg-green-500' : 'bg-yellow-500'
            ]"></div>
            <span class="text-slate-600 dark:text-slate-400 capitalize">{{ generation.status }}</span>
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
        </div>

        <!-- Prompt Tab -->
        <div v-if="activeTab === 'prompt'">
          <pre class="bg-slate-900 dark:bg-black rounded-lg p-6 overflow-auto max-h-[600px] text-sm"><code class="text-yellow-400 font-mono whitespace-pre-wrap">{{ generation.mcp_prompt }}</code></pre>
        </div>

        <!-- Blueprint Tab -->
        <div v-if="activeTab === 'blueprint'">
          <pre class="bg-slate-900 dark:bg-black rounded-lg p-6 overflow-auto max-h-[600px] text-sm"><code class="text-blue-400 font-mono">{{ JSON.stringify(generation.project.blueprint, null, 2) }}</code></pre>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
