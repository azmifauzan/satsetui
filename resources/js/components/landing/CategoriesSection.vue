<script setup lang="ts">
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

const categories = [
  { 
    icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    key: 'admin',
    color: 'from-blue-500 to-cyan-500'
  },
  { 
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    key: 'company',
    color: 'from-purple-500 to-pink-500'
  },
  { 
    icon: 'M13 10V3L4 14h7v7l9-11h-7z',
    key: 'landing',
    color: 'from-orange-500 to-red-500'
  },
  { 
    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    key: 'saas',
    color: 'from-green-500 to-teal-500'
  },
  { 
    icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
    key: 'blog',
    color: 'from-indigo-500 to-blue-500'
  },
  { 
    icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
    key: 'portfolio',
    color: 'from-yellow-500 to-orange-500'
  }
];
</script>

<template>
  <!-- Categories Section -->
  <section id="categories" class="py-20 bg-slate-50 dark:bg-slate-900">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4">
          {{ t.landing?.categories?.title || 'Template Categories' }}
        </h2>
        <p class="text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
          {{ t.landing?.categories?.subtitle || 'Choose from various tailored template categories' }}
        </p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
        <div 
          v-for="category in categories" 
          :key="category.key"
          class="group relative bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md border border-slate-200 dark:border-slate-700 hover:shadow-xl hover:-translate-y-1 transition-all cursor-pointer"
        >
          <div :class="`w-12 h-12 bg-gradient-to-br ${category.color} rounded-lg flex items-center justify-center text-white mb-4 group-hover:scale-110 transition-transform`">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="category.icon" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
            {{ t.landing?.categories?.[category.key]?.title || category.key }}
          </h3>
          <p class="text-slate-600 dark:text-slate-300">
            {{ t.landing?.categories?.[category.key]?.desc || '' }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
