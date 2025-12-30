<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useI18n } from '@/lib/i18n';

interface Template {
  id: number;
  name: string;
  status: string;
  current_status: string;
  total_pages: number;
  current_page_index: number;
  progress_percentage: number;
  model_used: string;
  created_at: string;
  completed_at: string | null;
  can_view: boolean;
}

interface Props {
  templates: {
    data: Template[];
  };
  pagination: {
    current_page: number;
    last_page: number;
    total: number;
  };
}

const props = defineProps<Props>();
const { t } = useI18n();

const getStatusColor = (status: string) => {
  switch (status) {
    case 'completed':
      return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
    case 'processing':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400';
    case 'failed':
      return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
    default:
      return 'bg-slate-100 text-slate-800 dark:bg-slate-900/20 dark:text-slate-400';
  }
};

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'completed':
      return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
    case 'processing':
      return 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15';
    case 'failed':
      return 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z';
    default:
      return 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z';
  }
};
</script>

<template>
  <AppLayout>
    <Head :title="t.templates?.title || 'My Templates'" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">
              {{ t.templates?.title || 'My Templates' }}
            </h1>
            <p class="mt-2 text-slate-600 dark:text-slate-400">
              {{ t.templates?.description || 'Manage your generated templates' }}
            </p>
          </div>
          <Link
            href="/wizard"
            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors inline-flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ t.templates?.createNew || 'Create New Template' }}
          </Link>
        </div>

        <!-- Templates Grid -->
        <div v-if="props.templates.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="template in props.templates.data"
            :key="template.id"
            class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 hover:shadow-lg transition-shadow"
          >
            <!-- Status Badge -->
            <div class="flex items-center justify-between mb-4">
              <span
                :class="['px-3 py-1 rounded-full text-xs font-medium inline-flex items-center gap-1.5', getStatusColor(template.status)]"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon(template.status)" />
                </svg>
                {{ template.status }}
              </span>
            </div>

            <!-- Template Name -->
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 truncate">
              {{ template.name }}
            </h3>

            <!-- Progress Bar (if processing) -->
            <div v-if="template.status === 'processing'" class="mb-4">
              <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400 mb-1">
                <span>{{ template.current_status }}</span>
                <span>{{ template.progress_percentage }}%</span>
              </div>
              <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                <div
                  class="bg-blue-600 h-1.5 rounded-full transition-all"
                  :style="{ width: `${template.progress_percentage}%` }"
                ></div>
              </div>
            </div>

            <!-- Meta Info -->
            <div class="space-y-2 text-sm text-slate-600 dark:text-slate-400 mb-4">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ template.current_page_index }} / {{ template.total_pages }} pages</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ template.created_at }}</span>
              </div>
              <div v-if="template.completed_at" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Completed {{ template.completed_at }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
              <Link
                v-if="template.status === 'completed'"
                :href="`/generation/${template.id}`"
                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium text-center transition-colors"
              >
                {{ t.templates?.view || 'View' }}
              </Link>
              <Link
                v-else-if="template.status === 'processing'"
                :href="`/generation/${template.id}`"
                class="flex-1 px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg text-sm font-medium text-center transition-colors"
              >
                {{ t.templates?.viewProgress || 'View Progress' }}
              </Link>
              <Link
                v-else
                :href="`/generation/${template.id}`"
                class="flex-1 px-4 py-2 bg-slate-300 dark:bg-slate-700 hover:bg-slate-400 dark:hover:bg-slate-600 text-slate-900 dark:text-white rounded-lg text-sm font-medium text-center transition-colors"
              >
                {{ t.templates?.viewDetails || 'View Details' }}
              </Link>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else
          class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-12 text-center"
        >
          <svg
            class="w-16 h-16 mx-auto text-slate-400 dark:text-slate-600 mb-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
            {{ t.templates?.noTemplates || 'No templates yet' }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 mb-6">
            {{ t.templates?.noTemplatesDesc || 'Start creating your first template using the wizard' }}
          </p>
          <Link
            href="/wizard"
            class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ t.templates?.createFirst || 'Create Your First Template' }}
          </Link>
        </div>

        <!-- Pagination -->
        <div
          v-if="props.pagination.last_page > 1"
          class="mt-8 flex items-center justify-center gap-2"
        >
          <Link
            v-for="page in props.pagination.last_page"
            :key="page"
            :href="`/templates?page=${page}`"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
              page === props.pagination.current_page
                ? 'bg-blue-600 text-white'
                : 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700'
            ]"
          >
            {{ page }}
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
