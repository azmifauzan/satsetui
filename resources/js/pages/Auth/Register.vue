<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const currentLang = ref<'id' | 'en'>('id'); // Default: Indonesian

// Initialize dark mode from localStorage
onMounted(() => {
  const savedTheme = localStorage.getItem('theme');
  const isDark = savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
  if (isDark) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
});

const translations = {
  id: {
    title: 'Buat Akun Baru',
    subtitle: 'Bergabunglah dengan ribuan developer yang telah mempercepat workflow mereka',
    name: 'Nama Lengkap',
    email: 'Email',
    password: 'Password',
    passwordConfirmation: 'Konfirmasi Password',
    terms: 'Saya setuju dengan',
    termsLink: 'Syarat & Ketentuan',
    and: 'dan',
    privacy: 'Kebijakan Privasi',
    submit: 'Daftar Sekarang',
    hasAccount: 'Sudah punya akun?',
    login: 'Masuk di sini',
    backHome: 'Kembali ke Beranda',
  },
  en: {
    title: 'Create New Account',
    subtitle: 'Join thousands of developers who have accelerated their workflow',
    name: 'Full Name',
    email: 'Email',
    password: 'Password',
    passwordConfirmation: 'Confirm Password',
    terms: 'I agree to the',
    termsLink: 'Terms & Conditions',
    and: 'and',
    privacy: 'Privacy Policy',
    submit: 'Register Now',
    hasAccount: 'Already have an account?',
    login: 'Sign in here',
    backHome: 'Back to Home',
  },
};

const t = () => translations[currentLang.value];

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
});

const submit = () => {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
    <Head :title="t().title" />

    <div class="max-w-md w-full">
      <!-- Logo & Back Button -->
      <div class="text-center mb-8">
        <Link href="/" class="inline-flex items-center space-x-2 mb-6">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
          </div>
          <span class="text-2xl font-bold text-slate-900 dark:text-white">Template<span class="text-blue-600">Gen</span></span>
        </Link>
        
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t().title }}
        </h1>
        <p class="text-slate-600 dark:text-slate-400">
          {{ t().subtitle }}
        </p>
      </div>

      <!-- Register Form -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8">
        <form @submit.prevent="submit" class="space-y-5">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t().name }}
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              autocomplete="name"
              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-white transition-colors"
              placeholder="John Doe"
            />
            <div v-if="form.errors.name" class="text-red-600 dark:text-red-400 text-sm mt-1">
              {{ form.errors.name }}
            </div>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t().email }}
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-white transition-colors"
              placeholder="nama@email.com"
            />
            <div v-if="form.errors.email" class="text-red-600 dark:text-red-400 text-sm mt-1">
              {{ form.errors.email }}
            </div>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t().password }}
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              autocomplete="new-password"
              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-white transition-colors"
              placeholder="••••••••"
            />
            <div v-if="form.errors.password" class="text-red-600 dark:text-red-400 text-sm mt-1">
              {{ form.errors.password }}
            </div>
          </div>

          <!-- Password Confirmation -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t().passwordConfirmation }}
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              autocomplete="new-password"
              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-white transition-colors"
              placeholder="••••••••"
            />
          </div>

          <!-- Terms Checkbox -->
          <div>
            <label class="flex items-start">
              <input
                v-model="form.terms"
                type="checkbox"
                required
                class="w-4 h-4 mt-1 text-blue-600 bg-slate-100 dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 dark:focus:ring-blue-600 focus:ring-2"
              />
              <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">
                {{ t().terms }}
                <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">{{ t().termsLink }}</a>
                {{ t().and }}
                <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">{{ t().privacy }}</a>
              </span>
            </label>
            <div v-if="form.errors.terms" class="text-red-600 dark:text-red-400 text-sm mt-1">
              {{ form.errors.terms }}
            </div>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!form.processing">{{ t().submit }}</span>
            <span v-else class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Processing...
            </span>
          </button>
        </form>

        <!-- Login Link -->
        <div class="mt-6 text-center">
          <span class="text-slate-600 dark:text-slate-400">{{ t().hasAccount }}</span>
          <Link href="/login" class="ml-1 text-blue-600 dark:text-blue-400 hover:underline font-medium">
            {{ t().login }}
          </Link>
        </div>
      </div>

      <!-- Back to Home -->
      <div class="text-center mt-6">
        <Link href="/" class="inline-flex items-center text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          {{ t().backHome }}
        </Link>
      </div>

      <!-- Language Toggle -->
      <div class="text-center mt-4">
        <button
          @click="currentLang = currentLang === 'id' ? 'en' : 'id'"
          class="text-sm text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
        >
          {{ currentLang === 'id' ? 'English' : 'Bahasa Indonesia' }}
        </button>
      </div>
    </div>
  </div>
</template>




