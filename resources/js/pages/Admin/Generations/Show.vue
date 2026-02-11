<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface User {
  id: number;
  name: string;
  email: string;
  is_premium: boolean;
}

interface PageGeneration {
  id: number;
  page_name: string;
  status: string;
  input_tokens: number;
  output_tokens: number;
  cost_usd: string;
  processing_time: number;
  error_message: string | null;
}

interface Generation {
  id: number;
  user: User;
  model_used: string;
  output_format: string;
  framework: string;
  category: string;
  credits_used: number;
  base_credits: number;
  extra_page_credits: number;
  extra_component_credits: number;
  subtotal_credits: number;
  error_margin: number;
  profit_margin: number;
  status: 'pending' | 'processing' | 'completed' | 'failed';
  total_pages: number;
  current_page_index: number;
  mcp_prompt: string | null;
  blueprint_json: any;
  generated_content: string | null;
  error_message: string | null;
  processing_time: number | null;
  created_at: string;
  completed_at: string | null;
  pageGenerations: PageGeneration[];
}

interface Props {
  generation: Generation;
}

const props = defineProps<Props>();

const refund = () => {
  if (confirm('Refund credits untuk generation ini?')) {
    router.post(`/admin/generations/${props.generation.id}/refund`, {}, {
      preserveScroll: true,
    });
  }
};

const retry = () => {
  if (confirm('Retry generation yang gagal ini?')) {
    router.post(`/admin/generations/${props.generation.id}/retry`, {}, {
      preserveScroll: true,
    });
  }
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('id-ID').format(num);
};

const formatDuration = (seconds: number | null) => {
  if (!seconds) return '-';
  const minutes = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${minutes}m ${secs}s`;
};

const getStatusColor = (status: string) => {
  switch (status) {
    case 'completed':
      return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400';
    case 'processing':
      return 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400';
    case 'failed':
      return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400';
    default:
      return 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-300';
  }
};
</script>

<template>
  <Head :title="`Generation #${generation.id}`" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Generation #{{ generation.id }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
              {{ generation.user.name }} • {{ formatDate(generation.created_at) }}
            </p>
          </div>
          <div class="flex gap-3">
            <Link
              href="/admin/generations"
              class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
            >
              ← Kembali
            </Link>
            <button
              v-if="generation.status === 'failed'"
              @click="retry"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors"
            >
              Retry
            </button>
            <button
              v-if="generation.status === 'failed' && generation.credits_used > 0"
              @click="refund"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 rounded-lg transition-colors"
            >
              Refund
            </button>
          </div>
        </div>

        <!-- Status & Progress -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Status</div>
            <span
              :class="[
                'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-2',
                getStatusColor(generation.status)
              ]"
            >
              {{ generation.status.toUpperCase() }}
            </span>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Progress</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
              {{ generation.current_page_index || 0 }}/{{ generation.total_pages || 0 }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400">pages completed</div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Credits Used</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
              {{ formatNumber(generation.credits_used) }}
            </div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Processing Time</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
              {{ formatDuration(generation.processing_time) }}
            </div>
          </div>
        </div>

        <!-- User & Configuration -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">User Information</h2>
            <div class="space-y-3">
              <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Name</div>
                <div class="text-sm text-slate-900 dark:text-white">{{ generation.user.name }}</div>
              </div>
              <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Email</div>
                <div class="text-sm text-slate-900 dark:text-white">{{ generation.user.email }}</div>
              </div>
              <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Tier</div>
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    generation.user.is_premium
                      ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400'
                      : 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-300'
                  ]"
                >
                  {{ generation.user.is_premium ? 'Premium' : 'Free' }}
                </span>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Configuration</h2>
            <div class="space-y-3">
              <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Model</div>
                <div class="text-sm text-slate-900 dark:text-white font-mono">{{ generation.model_used }}</div>
              </div>
              <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Framework</div>
                <div class="text-sm text-slate-900 dark:text-white">{{ generation.framework }}</div>
              </div>
              <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Category</div>
                <div class="text-sm text-slate-900 dark:text-white">{{ generation.category }}</div>
              </div>
              <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Output Format</div>
                <div class="text-sm text-slate-900 dark:text-white">{{ generation.output_format }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Credit Breakdown -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Credit Breakdown</h2>
          <div class="space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-slate-600 dark:text-slate-400">Base Credits</span>
              <span class="text-slate-900 dark:text-white font-medium">{{ generation.base_credits }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-600 dark:text-slate-400">Extra Pages</span>
              <span class="text-slate-900 dark:text-white font-medium">{{ generation.extra_page_credits }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-600 dark:text-slate-400">Extra Components</span>
              <span class="text-slate-900 dark:text-white font-medium">{{ generation.extra_component_credits }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-600 dark:text-slate-400">Subtotal</span>
              <span class="text-slate-900 dark:text-white font-medium">{{ generation.subtotal_credits }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-600 dark:text-slate-400">Error Margin ({{ generation.error_margin }}%)</span>
              <span class="text-slate-900 dark:text-white font-medium">+{{ (generation.error_margin / 100 * generation.subtotal_credits).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-600 dark:text-slate-400">Profit Margin ({{ generation.profit_margin }}%)</span>
              <span class="text-slate-900 dark:text-white font-medium">+{{ (generation.profit_margin / 100 * generation.subtotal_credits).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-sm pt-2 border-t border-slate-200 dark:border-slate-700">
              <span class="text-slate-900 dark:text-white font-semibold">Total Credits</span>
              <span class="text-slate-900 dark:text-white font-bold text-lg">{{ formatNumber(generation.credits_used) }}</span>
            </div>
          </div>
        </div>

        <!-- Per-Page Generations -->
        <div v-if="generation.pageGenerations && generation.pageGenerations.length > 0" class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Page Generations</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
              <thead>
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Page</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Status</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Tokens</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Cost</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Time</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                <tr v-for="page in generation.pageGenerations" :key="page.id">
                  <td class="px-4 py-2 text-sm text-slate-900 dark:text-white">{{ page.page_name }}</td>
                  <td class="px-4 py-2">
                    <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', getStatusColor(page.status)]">
                      {{ page.status }}
                    </span>
                  </td>
                  <td class="px-4 py-2 text-sm text-right text-slate-900 dark:text-white">
                    <div>In: {{ formatNumber(page.input_tokens) }}</div>
                    <div>Out: {{ formatNumber(page.output_tokens) }}</div>
                  </td>
                  <td class="px-4 py-2 text-sm text-right text-slate-900 dark:text-white">${{ page.cost_usd }}</td>
                  <td class="px-4 py-2 text-sm text-right text-slate-900 dark:text-white">{{ formatDuration(page.processing_time) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="generation.error_message" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800 dark:text-red-400">Error Message</h3>
              <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                <pre class="whitespace-pre-wrap font-mono text-xs">{{ generation.error_message }}</pre>
              </div>
            </div>
          </div>
        </div>

        <!-- Blueprint Preview -->
        <div v-if="generation.blueprint_json" class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Blueprint</h2>
          <pre class="bg-slate-900 dark:bg-slate-950 text-slate-100 p-4 rounded-lg overflow-x-auto text-xs">{{ JSON.stringify(generation.blueprint_json, null, 2) }}</pre>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
