<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from '@/lib/i18n';
import { useTheme } from '@/lib/theme';

const { t, currentLang, setLang } = useI18n();
const { isDark, toggleTheme } = useTheme();

// Password visibility toggles
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Get redirect param from URL query string
const page = usePage();
const redirectTo = computed(() => {
  const params = new URLSearchParams(window.location.search);
  return params.get('redirect') || '';
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
  redirect: '',
});

const submit = () => {
  form.redirect = redirectTo.value;
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
    <Head :title="currentLang === 'en' ? 'Register' : 'Daftar'" />

    <div class="max-w-md w-full">
      <!-- Logo -->
      <div class="text-center mb-8">
        <Link href="/" class="inline-flex items-center space-x-2 mb-6">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
          </div>
          <span class="text-2xl font-bold text-slate-900 dark:text-white">Satset<span class="text-blue-600">UI</span></span>
        </Link>
        
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">
          {{ currentLang === 'en' ? 'Create New Account' : 'Buat Akun Baru' }}
        </h1>
        <p class="text-slate-600 dark:text-slate-400">
          {{ currentLang === 'en' ? 'Join and start building with SatsetUI' : 'Bergabung dan mulai membangun dengan SatsetUI' }}
        </p>
      </div>

      <!-- Register Form -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8">
        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t.auth.name }}
            </label>
            <input id="name" v-model="form.name" type="text" required autocomplete="name"
              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-white transition-colors"
              placeholder="John Doe" />
            <div v-if="form.errors.name" class="text-red-600 dark:text-red-400 text-sm mt-1">{{ form.errors.name }}</div>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">{{ t.auth.email }}</label>
            <input id="email" v-model="form.email" type="email" required autocomplete="email"
              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-white transition-colors"
              placeholder="name@email.com" />
            <div v-if="form.errors.email" class="text-red-600 dark:text-red-400 text-sm mt-1">{{ form.errors.email }}</div>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">{{ t.auth.password }}</label>
            <div class="relative">
              <input 
                id="password" 
                v-model="form.password" 
                :type="showPassword ? 'text' : 'password'" 
                required 
                autocomplete="new-password"
                class="w-full px-4 py-3 pr-12 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-white transition-colors"
                placeholder="••••••••" 
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
                :title="showPassword ? t.auth.hidePassword : t.auth.showPassword"
              >
                <!-- Eye icon (show) -->
                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <!-- Eye-off icon (hide) -->
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
            <div v-if="form.errors.password" class="text-red-600 dark:text-red-400 text-sm mt-1">{{ form.errors.password }}</div>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t.auth.confirmPassword }}
            </label>
            <div class="relative">
              <input 
                id="password_confirmation" 
                v-model="form.password_confirmation" 
                :type="showPasswordConfirmation ? 'text' : 'password'" 
                required 
                autocomplete="new-password"
                class="w-full px-4 py-3 pr-12 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-white transition-colors"
                placeholder="••••••••" 
              />
              <button
                type="button"
                @click="showPasswordConfirmation = !showPasswordConfirmation"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
                :title="showPasswordConfirmation ? t.auth.hidePassword : t.auth.showPassword"
              >
                <!-- Eye icon (show) -->
                <svg v-if="!showPasswordConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <!-- Eye-off icon (hide) -->
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
          </div>

          <div>
            <label class="flex items-start">
              <input v-model="form.terms" type="checkbox" required class="w-4 h-4 mt-1 text-blue-600 bg-slate-100 dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500" />
              <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">
                {{ t.auth.agreeToTerms }}
              </span>
            </label>
            <div v-if="form.errors.terms" class="text-red-600 dark:text-red-400 text-sm mt-1">{{ form.errors.terms }}</div>
          </div>

          <button type="submit" :disabled="form.processing"
            class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
            <span v-if="!form.processing">{{ currentLang === 'en' ? 'Register Now' : 'Daftar Sekarang' }}</span>
            <span v-else class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Processing...
            </span>
          </button>
        </form>

        <div class="mt-6 text-center">
          <span class="text-slate-600 dark:text-slate-400">{{ currentLang === 'en' ? 'Already have an account?' : 'Sudah punya akun?' }}</span>
          <Link :href="redirectTo ? `/login?redirect=${redirectTo}` : '/login'" class="ml-1 text-blue-600 dark:text-blue-400 hover:underline font-medium">
            {{ currentLang === 'en' ? 'Sign in' : 'Masuk' }}
          </Link>
        </div>
      </div>

      <div class="flex items-center justify-center gap-4 mt-6">
        <Link href="/" class="inline-flex items-center text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors text-sm">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          {{ currentLang === 'en' ? 'Home' : 'Beranda' }}
        </Link>
        <span class="text-slate-300 dark:text-slate-600">|</span>
        <button @click="setLang(currentLang === 'id' ? 'en' : 'id')" class="text-sm text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
          {{ currentLang === 'id' ? 'English' : 'Bahasa Indonesia' }}
        </button>
        <span class="text-slate-300 dark:text-slate-600">|</span>
        <button @click="toggleTheme" class="text-sm text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
          {{ isDark ? '☀️' : '🌙' }}
        </button>
      </div>
    </div>
  </div>
</template>
