<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();
</script>

<template>
  <!-- CTA Section -->
  <section class="py-20 bg-gradient-to-br from-blue-600 to-indigo-700 dark:from-blue-700 dark:to-indigo-900 relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
      <div class="absolute top-10 left-10 w-64 h-64 bg-white rounded-full blur-3xl"></div>
      <div class="absolute bottom-10 right-10 w-96 h-96 bg-white rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
          {{ t.landing?.cta?.title || 'Ready to Create Professional Templates?' }}
        </h2>
        <p class="text-xl text-blue-100 mb-10 max-w-2xl mx-auto">
          {{ t.landing?.cta?.subtitle || 'Join developers who have accelerated their workflow' }}
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <Link href="/register" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-white hover:bg-slate-50 text-blue-600 rounded-lg transition-all font-semibold text-lg shadow-lg hover:shadow-xl hover:scale-105">
            {{ t.landing?.cta?.button || 'Start Creating Templates' }}
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </Link>
        </div>
        <p class="text-blue-100 mt-6">{{ t.landing?.cta?.free || 'Free to start, get 25 initial credits' }}</p>
      </div>
    </div>
  </section>
</template>
