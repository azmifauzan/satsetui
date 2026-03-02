<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

interface CreditPackage {
  id: number;
  name: string;
  description: string | null;
  credits: number;
  price: number;
  formatted_price: string;
}

interface RecentTopup {
  id: number;
  credits_added: number;
  amount: number;
  formatted_amount: string;
  paid_at: string | null;
}

const props = defineProps<{
  packages: CreditPackage[];
  userCredits: number;
  userPhone: string | null;
  completedTopups: RecentTopup[];
}>();

const selected = ref<number | null>(null);
const form = useForm({ credit_package_id: null as number | null });

const selectPackage = (pkg: CreditPackage) => {
  selected.value = pkg.id;
  form.credit_package_id = pkg.id;
};

const submit = () => {
  form.post('/credits/topup');
};

const formatDate = (d: string) =>
  new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
  <Head :title="t.topup?.title ?? 'Top-up Credits'" />

  <AppLayout>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 py-10">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center mb-10">
          <h1 class="text-3xl font-bold text-slate-900 dark:text-white">{{ t.topup?.title ?? 'Top-up Credits' }}</h1>
          <p class="mt-2 text-slate-600 dark:text-slate-400">
            {{ t.topup?.currentBalance ?? 'Your current credit balance' }}:
            <span class="font-bold text-blue-600 dark:text-blue-400">
              {{ new Intl.NumberFormat('id-ID').format(userCredits) }} {{ t.topup?.credits ?? 'credits' }}
            </span>
          </p>
        </div>

        <!-- Phone Required Warning -->
        <div
          v-if="!userPhone"
          class="mb-8 rounded-2xl border-2 border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-6 text-center"
        >
          <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-amber-800 dark:text-amber-300 mb-2">
            {{ t.topup?.phoneRequired ?? 'Phone Number Required' }}
          </h3>
          <p class="text-sm text-amber-600 dark:text-amber-400 mb-4">
            {{ t.topup?.phoneRequiredDesc ?? 'Please add your phone number in your profile first. Mayar uses it to send payment notifications via WhatsApp.' }}
          </p>
          <Link
            href="/profile"
            class="inline-flex items-center px-6 py-2.5 text-sm font-semibold text-white bg-amber-600 rounded-xl hover:bg-amber-700 transition-colors"
          >
            {{ t.topup?.goToProfile ?? 'Go to Profile' }} →
          </Link>
        </div>

        <!-- Package Cards -->
        <div :class="['grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8', !userPhone && 'opacity-50 pointer-events-none']">
          <button
            v-for="pkg in packages"
            :key="pkg.id"
            @click="selectPackage(pkg)"
            :class="[
              'relative text-left rounded-2xl border-2 p-6 transition-all duration-200 cursor-pointer',
              selected === pkg.id
                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-lg shadow-blue-500/10'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-300 dark:hover:border-blue-500/50 hover:shadow-md'
            ]"
          >
            <!-- Selected checkmark -->
            <div
              v-if="selected === pkg.id"
              class="absolute top-4 right-4 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center"
            >
              <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>

            <div class="mb-4">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="font-bold text-slate-900 dark:text-white text-lg">{{ pkg.name }}</h3>
              <p v-if="pkg.description" class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ pkg.description }}</p>
            </div>

            <div class="flex items-end justify-between mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
              <div>
                <span class="text-2xl font-extrabold text-slate-900 dark:text-white">
                  {{ new Intl.NumberFormat('id-ID').format(pkg.credits) }}
                </span>
                <span class="text-sm text-slate-500 dark:text-slate-400 ml-1">{{ t.topup?.credits ?? 'credits' }}</span>
              </div>
              <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ pkg.formatted_price }}</span>
            </div>
          </button>
        </div>

        <!-- Error -->
        <p v-if="form.errors.credit_package_id" class="text-center text-sm text-red-500 mb-4">
          {{ form.errors.credit_package_id }}
        </p>
        <p v-if="form.errors.payment" class="text-center text-sm text-red-500 mb-4">
          {{ form.errors.payment }}
        </p>

        <!-- CTA -->
        <div v-if="userPhone" class="flex justify-center">
          <button
            @click="submit"
            :disabled="!selected || form.processing"
            class="px-10 py-3.5 text-base font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-lg shadow-blue-600/20"
          >
            <span v-if="form.processing">{{ t.topup?.redirecting ?? 'Redirecting to Mayar...' }}</span>
            <span v-else-if="selected">{{ t.topup?.buyNow ?? 'Pay Now →' }}</span>
            <span v-else>{{ t.topup?.selectPackage ?? 'Select a Package First' }}</span>
          </button>
        </div>

        <!-- Payment info -->
        <p v-if="userPhone" class="text-center text-xs text-slate-400 dark:text-slate-500 mt-4">
          {{ t.topup?.paymentInfo ?? 'Secure payment via Mayar. Credits added automatically after successful payment.' }}
        </p>

        <!-- Recent topups -->
        <div v-if="completedTopups.length > 0" class="mt-12">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">{{ t.topup?.recentTopups ?? 'Recent Top-ups' }}</h2>
          <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
            <div v-for="topup in completedTopups" :key="topup.id" class="flex items-center justify-between px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div>
                  <span class="text-sm font-medium text-slate-900 dark:text-white">+{{ new Intl.NumberFormat('id-ID').format(topup.credits_added) }} {{ t.topup?.credits ?? 'credits' }}</span>
                  <span class="ml-2 text-xs text-slate-500 dark:text-slate-400">{{ topup.formatted_amount }}</span>
                </div>
              </div>
              <span class="text-xs text-slate-400 dark:text-slate-500">{{ formatDate(topup.paid_at ?? '') }}</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
