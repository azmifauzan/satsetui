<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

interface Transaction {
  id: number;
  status: 'pending' | 'success' | 'failed' | 'expired';
  credits_added: number;
  formatted_amount: string;
  paid_at: string | null;
}

const props = defineProps<{
  transaction: Transaction;
  userCredits: number;
}>();

const isSuccess = computed(() => props.transaction.status === 'success');
const isPending = computed(() => props.transaction.status === 'pending');
</script>

<template>
  <Head :title="t.topup?.success ?? 'Payment Status'" />

  <AppLayout>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 flex items-center justify-center py-12 px-4">
      <div class="max-w-md w-full text-center">

        <!-- Success -->
        <template v-if="isSuccess">
          <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ t.topup?.success ?? 'Payment Successful!' }}</h1>
          <p class="text-slate-600 dark:text-slate-400 mb-6">
            <span class="font-bold text-blue-600 dark:text-blue-400">
              {{ new Intl.NumberFormat('id-ID').format(transaction.credits_added) }} {{ t.topup?.credits ?? 'credits' }}
            </span>
            {{ t.topup?.successDesc ?? 'have been added to your account.' }}
          </p>
          <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 mb-6 text-left space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-slate-500 dark:text-slate-400">{{ t.topup?.creditsAdded ?? 'Credits added' }}</span>
              <span class="font-semibold text-slate-900 dark:text-white">+{{ new Intl.NumberFormat('id-ID').format(transaction.credits_added) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500 dark:text-slate-400">{{ t.topup?.amountPaid ?? 'Amount paid' }}</span>
              <span class="font-semibold text-slate-900 dark:text-white">{{ transaction.formatted_amount }}</span>
            </div>
            <div class="flex justify-between text-sm border-t border-slate-100 dark:border-slate-700 pt-3">
              <span class="text-slate-500 dark:text-slate-400">{{ t.topup?.yourBalance ?? 'Your credit balance' }}</span>
              <span class="font-bold text-blue-600 dark:text-blue-400">{{ new Intl.NumberFormat('id-ID').format(userCredits) }} {{ t.topup?.credits ?? 'credits' }}</span>
            </div>
          </div>
        </template>

        <!-- Pending -->
        <template v-else-if="isPending">
          <div class="w-20 h-20 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ t.topup?.pending ?? 'Payment Processing' }}</h1>
          <p class="text-slate-600 dark:text-slate-400 mb-6">
            {{ t.topup?.pendingDesc ?? 'Payment is still being verified. Credits will be added automatically after confirmation from Mayar.' }}
          </p>
        </template>

        <!-- Failed / Expired -->
        <template v-else>
          <div class="w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ t.topup?.failed ?? 'Payment Failed' }}</h1>
          <p class="text-slate-600 dark:text-slate-400 mb-6">
            {{ t.topup?.failedDesc ?? 'Payment was unsuccessful or expired. No credits were added.' }}
            {{ t.topup?.noCharges ?? 'No charges were applied.' }}
          </p>
        </template>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <Link
            href="/credits/topup"
            class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors"
          >
            {{ isSuccess ? (t.topup?.topupAgain ?? 'Top-up Again') : (t.topup?.tryAgain ?? 'Try Again') }}
          </Link>
          <Link
            href="/wizard"
            class="px-6 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
          >
            {{ t.topup?.toWizard ?? 'Go to Wizard →' }}
          </Link>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
