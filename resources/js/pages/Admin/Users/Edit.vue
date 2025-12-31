<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
}

interface Props {
  user: User;
}

const props = defineProps<Props>();

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  credits: props.user.credits,
  is_premium: props.user.is_premium,
  is_active: props.user.is_active,
});

const submit = () => {
  form.put(`/admin/users/${props.user.id}`, {
    preserveScroll: true,
  });
};

const adjustCreditsForm = useForm({
  amount: 0,
  reason: '',
});

const showCreditModal = ref(false);

const adjustCredits = () => {
  adjustCreditsForm.post(`/admin/users/${props.user.id}/credits`, {
    preserveScroll: true,
    onSuccess: () => {
      showCreditModal.value = false;
      adjustCreditsForm.reset();
    },
  });
};

const togglePremium = () => {
  if (confirm(`Ubah status menjadi ${props.user.is_premium ? 'Free' : 'Premium'}?`)) {
    router.post(`/admin/users/${props.user.id}/toggle-premium`, {}, {
      preserveScroll: true,
    });
  }
};

const toggleStatus = () => {
  if (confirm(`${props.user.is_active ? 'Tangguhkan' : 'Aktifkan'} pengguna ini?`)) {
    router.post(`/admin/users/${props.user.id}/toggle-status`, {}, {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <Head :title="`Edit ${user.name} - Admin`" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Edit Pengguna</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
              Ubah informasi dan pengaturan pengguna
            </p>
          </div>
          <Link
            :href="`/admin/users/${user.id}`"
            class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
          >
            ← Kembali
          </Link>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <button
            @click="showCreditModal = true"
            class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors text-left"
          >
            <div class="flex items-center">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span class="text-sm font-medium text-blue-900 dark:text-blue-100">Sesuaikan Credits</span>
            </div>
          </button>

          <button
            @click="togglePremium"
            class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors text-left"
          >
            <div class="flex items-center">
              <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
              </svg>
              <span class="text-sm font-medium text-yellow-900 dark:text-yellow-100">
                {{ user.is_premium ? 'Ubah ke Free' : 'Ubah ke Premium' }}
              </span>
            </div>
          </button>

          <button
            @click="toggleStatus"
            :class="[
              'p-4 border rounded-lg transition-colors text-left',
              user.is_active
                ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/30'
                : 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 hover:bg-green-100 dark:hover:bg-green-900/30'
            ]"
          >
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-3" :class="user.is_active ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="user.is_active ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'" />
              </svg>
              <span class="text-sm font-medium" :class="user.is_active ? 'text-red-900 dark:text-red-100' : 'text-green-900 dark:text-green-100'">
                {{ user.is_active ? 'Tangguhkan User' : 'Aktifkan User' }}
              </span>
            </div>
          </button>
        </div>

        <!-- Edit Form -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Nama Lengkap
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.name }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Email
              </label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
              />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.email }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Credits
              </label>
              <input
                v-model.number="form.credits"
                type="number"
                min="0"
                required
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
              />
              <p v-if="form.errors.credits" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.credits }}
              </p>
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Gunakan tombol "Sesuaikan Credits" di atas untuk mencatat alasan perubahan
              </p>
            </div>

            <div class="flex items-center gap-6">
              <label class="flex items-center">
                <input
                  v-model="form.is_premium"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400"
                />
                <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                  Premium User
                </span>
              </label>

              <label class="flex items-center">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400"
                />
                <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                  Akun Aktif
                </span>
              </label>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
              <button
                type="submit"
                :disabled="form.processing"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
              <Link
                :href="`/admin/users/${user.id}`"
                class="px-6 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-medium transition-colors"
              >
                Batal
              </Link>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Credit Adjustment Modal -->
    <div
      v-if="showCreditModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      @click.self="showCreditModal = false"
    >
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        
        <div class="relative bg-white dark:bg-slate-800 rounded-lg shadow-xl max-w-md w-full p-6">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
            Sesuaikan Credits
          </h3>
          
          <form @submit.prevent="adjustCredits" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Jumlah Credits
              </label>
              <input
                v-model.number="adjustCreditsForm.amount"
                type="number"
                required
                placeholder="Gunakan angka negatif untuk mengurangi"
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
              />
              <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Credits saat ini: {{ user.credits }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Alasan
              </label>
              <textarea
                v-model="adjustCreditsForm.reason"
                required
                rows="3"
                placeholder="Jelaskan alasan penyesuaian credits..."
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent"
              ></textarea>
            </div>

            <div class="flex items-center gap-3 pt-4">
              <button
                type="submit"
                :disabled="adjustCreditsForm.processing"
                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ adjustCreditsForm.processing ? 'Memproses...' : 'Sesuaikan' }}
              </button>
              <button
                type="button"
                @click="showCreditModal = false"
                class="flex-1 px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-medium transition-colors"
              >
                Batal
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
