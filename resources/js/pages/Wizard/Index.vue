<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

interface Props {
  auth?: {
    user: {
      name: string;
      email: string;
    };
  };
}

const props = defineProps<Props>();

const logout = () => {
  router.post('/logout');
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900">
    <Head title="Wizard - Template Generator" />
    
    <!-- Header -->
    <header class="bg-white dark:bg-slate-800 shadow">
      <div class="container mx-auto px-4 py-4 flex items-center justify-between">
        <Link href="/" class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
          </div>
          <span class="text-xl font-bold text-slate-900 dark:text-white">Template<span class="text-blue-600">Gen</span></span>
        </Link>
        
        <div v-if="props.auth?.user" class="flex items-center space-x-4">
          <span class="text-slate-600 dark:text-slate-300">{{ props.auth.user.name }}</span>
          <button 
            @click="logout"
            class="px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
          >
            Logout
          </button>
        </div>
      </div>
    </header>
    
    <div class="container mx-auto px-4 py-20 text-center">
      <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-4">
        Template Wizard
      </h1>
      <p class="text-lg text-slate-600 dark:text-slate-300 mb-8">
        Wizard page coming soon...
      </p>
      <p v-if="props.auth?.user" class="text-slate-500 dark:text-slate-400 mb-8">
        Selamat datang, <strong>{{ props.auth.user.name }}</strong>!
      </p>
      <Link 
        href="/" 
        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Kembali ke Beranda
      </Link>
    </div>
  </div>
</template>
