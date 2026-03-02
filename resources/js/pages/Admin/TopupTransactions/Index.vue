<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface User { id: number; name: string; email: string; }
interface CreditPackage { id: number; name: string; credits: number; }
interface TopupTransaction {
  id: number;
  user: User;
  credit_package: CreditPackage | null;
  amount: number;
  credits_added: number;
  mayar_transaction_id: string | null;
  status: 'pending' | 'success' | 'failed' | 'expired';
  paid_at: string | null;
  created_at: string;
}

interface PaginatedTransactions {
  data: TopupTransaction[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface Stats {
  total_revenue: number;
  total_credits_sold: number;
  total_transactions: number;
  pending_count: number;
}

const props = defineProps<{
  transactions: PaginatedTransactions;
  stats: Stats;
  filters: { search?: string; status?: string; date_from?: string; date_to?: string; };
}>();

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const applyFilters = () => {
  router.get('/admin/topup-transactions', {
    search: searchQuery.value || undefined,
    status: statusFilter.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
  }, { preserveState: true, preserveScroll: true });
};

const resetFilters = () => {
  searchQuery.value = ''; statusFilter.value = ''; dateFrom.value = ''; dateTo.value = '';
  router.get('/admin/topup-transactions');
};

const formatCurrency = (val: number) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

const formatDate = (d: string) =>
  new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

const statusClasses = (status: string) => ({
  success: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
  pending: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
  failed: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
  expired: 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400',
}[status] || 'bg-slate-100 text-slate-600');

const statusLabel = (status: string) => ({
  success: 'Berhasil', pending: 'Menunggu', failed: 'Gagal', expired: 'Kadaluarsa',
}[status] || status);
</script>

<template>
  <Head title="Riwayat Topup - Admin" />

  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Riwayat Topup</h1>
          <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Monitor semua transaksi topup kredit via Mayar</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pendapatan</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ formatCurrency(stats.total_revenue) }}</p>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kredit Terjual</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ new Intl.NumberFormat('id-ID').format(stats.total_credits_sold) }}</p>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Transaksi Sukses</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ stats.total_transactions }}</p>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Menunggu Bayar</p>
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ stats.pending_count }}</p>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari nama / email pengguna..."
                class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                @keyup.enter="applyFilters"
              />
            </div>
            <div>
              <select
                v-model="statusFilter"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Semua Status</option>
                <option value="success">Berhasil</option>
                <option value="pending">Menunggu</option>
                <option value="failed">Gagal</option>
                <option value="expired">Kadaluarsa</option>
              </select>
            </div>
            <div>
              <input v-model="dateFrom" type="date" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
            <div>
              <input v-model="dateTo" type="date" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
          </div>
          <div class="flex gap-2 mt-3">
            <button @click="applyFilters" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Filter</button>
            <button @click="resetFilters" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Reset</button>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
          <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pengguna</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Paket</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kredit</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Waktu</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                <td class="px-6 py-4">
                  <div class="font-medium text-slate-900 dark:text-white text-sm">{{ tx.user.name }}</div>
                  <div class="text-xs text-slate-500 dark:text-slate-400">{{ tx.user.email }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                  {{ tx.credit_package?.name ?? '—' }}
                </td>
                <td class="px-6 py-4 text-sm font-semibold text-blue-600 dark:text-blue-400">
                  +{{ new Intl.NumberFormat('id-ID').format(tx.credits_added) }}
                </td>
                <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">
                  {{ formatCurrency(tx.amount) }}
                </td>
                <td class="px-6 py-4">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', statusClasses(tx.status)]">
                    {{ statusLabel(tx.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400">
                  {{ formatDate(tx.paid_at ?? tx.created_at) }}
                </td>
              </tr>
              <tr v-if="transactions.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                  Belum ada transaksi topup.
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="transactions.last_page > 1" class="px-6 py-4 flex items-center justify-between border-t border-slate-200 dark:border-slate-700">
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Menampilkan {{ transactions.from }}–{{ transactions.to }} dari {{ transactions.total }} transaksi
            </p>
            <div class="flex gap-2">
              <button
                v-for="page in transactions.last_page"
                :key="page"
                @click="router.get('/admin/topup-transactions', { ...filters, page }, { preserveState: true })"
                :class="[
                  'px-3 py-1 text-sm rounded-lg',
                  page === transactions.current_page
                    ? 'bg-blue-600 text-white'
                    : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                ]"
              >{{ page }}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
