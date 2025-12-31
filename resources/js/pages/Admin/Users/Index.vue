<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface User {
  id: number;
  name: string;
  email: string;
  credits: number;
  is_premium: boolean;
  is_active: boolean;
  is_admin: boolean;
  created_at: string;
  suspended_at: string | null;
  generations_count: number;
}

interface PaginatedUsers {
  data: User[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface Props {
  users: PaginatedUsers;
  filters: {
    search?: string;
    premium?: string;
    active?: string;
    sort_by?: string;
    sort_order?: string;
  };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const premiumFilter = ref(props.filters.premium || '');
const activeFilter = ref(props.filters.active || '');

const applyFilters = () => {
  router.get('/admin/users', {
    search: searchQuery.value || undefined,
    premium: premiumFilter.value || undefined,
    active: activeFilter.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  searchQuery.value = '';
  premiumFilter.value = '';
  activeFilter.value = '';
  router.get('/admin/users');
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('id-ID').format(num);
};
</script>

<template>
  <Head title="Kelola Pengguna - Admin" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Kelola Pengguna</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
              Total {{ formatNumber(users.total) }} pengguna terdaftar
            </p>
          </div>
          <Link
            href="/admin"
            class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
          >
            ← Kembali ke Dashboard
          </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Cari Pengguna
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
                Status Premium
              </label>
              <select
                v-model="premiumFilter"
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
              >
                <option value="">Semua</option>
                <option value="true">Premium</option>
                <option value="false">Free</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Status Aktif
              </label>
              <select
                v-model="activeFilter"
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
              >
                <option value="">Semua</option>
                <option value="true">Aktif</option>
                <option value="false">Ditangguhkan</option>
              </select>
            </div>
            
            <div class="flex items-end gap-2">
              <button
                @click="applyFilters"
                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-lg font-medium transition-colors"
              >
                Terapkan
              </button>
              <button
                @click="resetFilters"
                class="px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-medium transition-colors"
              >
                Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Pengguna
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Email
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Credits
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Generations
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Terdaftar
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                <tr
                  v-for="user in users.data"
                  :key="user.id"
                  class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-medium text-sm">
                          {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                          {{ user.name }}
                        </div>
                        <div v-if="user.is_admin" class="text-xs text-red-600 dark:text-red-400">
                          Admin
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                    {{ user.email }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-slate-900 dark:text-white">
                      {{ formatNumber(user.credits) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex flex-col gap-1">
                      <span
                        :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          user.is_premium
                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
                            : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300'
                        ]"
                      >
                        {{ user.is_premium ? 'Premium' : 'Free' }}
                      </span>
                      <span
                        :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          user.is_active
                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                            : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                        ]"
                      >
                        {{ user.is_active ? 'Aktif' : 'Ditangguhkan' }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                    {{ formatNumber(user.generations_count) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                    {{ formatDate(user.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="`/admin/users/${user.id}`"
                        class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                      >
                        Lihat
                      </Link>
                      <Link
                        :href="`/admin/users/${user.id}/edit`"
                        class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                      >
                        Edit
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="users.last_page > 1" class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 border-t border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
              <div class="text-sm text-slate-600 dark:text-slate-400">
                Menampilkan {{ formatNumber(users.from) }} - {{ formatNumber(users.to) }} dari {{ formatNumber(users.total) }} pengguna
              </div>
              <div class="flex items-center gap-2">
                <Link
                  v-if="users.current_page > 1"
                  :href="`/admin/users?page=${users.current_page - 1}`"
                  preserve-state
                  class="px-3 py-1 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded hover:bg-slate-50 dark:hover:bg-slate-700"
                >
                  Sebelumnya
                </Link>
                <span class="text-sm text-slate-600 dark:text-slate-400">
                  Halaman {{ users.current_page }} dari {{ users.last_page }}
                </span>
                <Link
                  v-if="users.current_page < users.last_page"
                  :href="`/admin/users?page=${users.current_page + 1}`"
                  preserve-state
                  class="px-3 py-1 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded hover:bg-slate-50 dark:hover:bg-slate-700"
                >
                  Selanjutnya
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
