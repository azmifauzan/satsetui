<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface LlmModel {
  id: number;
  model_type: 'satset' | 'expert';
  display_name: string;
  description: string;
  provider: 'gemini' | 'openai';
  model_name: string;
  base_credits: number;
  is_active: boolean;
  has_api_key: boolean;
  has_base_url: boolean;
  base_url: string | null;
}

interface Props {
  model: LlmModel;
}

const props = defineProps<Props>();

const showApiKey = ref(false);
const showBaseUrl = ref(false);

const form = useForm({
  provider: props.model.provider,
  model_name: props.model.model_name,
  api_key: '',  // Empty by default, only update if filled
  base_url: props.model.base_url || '',  // Use existing base_url if available
  base_credits: props.model.base_credits,
  is_active: props.model.is_active,
});

const submit = () => {
  form.put(`/admin/models/${props.model.id}`, {
    preserveScroll: true,
  });
};

// Provider specific model suggestions
const modelSuggestions = {
  gemini: [
    'gemini-2.0-flash-exp',
    'gemini-2.5-pro-preview',
    'gemini-3-pro-preview',
  ],
  openai: [
    'gpt-4o',
    'gpt-4-turbo',
    'gpt-4',
    'gpt-3.5-turbo',
  ],
};

 const defaultBaseUrls = {
  gemini: 'https://generativelanguage.googleapis.com/v1beta',
  openai: 'https://api.openai.com/v1',
};
</script>

<template>
  <Head :title="`Konfigurasi Model: ${model.display_name}`" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Konfigurasi Model {{ model.display_name }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
              Atur provider, model, dan kredit untuk tipe model {{ model.model_type }}
            </p>
          </div>
          <Link
            href="/admin/models"
            class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
          >
            ← Kembali
          </Link>
        </div>

        <!-- Info Box -->
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-1">
                💡 Tipe Model: {{ model.display_name }} ({{ model.model_type }})
              </h3>
              <p class="text-sm text-blue-700 dark:text-blue-300">
                {{ model.description }}
              </p>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Provider Configuration -->
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Konfigurasi Provider</h2>
            
            <div class="space-y-4">
              <!-- Provider Selection -->
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Provider <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.provider"
                  class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.provider }"
                >
                  <option value="gemini">Gemini (Google)</option>
                  <option value="openai">OpenAI</option>
                </select>
                <p v-if="form.errors.provider" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.provider }}
                </p>
              </div>

              <!-- Model Name -->
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Nama Model <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.model_name"
                  type="text"
                  :placeholder="modelSuggestions[form.provider][0]"
                  class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent font-mono text-sm"
                  :class="{ 'border-red-500': form.errors.model_name }"
                />
                <p v-if="form.errors.model_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.model_name }}
                </p>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                  <strong>Contoh untuk {{ form.provider === 'gemini' ? 'Gemini' : 'OpenAI' }}:</strong>
                  <span v-for="(suggestion, index) in modelSuggestions[form.provider]" :key="suggestion" class="ml-1">
                    <button
                      type="button"
                      @click="form.model_name = suggestion"
                      class="text-blue-600 dark:text-blue-400 hover:underline"
                    >
                      {{ suggestion }}
                    </button>{{ index < modelSuggestions[form.provider].length - 1 ? ',' : '' }}
                  </span>
                </p>
              </div>

              <!-- API Key -->
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  API Key {{ model.has_api_key ? '' : '(Not configured)' }}
                  <span v-if="!model.has_api_key" class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input
                    v-model="form.api_key"
                    :type="showApiKey ? 'text' : 'password'"
                    :placeholder="model.has_api_key ? 'Leave empty to keep current key' : 'Enter API key'"
                    class="w-full px-4 py-2 pr-10 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent font-mono text-sm"
                    :class="{ 'border-red-500': form.errors.api_key }"
                  />
                  <button
                    type="button"
                    @click="showApiKey = !showApiKey"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                  >
                    <svg v-if="!showApiKey" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
                </div>
                <p v-if="form.errors.api_key" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.api_key }}
                </p>
                <p v-if="model.has_api_key" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  API key tersimpan dengan aman (encrypted). Kosongkan untuk tetap menggunakan key yang ada.
                </p>
              </div>

              <!-- Base URL (Optional) -->
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Base URL (Opsional)
                </label>
                <input
                  v-model="form.base_url"
                  type="url"
                  :placeholder="defaultBaseUrls[form.provider]"
                  class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent font-mono text-sm"
                  :class="{ 'border-red-500': form.errors.base_url }"
                />
                <p v-if="form.errors.base_url" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.base_url }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Default: <span class="font-mono">{{ defaultBaseUrls[form.provider] }}</span>. Kosongkan untuk menggunakan default.
                </p>
              </div>
            </div>
          </div>

          <!-- Billing Configuration -->
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Konfigurasi Kredit</h2>
            
            <div class="space-y-4">
              <!-- Base Credits -->
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  Base Credits per Generasi <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.base_credits"
                  type="number"
                  min="1"
                  step="1"
                  class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.base_credits }"
                />
                <p v-if="form.errors.base_credits" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.base_credits }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Jumlah kredit dasar yang dicharge untuk setiap generasi template. Total kredit akan dihitung dengan error margin dan profit margin di sistem.
                </p>
              </div>

              <!-- Active Status -->
              <div class="flex items-center gap-3 p-4 rounded-lg bg-slate-50 dark:bg-slate-900">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  id="is_active"
                  class="w-5 h-5 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-400"
                />
                <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                  Model Aktif (Tersedia untuk user)
                </label>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end gap-3">
            <Link
              href="/admin/models"
              class="px-6 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="form.processing">Menyimpan...</span>
              <span v-else>Simpan Perubahan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
