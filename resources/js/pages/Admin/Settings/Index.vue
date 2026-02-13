<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Setting {
  id: number;
  key: string;
  value: string;
  type: 'string' | 'integer' | 'float' | 'boolean' | 'json';
  description: string;
  group: string;
  is_public: boolean;
}

interface Props {
  settings: Record<string, Setting[]>;
}

const props = defineProps<Props>();

const activeTab = ref('billing');

const tabs = [
  { key: 'billing', label: 'Billing & Credits', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
  { key: 'generation', label: 'Generation', icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4' },
  { key: 'email', label: 'Email (SMTP)', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
  { key: 'notification', label: 'Notification', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
  { key: 'general', label: 'General', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z' },
];

const currentSettings = computed(() => props.settings[activeTab.value] || []);

const form = useForm({
  settings: [] as Array<{ key: string; value: any }>,
});

const editingSettings = ref<Record<string, any>>({});

// Initialize editing values
Object.keys(props.settings).forEach(group => {
  props.settings[group].forEach(setting => {
    editingSettings.value[setting.key] = parseValue(setting.value, setting.type);
  });
});

function parseValue(value: string, type: string) {
  switch (type) {
    case 'integer':
      return parseInt(value) || 0;
    case 'float':
      return parseFloat(value) || 0;
    case 'boolean':
      return value === 'true' || value === '1';
    case 'json':
      try {
        return JSON.parse(value);
      } catch {
        return value;
      }
    default:
      return value;
  }
}

function saveSetting(setting: Setting) {
  form.settings = [{
    key: setting.key,
    value: editingSettings.value[setting.key],
  }];

  form.post('/admin/settings', {
    preserveScroll: true,
    onSuccess: () => {
      // Success message handled by backend
    },
  });
}

function resetSetting(key: string) {
  if (confirm('Reset pengaturan ini ke nilai default?')) {
    form.post(`/admin/settings/${key}/reset`, {
      preserveScroll: true,
    });
  }
}

function formatKey(key: string) {
  // Extract the part after the last dot, or use the whole key if no dot
  const parts = key.split('.');
  const lastPart = parts.length > 1 ? parts[parts.length - 1] : key;
  
  return lastPart
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}

function getSettingIcon(type: string) {
  switch (type) {
    case 'boolean':
      return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
    case 'integer':
    case 'float':
      return 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14';
    default:
      return 'M4 6h16M4 12h16M4 18h16';
  }
}
</script>

<template>
  <Head title="Pengaturan - Admin" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Pengaturan Platform</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
              Konfigurasi billing, generation, dan pengaturan umum
            </p>
          </div>
          <Link
            href="/admin"
            class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
          >
            ← Kembali
          </Link>
        </div>

        <!-- Tabs -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
          <div class="border-b border-slate-200 dark:border-slate-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
              <button
                v-for="tab in tabs"
                :key="tab.key"
                @click="activeTab = tab.key"
                :class="[
                  activeTab === tab.key
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300 dark:hover:border-slate-600',
                  'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                ]"
              >
                <svg
                  :class="[
                    activeTab === tab.key ? 'text-blue-500 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-500 dark:group-hover:text-slate-400',
                    '-ml-0.5 mr-2 h-5 w-5'
                  ]"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                </svg>
                <span>{{ tab.label }}</span>
              </button>
            </nav>
          </div>

          <!-- Settings List -->
          <div class="divide-y divide-slate-200 dark:divide-slate-700">
            <div v-if="currentSettings.length === 0" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
              Tidak ada pengaturan dalam kategori ini
            </div>
            
            <div
              v-for="setting in currentSettings"
              :key="setting.key"
              class="px-6 py-6 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
            >
              <div class="flex items-start justify-between gap-6">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-3 mb-2">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getSettingIcon(setting.type)" />
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
                        {{ formatKey(setting.key) }}
                      </h3>
                      <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                        {{ setting.key }}
                      </p>
                    </div>
                    <div>
                      <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300"
                      >
                        {{ setting.type }}
                      </span>
                    </div>
                  </div>
                  
                  <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                    {{ setting.description }}
                  </p>

                  <!-- Input based on type -->
                  <div class="flex items-end gap-3">
                    <!-- Boolean -->
                    <div v-if="setting.type === 'boolean'" class="flex items-center">
                      <input
                        v-model="editingSettings[setting.key]"
                        type="checkbox"
                        :id="setting.key"
                        class="w-4 h-4 text-blue-600 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400"
                      />
                      <label :for="setting.key" class="ml-2 text-sm text-slate-700 dark:text-slate-300">
                        {{ editingSettings[setting.key] ? 'Aktif' : 'Nonaktif' }}
                      </label>
                    </div>

                    <!-- Integer / Float -->
                    <div v-else-if="setting.type === 'integer' || setting.type === 'float'" class="flex-1 max-w-xs">
                      <input
                        v-model.number="editingSettings[setting.key]"
                        :type="setting.type === 'integer' ? 'number' : 'text'"
                        :step="setting.type === 'float' ? '0.01' : '1'"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                      />
                    </div>

                    <!-- String -->
                    <div v-else class="flex-1 max-w-md">
                      <input
                        v-model="editingSettings[setting.key]"
                        type="text"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                      />
                    </div>

                    <button
                      @click="saveSetting(setting)"
                      :disabled="form.processing"
                      class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                    
                    <button
                      @click="resetSetting(setting.key)"
                      class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg transition-colors"
                    >
                      Reset
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Info Boxes -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-400">Billing Settings</h3>
                <p class="mt-1 text-xs text-yellow-700 dark:text-yellow-300">
                  Margin & multiplier mempengaruhi perhitungan kredit
                </p>
              </div>
            </div>
          </div>

          <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-purple-800 dark:text-purple-400">Generation Limits</h3>
                <p class="mt-1 text-xs text-purple-700 dark:text-purple-300">
                  Batas concurrent jobs & ukuran template
                </p>
              </div>
            </div>
          </div>

          <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" />
                  <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-400">Cache Notice</h3>
                <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">
                  Settings di-cache 1 jam untuk performa optimal
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
