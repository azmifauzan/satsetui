<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useI18n } from '@/lib/i18n';
import Swal from 'sweetalert2';

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

// --- Rename ---
const renameTarget = ref<Template | null>(null);
const renameForm = useForm({ name: '' });

function openRename(template: Template) {
  renameTarget.value = template;
  renameForm.name = template.name;
}

function closeRename() {
  renameTarget.value = null;
  renameForm.reset();
}

function submitRename() {
  if (!renameTarget.value) { return; }
  renameForm.put(`/templates/${renameTarget.value.id}/rename`, {
    preserveScroll: true,
    onSuccess: () => closeRename(),
  });
}

// --- Delete ---
const deleteTarget = ref<Template | null>(null);
const isDeleting = ref(false);

function openDelete(template: Template) {
  deleteTarget.value = template;
}

function closeDelete() {
  deleteTarget.value = null;
  isDeleting.value = false;
}

function submitDelete() {
  if (!deleteTarget.value) { return; }
  isDeleting.value = true;
  const name = deleteTarget.value.name;
  router.delete(`/templates/${deleteTarget.value.id}`, {
    onSuccess: () => {
      closeDelete();
      Swal.fire({
        icon: 'success',
        title: 'Template dihapus!',
        text: `"${name}" berhasil dihapus.`,
        confirmButtonColor: '#2563eb',
        timer: 3000,
        timerProgressBar: true,
      });
    },
    onError: () => {
      isDeleting.value = false;
    },
  });
}

// --- Helpers ---
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
            <!-- Status Badge + context menu -->
            <div class="flex items-center justify-between mb-4">
              <span
                :class="['px-3 py-1 rounded-full text-xs font-medium inline-flex items-center gap-1.5', getStatusColor(template.status)]"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon(template.status)" />
                </svg>
                {{ template.status }}
              </span>

              <!-- Edit / Delete icon buttons -->
              <div class="flex items-center gap-1">
                <button
                  type="button"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                  :title="t.templates?.rename || 'Rename'"
                  @click="openRename(template)"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button
                  type="button"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                  :title="t.templates?.delete || 'Delete'"
                  @click="openDelete(template)"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
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
              <button
                type="button"
                class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                :title="t.templates?.rename || 'Rename'"
                @click="openRename(template)"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button
                type="button"
                class="px-3 py-2 rounded-lg border border-red-200 dark:border-red-800 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                :title="t.templates?.delete || 'Delete'"
                @click="openDelete(template)"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
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

    <!-- ── Rename Modal ── -->
    <Teleport to="body">
      <div
        v-if="renameTarget"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="closeRename"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeRename" />

        <!-- Dialog -->
        <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6">
          <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">
            {{ t.templates?.renameTitle || 'Rename Template' }}
          </h2>

          <form @submit.prevent="submitRename">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              {{ t.templates?.nameLabel || 'Template Name' }}
            </label>
            <input
              v-model="renameForm.name"
              type="text"
              autofocus
              class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              :placeholder="t.templates?.namePlaceholder || 'Enter template name'"
            />
            <p v-if="renameForm.errors.name" class="mt-1.5 text-sm text-red-600 dark:text-red-400">
              {{ renameForm.errors.name }}
            </p>

            <div class="flex gap-3 mt-6">
              <button
                type="button"
                class="flex-1 px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors font-medium"
                @click="closeRename"
              >
                {{ t.common?.cancel || 'Cancel' }}
              </button>
              <button
                type="submit"
                :disabled="renameForm.processing || !renameForm.name.trim()"
                class="flex-1 px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-medium transition-colors"
              >
                {{ renameForm.processing ? (t.common?.saving || 'Saving…') : (t.common?.save || 'Save') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Delete Confirmation Modal ── -->
    <Teleport to="body">
      <div
        v-if="deleteTarget"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="closeDelete"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeDelete" />

        <!-- Dialog -->
        <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
              </svg>
            </div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
              {{ t.templates?.deleteTitle || 'Delete Template' }}
            </h2>
          </div>

          <p class="text-slate-600 dark:text-slate-400 mb-6">
            {{ t.templates?.deleteConfirm || 'Are you sure you want to delete' }}
            <strong class="text-slate-900 dark:text-white">{{ deleteTarget.name }}</strong>?
            {{ t.templates?.deleteWarning || 'This action cannot be undone.' }}
          </p>

          <div class="flex gap-3">
            <button
              type="button"
              :disabled="isDeleting"
              class="flex-1 px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
              @click="closeDelete"
            >
              {{ t.common?.cancel || 'Cancel' }}
            </button>
            <button
              type="button"
              :disabled="isDeleting"
              class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 disabled:opacity-70 disabled:cursor-not-allowed text-white font-medium transition-colors"
              @click="submitDelete"
            >
              <svg
                v-if="isDeleting"
                class="w-4 h-4 animate-spin"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              <svg
                v-else
                class="w-4 h-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              {{ isDeleting ? (t.common?.deleting || 'Menghapus…') : (t.templates?.deleteConfirmBtn || 'Delete') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>
