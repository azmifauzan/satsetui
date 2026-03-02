<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

interface Transaction {
  id: number;
  package_name: string | null;
  credits_added: number;
  amount: number;
  formatted_amount: string;
  status: 'pending' | 'success' | 'failed' | 'expired';
  mayar_payment_link: string | null;
  paid_at: string | null;
  created_at: string;
}

interface PaginatedTransactions {
  data: Transaction[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  links: Array<{ url: string | null; label: string; active: boolean }>;
}

const props = defineProps<{
  transactions: PaginatedTransactions;
  userCredits: number;
}>();

const formatDate = (d: string) =>
  new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const statusClasses = (status: string) => ({
  success: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
  pending: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
  failed: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
  expired: 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400',
}[status] || 'bg-slate-100 text-slate-600');

const statusLabel = (status: string) => ({
  success: t.value.topup?.statusSuccess ?? 'Success',
  pending: t.value.topup?.statusPending ?? 'Pending',
  failed: t.value.topup?.statusFailed ?? 'Failed',
  expired: t.value.topup?.statusExpired ?? 'Expired',
}[status] || status);

const goToPage = (page: number) => {
  router.get('/credits/history', { page }, { preserveState: true, preserveScroll: true });
};
</script>

<template>
  <Head :title="t.topup?.history ?? 'Topup History'" />

  <AppLayout>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 py-10">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">{{ t.topup?.history ?? 'Topup History' }}</h1>
            <p class="mt-1 text-slate-600 dark:text-slate-400">{{ t.topup?.historySubtitle ?? 'All your credit topup transactions' }}</p>
          </div>
          <div class="mt-4 sm:mt-0 flex items-center gap-3">
            <div class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
              <span class="text-sm text-blue-600 dark:text-blue-400">{{ t.topup?.currentBalance ?? 'Balance' }}:</span>
              <span class="ml-1 font-bold text-blue-700 dark:text-blue-300">{{ new Intl.NumberFormat('id-ID').format(userCredits) }}</span>
              <span class="ml-1 text-sm text-blue-600 dark:text-blue-400">{{ t.topup?.credits ?? 'credits' }}</span>
            </div>
            <Link
              href="/credits/topup"
              class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors"
            >
              {{ t.topup?.topupAgain ?? 'Top-up' }}
            </Link>
          </div>
        </div>

        <!-- Transaction List -->
        <div v-if="transactions.data.length > 0" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
          <!-- Table header -->
          <div class="hidden sm:grid grid-cols-12 gap-4 px-6 py-3 bg-slate-50 dark:bg-slate-700/50 text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
            <div class="col-span-3">{{ t.topup?.package ?? 'Package' }}</div>
            <div class="col-span-2">{{ t.topup?.credits ?? 'Credits' }}</div>
            <div class="col-span-2">{{ t.topup?.amount ?? 'Amount' }}</div>
            <div class="col-span-2">{{ t.topup?.status ?? 'Status' }}</div>
            <div class="col-span-3">{{ t.topup?.date ?? 'Date' }}</div>
          </div>

          <!-- Rows -->
          <div class="divide-y divide-slate-100 dark:divide-slate-700">
            <div
              v-for="tx in transactions.data"
              :key="tx.id"
              class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors items-center"
            >
              <!-- Package -->
              <div class="sm:col-span-3">
                <span class="sm:hidden text-xs text-slate-500 dark:text-slate-400 mr-1">{{ t.topup?.package ?? 'Package' }}:</span>
                <span class="text-sm font-medium text-slate-900 dark:text-white">{{ tx.package_name ?? '—' }}</span>
              </div>

              <!-- Credits -->
              <div class="sm:col-span-2">
                <span class="sm:hidden text-xs text-slate-500 dark:text-slate-400 mr-1">{{ t.topup?.credits ?? 'Credits' }}:</span>
                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">+{{ new Intl.NumberFormat('id-ID').format(tx.credits_added) }}</span>
              </div>

              <!-- Amount -->
              <div class="sm:col-span-2">
                <span class="sm:hidden text-xs text-slate-500 dark:text-slate-400 mr-1">{{ t.topup?.amount ?? 'Amount' }}:</span>
                <span class="text-sm font-medium text-slate-900 dark:text-white">{{ tx.formatted_amount }}</span>
              </div>

              <!-- Status -->
              <div class="sm:col-span-2 flex items-center gap-2">
                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', statusClasses(tx.status)]">
                  {{ statusLabel(tx.status) }}
                </span>
                <a
                  v-if="tx.status === 'pending' && tx.mayar_payment_link"
                  :href="tx.mayar_payment_link"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium"
                >
                  {{ t.topup?.continuePay ?? 'Pay' }} →
                </a>
              </div>

              <!-- Date -->
              <div class="sm:col-span-3">
                <span class="sm:hidden text-xs text-slate-500 dark:text-slate-400 mr-1">{{ t.topup?.date ?? 'Date' }}:</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ formatDate(tx.paid_at ?? tx.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="transactions.last_page > 1" class="px-6 py-4 flex items-center justify-between border-t border-slate-200 dark:border-slate-700">
            <p class="text-sm text-slate-600 dark:text-slate-400">
              {{ t.topup?.showing ?? 'Showing' }} {{ transactions.from }}–{{ transactions.to }}
              {{ t.topup?.of ?? 'of' }} {{ transactions.total }} {{ t.topup?.transactions ?? 'transactions' }}
            </p>
            <div class="flex gap-1">
              <button
                v-for="page in transactions.last_page"
                :key="page"
                @click="goToPage(page)"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  page === transactions.current_page
                    ? 'bg-blue-600 text-white'
                    : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                ]"
              >{{ page }}</button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-12 text-center">
          <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">{{ t.topup?.noTransactions ?? 'No transactions yet' }}</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">{{ t.topup?.noTransactionsDesc ?? "You haven't made any credit top-ups yet." }}</p>
          <Link
            href="/credits/topup"
            class="inline-flex items-center px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors"
          >
            {{ t.topup?.topupAgain ?? 'Top-up Now' }} →
          </Link>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
