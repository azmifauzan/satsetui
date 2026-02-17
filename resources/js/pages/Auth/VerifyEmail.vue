<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

const form = useForm({});
const verificationLinkSent = ref(false);

function submit() {
  form.post('/email/verification-notification', {
    preserveScroll: true,
    onSuccess: () => {
      verificationLinkSent.value = true;
      setTimeout(() => {
        verificationLinkSent.value = false;
      }, 3000);
    },
  });
}
</script>

<template>
  <GuestLayout>
    <Head :title="t.auth.verifyEmail || 'Verify Email'" />

    <div class="mb-4 text-sm text-slate-600 dark:text-slate-400">
      {{ t.auth.verifyEmailMessage || 'Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengklik link yang baru saja kami kirimkan. Jika Anda tidak menerima email, kami akan dengan senang hati mengirimkan yang baru.' }}
    </div>

    <div v-if="verificationLinkSent" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
      {{ t.auth.verificationLinkSent || 'Link verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.' }}
    </div>

    <form @submit.prevent="submit">
      <div class="mt-4 flex items-center justify-between">
        <button
          type="submit"
          class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 dark:hover:bg-blue-400 focus:bg-blue-500 dark:focus:bg-blue-400 active:bg-blue-700 dark:active:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition ease-in-out duration-150"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          {{ t.auth.resendVerificationEmail || 'Kirim Ulang Email Verifikasi' }}
        </button>

        <Link
          href="/logout"
          method="post"
          as="button"
          class="underline text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-slate-800"
        >
          {{ t.auth.logout || 'Keluar' }}
        </Link>
      </div>
    </form>
  </GuestLayout>
</template>
