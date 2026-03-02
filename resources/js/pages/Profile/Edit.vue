<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

const props = defineProps<{
  user: {
    name: string;
    email: string;
    phone: string | null;
  };
}>();

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone ?? '',
});

const submit = () => {
  form.put('/profile');
};

const showSuccess = computed(() => form.recentlySuccessful);
</script>

<template>
  <Head :title="t.profile?.title ?? 'Profile'" />

  <AppLayout>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 py-10">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-slate-900 dark:text-white">
            {{ t.profile?.title ?? 'Profile' }}
          </h1>
          <p class="mt-2 text-slate-600 dark:text-slate-400">
            {{ t.profile?.subtitle ?? 'Update your profile information' }}
          </p>
        </div>

        <!-- Success Message -->
        <div
          v-if="showSuccess"
          class="mb-6 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4 flex items-center gap-3"
        >
          <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <p class="text-sm text-green-700 dark:text-green-300">
            {{ t.profile?.saved ?? 'Profile updated successfully.' }}
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">

          <div class="p-6 space-y-6">

            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                {{ t.profile?.name ?? 'Name' }}
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors"
                :placeholder="t.profile?.namePlaceholder ?? 'Your full name'"
              />
              <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                {{ t.profile?.email ?? 'Email' }}
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors"
                :placeholder="t.profile?.emailPlaceholder ?? 'your@email.com'"
              />
              <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500">{{ form.errors.email }}</p>
            </div>

            <!-- Phone -->
            <div>
              <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                {{ t.profile?.phone ?? 'Phone Number' }}
              </label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors"
                placeholder="08xxxxxxxxxx"
              />
              <p v-if="form.errors.phone" class="mt-1.5 text-xs text-red-500">{{ form.errors.phone }}</p>
              <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">
                {{ t.profile?.phoneHint ?? 'Used for payment notifications via WhatsApp from Mayar.' }}
              </p>
            </div>

          </div>

          <!-- Footer -->
          <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 rounded-b-2xl flex items-center justify-end gap-3">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm"
            >
              <span v-if="form.processing">{{ t.common?.loading ?? 'Loading...' }}</span>
              <span v-else>{{ t.common?.save ?? 'Save' }}</span>
            </button>
          </div>
        </form>

        <!-- Phone warning for topup -->
        <div
          v-if="!user.phone"
          class="mt-6 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 flex items-start gap-3"
        >
          <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
              {{ t.profile?.phoneRequired ?? 'Phone number required for top-up' }}
            </p>
            <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
              {{ t.profile?.phoneRequiredDesc ?? 'Please add your phone number before using the top-up feature. Mayar uses it to send payment notifications via WhatsApp.' }}
            </p>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
