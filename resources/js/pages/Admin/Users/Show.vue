<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Generation {
  id: number;
  model_used: string;
  framework: string;
  category: string;
  status: string;
  credits_used: number;
  created_at: string;
}

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
  generations: Generation[];
}

interface Statistics {
  total_generations: number;
  completed_generations: number;
  failed_generations: number;
  total_credits_used: number;
}

interface Props {
  user: User;
  statistics: Statistics;
}

defineProps<Props>();

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
  <Head :title="`User: ${user.name}`" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">{{ user.name }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
              {{ user.email }}
            </p>
          </div>
          <div class="flex gap-3">
            <Link
              href="/admin/users"
              class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
            >
              ← Kembali
            </Link>
            <Link
              :href="`/admin/users/${user.id}/edit`"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors"
            >
              Edit User
            </Link>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Credits</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
              {{ formatNumber(user.credits) }}
            </div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Generations</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
              {{ formatNumber(statistics.total_generations) }}
            </div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Completed</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
              {{ formatNumber(statistics.completed_generations) }}
            </div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-sm font-medium text-slate-600 dark:text-slate-400">Credits Used</div>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
              {{ formatNumber(statistics.total_credits_used) }}
            </div>
          </div>
        </div>

        <!-- User Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">User Information</h2>
            <div class="space-y-4">
              <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Name</div>
                <div class="text-sm text-slate-900 dark:text-white mt-1">{{ user.name }}</div>
              </div>
              <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Email</div>
                <div class="text-sm text-slate-900 dark:text-white mt-1">{{ user.email }}</div>
              </div>
              <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Member Since</div>
                <div class="text-sm text-slate-900 dark:text-white mt-1">{{ formatDate(user.created_at) }}</div>
              </div>
              <div v-if="user.suspended_at">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Suspended At</div>
                <div class="text-sm text-red-600 dark:text-red-400 mt-1">{{ formatDate(user.suspended_at) }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Status & Permissions</h2>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 dark:text-slate-400">Premium User</span>
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    user.is_premium
                      ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400'
                      : 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-300'
                  ]"
                >
                  {{ user.is_premium ? 'Yes' : 'No' }}
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 dark:text-slate-400">Admin Access</span>
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    user.is_admin
                      ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400'
                      : 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-300'
                  ]"
                >
                  {{ user.is_admin ? 'Yes' : 'No' }}
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 dark:text-slate-400">Account Status</span>
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    user.is_active
                      ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400'
                      : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400'
                  ]"
                >
                  {{ user.is_active ? 'Active' : 'Suspended' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Generations -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Recent Generations</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
              Last 10 template generations by this user
            </p>
          </div>
          
          <div v-if="user.generations.length === 0" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
            User belum pernah melakukan generation
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
              <thead class="bg-slate-50 dark:bg-slate-900">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    ID
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Model & Framework
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Category
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Credits
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Date
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                <tr v-for="generation in user.generations" :key="generation.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
                    #{{ generation.id }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-slate-900 dark:text-white">{{ generation.model_used }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ generation.framework }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                    {{ generation.category }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                    {{ formatNumber(generation.credits_used) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        getStatusColor(generation.status)
                      ]"
                    >
                      {{ generation.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                    {{ formatDate(generation.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Link
                      :href="`/admin/generations/${generation.id}`"
                      class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                    >
                      View →
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
