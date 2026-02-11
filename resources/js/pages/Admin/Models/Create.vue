<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const form = useForm({
  name: '',
  display_name: '',
  description: '',
  input_price_per_million: 0,
  output_price_per_million: 0,
  estimated_credits_per_generation: 0,
  context_length: 0,
  is_free: false,
  is_active: true,
  sort_order: null,
});

const submit = () => {
  form.post('/admin/models', {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Tambah Model LLM Baru" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Tambah Model LLM Baru</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
              Konfigurasi model AI baru untuk generasi template
            </p>
          </div>
          <Link
            href="/admin/models"
            class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
          >
            ← Kembali
          </Link>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Basic Information -->
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Informasi Dasar</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Nama Internal <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="gemini-2.5-flash"
                  class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.name }"
                  required
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.name }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Format: lowercase, dash-separated (contoh: gpt-4-turbo, claude-sonnet-4)
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Nama Tampilan <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.display_name"
                  type="text"
                  placeholder="Gemini 2.5 Flash"
                  class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.display_name }"
                  required
                />
                <p v-if="form.errors.display_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.display_name }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Nama yang user-friendly untuk ditampilkan di wizard
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Deskripsi
                </label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  placeholder="Model cepat dan efisien untuk penggunaan umum..."
                  class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent resize-none"
                  :class="{ 'border-red-500': form.errors.description }"
                ></textarea>
                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.description }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Penjelasan singkat tentang karakteristik dan kegunaan model
                </p>
              </div>
            </div>
          </div>

          <!-- Pricing Configuration -->
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Konfigurasi Harga</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Harga Input (per 1M token) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                  <input
                    v-model.number="form.input_price_per_million"
                    type="number"
                    step="0.0001"
                    min="0"
                    placeholder="0.075"
                    class="w-full pl-8 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                    :class="{ 'border-red-500': form.errors.input_price_per_million }"
                    required
                  />
                </div>
                <p v-if="form.errors.input_price_per_million" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.input_price_per_million }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Biaya per 1 juta token input dari API provider
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Harga Output (per 1M token) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">$</span>
                  <input
                    v-model.number="form.output_price_per_million"
                    type="number"
                    step="0.0001"
                    min="0"
                    placeholder="0.30"
                    class="w-full pl-8 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                    :class="{ 'border-red-500': form.errors.output_price_per_million }"
                    required
                  />
                </div>
                <p v-if="form.errors.output_price_per_million" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.output_price_per_million }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Biaya per 1 juta token output dari API provider
                </p>
              </div>
            </div>
          </div>

          <!-- Model Configuration -->
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Konfigurasi Model</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Estimasi Kredit per Generasi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.estimated_credits_per_generation"
                  type="number"
                  min="0"
                  placeholder="3"
                  class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.estimated_credits_per_generation }"
                  required
                />
                <p v-if="form.errors.estimated_credits_per_generation" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.estimated_credits_per_generation }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Kredit yang dicharge untuk generasi standar (5 pages, 6 components)
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Context Length <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.context_length"
                  type="number"
                  min="0"
                  placeholder="128000"
                  class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.context_length }"
                  required
                />
                <p v-if="form.errors.context_length" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.context_length }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Maximum tokens yang dapat diproses model (input + output)
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Sort Order
                </label>
                <input
                  v-model.number="form.sort_order"
                  type="number"
                  min="0"
                  placeholder="Otomatis"
                  class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.sort_order }"
                />
                <p v-if="form.errors.sort_order" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.sort_order }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Posisi urutan tampilan (kosongkan untuk otomatis)
                </p>
              </div>
            </div>
          </div>

          <!-- Status Settings -->
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Pengaturan Status</h2>
            
            <div class="space-y-4">
              <div class="flex items-start">
                <div class="flex items-center h-5">
                  <input
                    v-model="form.is_free"
                    type="checkbox"
                    id="is_free"
                    class="w-4 h-4 text-blue-600 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400"
                  />
                </div>
                <div class="ml-3">
                  <label for="is_free" class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    Model Gratis
                  </label>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Tersedia untuk semua user (free & premium)
                  </p>
                </div>
              </div>

              <div class="flex items-start">
                <div class="flex items-center h-5">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    id="is_active"
                    class="w-4 h-4 text-blue-600 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400"
                  />
                </div>
                <div class="ml-3">
                  <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    Model Aktif
                  </label>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Dapat dipilih saat generasi template di wizard
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Info Box -->
          <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-400">
                  Panduan Konfigurasi Model
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300 space-y-1">
                  <p>• Pastikan nama internal unik dan mengikuti format provider (contoh: gpt-4-turbo, claude-sonnet-4)</p>
                  <p>• Harga input/output harus sesuai dengan pricing resmi dari provider</p>
                  <p>• Estimasi kredit dihitung berdasarkan average token usage per generasi standar</p>
                  <p>• Model gratis tidak memerlukan user premium, model premium hanya untuk premium users</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-3">
            <Link
              href="/admin/models"
              class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ form.processing ? 'Menyimpan...' : 'Tambah Model' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
