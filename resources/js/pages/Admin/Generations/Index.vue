<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface User {
  id: number;
  name: string;
  email: string;
}

interface Generation {
  id: number;
  user: User;
  model_used: string;
  output_format: string;
  framework: string;
  category: string;
  credits_used: number;
  status: 'pending' | 'processing' | 'completed' | 'failed';
  total_pages: number;
  current_page_index: number;
  created_at: string;
  completed_at: string | null;
  processing_time: number | null;
}

interface PaginatedGenerations {
  data: Generation[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface Props {
  generations: PaginatedGenerations;
  filters: {
    search?: string;
    status?: string;
    model?: string;
    date_from?: string;
    date_to?: string;
    sort_by?: string;
    sort_order?: string;
  };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const modelFilter = ref(props.filters.model || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const applyFilters = () => {
  router.get('/admin/generations', {
    search: searchQuery.value || undefined,
    status: statusFilter.value || undefined,
    model: modelFilter.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  searchQuery.value = '';
  statusFilter.value = '';
  modelFilter.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  router.get('/admin/generations');
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
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

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Pending',
    processing: 'Processing',
    completed: 'Completed',
    failed: 'Failed',
  };
  return labels[status] || status;
};

const statistics = computed(() => ({
  total: props.generations.total,
  completed: props.generations.data.filter(g => g.status === 'completed').length,
  failed: props.generations.data.filter(g => g.status === 'failed').length,
  processing: props.generations.data.filter(g => g.status === 'processing').length,
}));
</script>

<template>
  <Head title="Riwayat Generation - Admin" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Riwayat Generation</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
              Monitor dan kelola semua template generation
            </p>
          </div>
          <Link
            href="/admin"
            class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
          >
            ← Kembali
          </Link>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Generation</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
              {{ formatNumber(statistics.total) }}
            </div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Completed</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
              {{ formatNumber(statistics.completed) }}
            </div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Processing</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
              {{ formatNumber(statistics.processing) }}
            </div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Failed</div>
            <div class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
              {{ formatNumber(statistics.failed) }}
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Cari User
              </label>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Nama atau email..."
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
                @keyup.enter="applyFilters"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Status
              </label>
              <select
                v-model="statusFilter"
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
              >
                <option value="">Semua</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Dari Tanggal
              </label>
              <input
                v-model="dateFrom"
                type="date"
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Sampai Tanggal
              </label>
              <input
                v-model="dateTo"
                type="date"
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
              />
            </div>
            
            <div class="flex items-end gap-2">
              <button
                @click="applyFilters"
                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-lg font-medium transition-colors text-sm"
              >
                Filter
              </button>
              <button
                @click="resetFilters"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-medium transition-colors text-sm"
              >
                Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Generations Table -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
              <thead class="bg-slate-50 dark:bg-slate-900">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    User & Template
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Model
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Progress
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Credits
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                <tr v-if="generations.data.length === 0">
                  <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                    Tidak ada generation yang ditemukan
                  </td>
                </tr>
                <tr v-for="generation in generations.data" :key="generation.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex flex-col">
                      <div class="text-sm font-medium text-slate-900 dark:text-white">
                        {{ generation.user.name }}
                      </div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">
                        {{ generation.user.email }}
                      </div>
                      <div class="text-xs text-slate-600 dark:text-slate-400 mt-1 font-mono">
                        {{ generation.framework }} • {{ generation.category }}
                      </div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">
                        {{ formatDate(generation.created_at) }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-slate-900 dark:text-white">{{ generation.model_used }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ generation.output_format }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-slate-900 dark:text-white">
                      {{ generation.current_page_index || 0 }}/{{ generation.total_pages || 0 }} pages
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                      {{ formatDuration(generation.processing_time) }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                      {{ formatNumber(generation.credits_used) }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        getStatusColor(generation.status)
                      ]"
                    >
                      {{ getStatusLabel(generation.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right text-sm font-medium">
                    <Link
                      :href="`/admin/generations/${generation.id}`"
                      class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                    >
                      Detail →
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="generations.last_page > 1" class="bg-slate-50 dark:bg-slate-900 px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div class="text-sm text-slate-700 dark:text-slate-300">
              Menampilkan {{ generations.from }} - {{ generations.to }} dari {{ formatNumber(generations.total) }}
            </div>
            <div class="flex gap-2">
              <Link
                v-if="generations.current_page > 1"
                :href="`/admin/generations?page=${generations.current_page - 1}`"
                class="px-3 py-1 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
              >
                ← Prev
              </Link>
              <Link
                v-if="generations.current_page < generations.last_page"
                :href="`/admin/generations?page=${generations.current_page + 1}`"
                class="px-3 py-1 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
              >
                Next →
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
