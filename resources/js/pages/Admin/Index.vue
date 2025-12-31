<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import StatCard from '@/components/admin/StatCard.vue';

interface Statistics {
  users: {
    total: number;
    premium: number;
    free: number;
    active: number;
    new_30d: number;
    active_7d: number;
    premium_percentage: number;
  };
  generations: {
    total: number;
    completed: number;
    failed: number;
    in_progress: number;
    success_rate: number;
    per_category: Record<string, number>;
    avg_processing_time: number;
  };
  credits: {
    total_issued: number;
    total_used: number;
    total_remaining: number;
    avg_per_generation: number;
    recent_transactions_30d: number;
    estimated_revenue: number;
  };
  models: {
    total: number;
    active: number;
    most_used: string | null;
    most_used_count: number;
    usage_distribution: Record<string, number>;
  };
  system: {
    queue_pending: number;
    failed_jobs_24h: number;
    error_rate: number;
    generations_24h: number;
    failed_generations_24h: number;
  };
}

interface Props {
  statistics: Statistics;
  generationTrend: {
    labels: string[];
    total: number[];
    completed: number[];
    failed: number[];
  };
  creditUsageTrend: {
    labels: string[];
    credits: number[];
  };
}

const props = defineProps<Props>();

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('id-ID').format(num);
};

const formatCurrency = (num: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(num);
};
</script>

<template>
  <Head title="Dashboard Admin" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Dashboard Admin</h1>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
            Statistik sistem secara keseluruhan
          </p>
        </div>

        <!-- User Statistics -->
        <div class="mb-8">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Statistik Pengguna</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <StatCard
              title="Total Pengguna"
              :value="formatNumber(statistics.users.total)"
              :subtitle="`${statistics.users.premium_percentage}% Premium`"
              icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
              color="blue"
            />
            <StatCard
              title="Pengguna Premium"
              :value="formatNumber(statistics.users.premium)"
              :subtitle="`${formatNumber(statistics.users.free)} pengguna free`"
              icon="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
              color="yellow"
            />
            <StatCard
              title="Pengguna Aktif"
              :value="formatNumber(statistics.users.active)"
              :subtitle="`${formatNumber(statistics.users.active_7d)} aktif 7 hari terakhir`"
              icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              color="green"
            />
            <StatCard
              title="Pengguna Baru"
              :value="formatNumber(statistics.users.new_30d)"
              subtitle="30 hari terakhir"
              icon="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
              color="purple"
            />
          </div>
        </div>

        <!-- Generation Statistics -->
        <div class="mb-8">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Statistik Generation</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <StatCard
              title="Total Generation"
              :value="formatNumber(statistics.generations.total)"
              :subtitle="`Success rate: ${statistics.generations.success_rate}%`"
              icon="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"
              color="blue"
            />
            <StatCard
              title="Berhasil"
              :value="formatNumber(statistics.generations.completed)"
              :subtitle="`${statistics.generations.avg_processing_time}s rata-rata`"
              icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              color="green"
            />
            <StatCard
              title="Gagal"
              :value="formatNumber(statistics.generations.failed)"
              subtitle="Perlu investigasi"
              icon="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
              color="red"
            />
            <StatCard
              title="Sedang Proses"
              :value="formatNumber(statistics.generations.in_progress)"
              subtitle="Dalam antrian"
              icon="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              color="yellow"
            />
          </div>
        </div>

        <!-- Credit Statistics -->
        <div class="mb-8">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Statistik Credit</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <StatCard
              title="Total Credit Dikeluarkan"
              :value="formatNumber(statistics.credits.total_issued)"
              subtitle="Semua pengguna"
              icon="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
              color="blue"
            />
            <StatCard
              title="Credit Terpakai"
              :value="formatNumber(statistics.credits.total_used)"
              :subtitle="`${formatNumber(statistics.credits.total_remaining)} tersisa`"
              icon="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
              color="green"
            />
            <StatCard
              title="Estimasi Pendapatan"
              :value="formatCurrency(statistics.credits.estimated_revenue)"
              subtitle="Dari credit terpakai"
              icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              color="yellow"
            />
            <StatCard
              title="Rata-rata per Generation"
              :value="formatNumber(statistics.credits.avg_per_generation)"
              subtitle="credit"
              icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
              color="purple"
            />
          </div>
        </div>

        <!-- Model Statistics -->
        <div class="mb-8">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Statistik Model LLM</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <StatCard
              title="Total Model"
              :value="formatNumber(statistics.models.total)"
              :subtitle="`${formatNumber(statistics.models.active)} aktif`"
              icon="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"
              color="indigo"
            />
            <StatCard
              title="Model Paling Populer"
              :value="statistics.models.most_used || 'N/A'"
              :subtitle="`${formatNumber(statistics.models.most_used_count)} penggunaan`"
              icon="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
              color="yellow"
            />
            <StatCard
              title="Model Aktif"
              :value="formatNumber(statistics.models.active)"
              subtitle="Tersedia untuk pengguna"
              icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              color="green"
            />
            <StatCard
              title="Model Tidak Aktif"
              :value="formatNumber(statistics.models.total - statistics.models.active)"
              subtitle="Tidak tersedia"
              icon="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
              color="red"
            />
          </div>
        </div>

        <!-- System Health -->
        <div class="mb-8">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Kesehatan Sistem</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <StatCard
              title="Queue Pending"
              :value="formatNumber(statistics.system.queue_pending)"
              subtitle="Job dalam antrian"
              icon="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              color="blue"
            />
            <StatCard
              title="Failed Jobs"
              :value="formatNumber(statistics.system.failed_jobs_24h)"
              subtitle="24 jam terakhir"
              icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
              color="red"
            />
            <StatCard
              title="Error Rate"
              :value="`${statistics.system.error_rate}%`"
              subtitle="Generation gagal"
              icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
              :color="statistics.system.error_rate > 10 ? 'red' : statistics.system.error_rate > 5 ? 'yellow' : 'green'"
            />
            <StatCard
              title="Generation Hari Ini"
              :value="formatNumber(statistics.system.generations_24h)"
              :subtitle="`${formatNumber(statistics.system.failed_generations_24h)} gagal`"
              icon="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
              color="purple"
            />
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Aksi Cepat</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a
              href="/admin/users"
              class="flex items-center p-4 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-400 transition-colors"
            >
              <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-slate-900 dark:text-white">Kelola Pengguna</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Lihat & edit pengguna</p>
              </div>
            </a>

            <a
              href="/admin/models"
              class="flex items-center p-4 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 hover:border-green-500 dark:hover:border-green-400 transition-colors"
            >
              <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-slate-900 dark:text-white">Model LLM</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Konfigurasi model</p>
              </div>
            </a>

            <a
              href="/admin/settings"
              class="flex items-center p-4 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 hover:border-purple-500 dark:hover:border-purple-400 transition-colors"
            >
              <div class="flex-shrink-0 w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-slate-900 dark:text-white">Pengaturan</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Konfigurasi sistem</p>
              </div>
            </a>

            <a
              href="/admin/generations"
              class="flex items-center p-4 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 hover:border-yellow-500 dark:hover:border-yellow-400 transition-colors"
            >
              <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-slate-900 dark:text-white">Riwayat Generation</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Monitor generation</p>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
