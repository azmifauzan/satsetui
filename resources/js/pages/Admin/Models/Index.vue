<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useI18n } from '@/lib/i18n';

interface LlmModel {
  id: number;
  model_type: 'fast' | 'professional' | 'expert';
  display_name: string;
  description: string;
  provider: 'gemini' | 'openai';
  model_name: string;
  base_credits: number;
  is_active: boolean;
}

interface Props {
  models: LlmModel[];
}

const props = defineProps<Props>();
const { t } = useI18n();

const getProviderBadge = (provider: string) => {
  return provider === 'gemini'
    ? { class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300', label: 'Gemini' }
    : { class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300', label: 'OpenAI' };
};

const getStatusBadge = (isActive: boolean) => {
  return isActive
    ? { class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300', label: 'Aktif' }
    : { class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300', label: 'Tidak Aktif' };
};

const toggleActive = (modelId: number) => {
  router.post(`/admin/models/${modelId}/toggle-active`, {}, {
    preserveState: true,
    preserveScroll: true,
  });
};

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('id-ID').format(num);
};
</script>

<template>
  <Head title="Model AI - Admin" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Model AI</h1>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
            Kelola konfigurasi 3 model AI (Cepat, Profesional, Expert) untuk generasi template
          </p>
        </div>

        <!-- Info Box -->
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-1">
                💡 Sistem 3 Model Tetap
              </h3>
              <p class="text-sm text-blue-700 dark:text-blue-300">
                SatsetUI menggunakan 3 tipe model tetap. Anda dapat mengkonfigurasi setiap model untuk menggunakan provider (Gemini/OpenAI) dan model yang berbeda, serta mengatur base credit untuk setiap generasi.
              </p>
            </div>
          </div>
        </div>

        <!-- Models Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="model in props.models"
            :key="model.id"
            class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 hover:shadow-lg transition-shadow"
          >
            <!-- Model Type Badge & Status -->
            <div class="flex items-center justify-between mb-4">
              <span 
                :class="[
                  'px-3 py-1 rounded-lg text-sm font-semibold',
                  model.model_type === 'fast' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' :
                  model.model_type === 'professional' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' :
                  'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'
                ]"
              >
                {{ model.display_name }}
              </span>
              <button
                @click="toggleActive(model.id)"
                :class="[
                  'px-3 py-1 rounded-lg text-xs font-medium transition-colors',
                  getStatusBadge(model.is_active).class
                ]"
              >
                {{ getStatusBadge(model.is_active).label }}
              </button>
            </div>

            <!-- Description -->
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
              {{ model.description }}
            </p>

            <!-- Model Details -->
            <div class="space-y-3 mb-4">
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500 dark:text-slate-500">Provider:</span>
                <span 
                  :class="['px-2 py-1 rounded text-xs font-medium', getProviderBadge(model.provider).class]"
                >
                  {{ getProviderBadge(model.provider).label }}
                </span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500 dark:text-slate-500">Model:</span>
                <span class="text-slate-900 dark:text-white font-mono text-xs">
                  {{ model.model_name }}
                </span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500 dark:text-slate-500">Base Credits:</span>
                <span class="text-slate-900 dark:text-white font-semibold">
                  {{ formatNumber(model.base_credits) }} kredit
                </span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
              <Link
                :href="`/admin/models/${model.id}/edit`"
                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white text-center rounded-lg text-sm font-medium transition-colors"
              >
                <span class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Konfigurasi
                </span>
              </Link>
            </div>
          </div>
        </div>

        <!-- No Models -->
        <div v-if="!props.models ||props.models.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-white">Tidak ada model</h3>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Jalankan seeder untuk membuat 3 model default.</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
