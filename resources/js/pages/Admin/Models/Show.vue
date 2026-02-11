<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface LlmModel {
  id: number;
  name: string;
  display_name: string;
  description: string | null;
  input_price_per_million: string;
  output_price_per_million: string;
  estimated_credits_per_generation: number;
  context_length: number;
  is_free: boolean;
  is_active: boolean;
  sort_order: number;
  created_at: string;
  updated_at: string;
}

interface Props {
  model: LlmModel;
}

const props = defineProps<Props>();

const deleteModel = () => {
  if (confirm(`Apakah Anda yakin ingin menghapus model "${props.model.display_name}"?`)) {
    router.delete(`/admin/models/${props.model.id}`, {
      onSuccess: () => {
        router.visit('/admin/models');
      },
    });
  }
};

const toggleActive = () => {
  router.patch(`/admin/models/${props.model.id}`, {
    is_active: !props.model.is_active,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const formatNumber = (num: number | string) => {
  return new Intl.NumberFormat('id-ID').format(Number(num));
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
</script>

<template>
  <Head :title="`Model: ${model.display_name}`" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">{{ model.display_name }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 font-mono">
              {{ model.name }}
            </p>
          </div>
          <div class="flex gap-3">
            <Link
              href="/admin/models"
              class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
            >
              ← Kembali
            </Link>
            <Link
              :href="`/admin/models/${model.id}/edit`"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors"
            >
              Edit Model
            </Link>
          </div>
        </div>

        <!-- Status Badges -->
        <div class="flex gap-2 mb-6">
          <span
            :class="[
              'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
              model.is_active
                ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400'
                : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400'
            ]"
          >
            {{ model.is_active ? 'Aktif' : 'Tidak Aktif' }}
          </span>
          <span
            :class="[
              'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
              model.is_free
                ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400'
                : 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400'
            ]"
          >
            {{ model.is_free ? 'Gratis' : 'Premium' }}
          </span>
        </div>

        <!-- Model Information -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Informasi Model</h2>
          <div class="space-y-4">
            <div>
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Nama Internal</div>
              <div class="text-sm text-slate-900 dark:text-white font-mono mt-1">{{ model.name }}</div>
            </div>
            
            <div>
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Nama Tampilan</div>
              <div class="text-sm text-slate-900 dark:text-white mt-1">{{ model.display_name }}</div>
            </div>

            <div v-if="model.description">
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Deskripsi</div>
              <div class="text-sm text-slate-900 dark:text-white mt-1">{{ model.description }}</div>
            </div>

            <div>
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Sort Order</div>
              <div class="text-sm text-slate-900 dark:text-white mt-1">{{ model.sort_order }}</div>
            </div>
          </div>
        </div>

        <!-- Pricing Configuration -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Konfigurasi Harga</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Harga Input (per 1M token)</div>
              <div class="text-2xl font-bold text-slate-900 dark:text-white mt-2">
                ${{ model.input_price_per_million }}
              </div>
              <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Biaya untuk 1 juta token input
              </div>
            </div>

            <div>
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Harga Output (per 1M token)</div>
              <div class="text-2xl font-bold text-slate-900 dark:text-white mt-2">
                ${{ model.output_price_per_million }}
              </div>
              <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Biaya untuk 1 juta token output
              </div>
            </div>
          </div>
        </div>

        <!-- Model Specifications -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Spesifikasi Model</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Estimasi Kredit per Generasi</div>
              <div class="text-2xl font-bold text-slate-900 dark:text-white mt-2">
                {{ formatNumber(model.estimated_credits_per_generation) }}
              </div>
              <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Kredit yang dicharge untuk generasi standar
              </div>
            </div>

            <div>
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Context Length</div>
              <div class="text-2xl font-bold text-slate-900 dark:text-white mt-2">
                {{ formatNumber(model.context_length) }}
              </div>
              <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Maximum tokens (input + output)
              </div>
            </div>
          </div>
        </div>

        <!-- Timestamps -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Timestamps</h2>
          <div class="space-y-3">
            <div>
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Created At</div>
              <div class="text-sm text-slate-900 dark:text-white mt-1">{{ formatDate(model.created_at) }}</div>
            </div>
            <div>
              <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Last Updated</div>
              <div class="text-sm text-slate-900 dark:text-white mt-1">{{ formatDate(model.updated_at) }}</div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Actions</h2>
          <div class="flex flex-wrap gap-3">
            <button
              @click="toggleActive"
              :class="[
                'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                model.is_active
                  ? 'text-yellow-700 dark:text-yellow-300 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-900/30 dark:hover:bg-yellow-900/50'
                  : 'text-green-700 dark:text-green-300 bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-900/50'
              ]"
            >
              {{ model.is_active ? 'Nonaktifkan Model' : 'Aktifkan Model' }}
            </button>
            
            <Link
              :href="`/admin/models/${model.id}/edit`"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors"
            >
              Edit Konfigurasi
            </Link>

            <button
              @click="deleteModel"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 rounded-lg transition-colors"
            >
              Hapus Model
            </button>
          </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800 dark:text-blue-400">
                Informasi Penggunaan
              </h3>
              <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                <p>Model ini {{ model.is_active ? 'saat ini tersedia' : 'tidak tersedia' }} untuk dipilih oleh user saat membuat template. {{ model.is_free ? 'Semua user (free & premium) dapat menggunakan model ini.' : 'Hanya premium users yang dapat menggunakan model ini.' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
