<script setup lang="ts">
import { useI18n } from '@/lib/i18n';
import { Link } from '@inertiajs/vue3';

const { currentLang } = useI18n();
</script>

<template>
  <section class="py-24 bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 relative overflow-hidden">
    <!-- Background decoration -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute w-96 h-96 bg-white rounded-full -top-24 -left-24 blur-3xl"></div>
      <div class="absolute w-96 h-96 bg-white rounded-full -bottom-24 -right-24 blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
      <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
        {{ currentLang === 'id' ? 'Siap Bikin UI Sat-set?' : 'Ready to Build UI Sat-set?' }}
      </h2>
      <p class="text-xl text-blue-100 max-w-2xl mx-auto mb-10">
        {{ currentLang === 'id' 
          ? 'Mulai dari wizard, pilih, kustomisasi, generate. Semudah itu.' 
          : 'Start from the wizard, select, customize, generate. It\'s that simple.' }}
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <Link 
          href="/wizard"
          class="inline-flex items-center justify-center px-8 py-4 bg-white text-indigo-700 rounded-xl font-bold text-lg transition-all hover:bg-blue-50 shadow-xl hover:shadow-2xl hover:scale-105"
        >
          {{ currentLang === 'id' ? 'Buka Wizard' : 'Open Wizard' }}
          <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </Link>
        <a 
          href="#faq"
          class="inline-flex items-center justify-center px-8 py-4 border-2 border-white/30 text-white rounded-xl font-bold text-lg transition-all hover:bg-white/10 hover:border-white/50"
        >
          {{ currentLang === 'id' ? 'Pelajari Selengkapnya' : 'Learn More' }}
        </a>
      </div>

      <!-- Stats bar -->
      <div class="mt-16 grid grid-cols-3 gap-8 max-w-lg mx-auto">
        <div>
          <div class="text-3xl font-bold text-white">5+</div>
          <div class="text-sm text-blue-200">
            {{ currentLang === 'id' ? 'Kategori' : 'Categories' }}
          </div>
        </div>
        <div>
          <div class="text-3xl font-bold text-white">3</div>
          <div class="text-sm text-blue-200">
            {{ currentLang === 'id' ? 'Framework CSS' : 'CSS Frameworks' }}
          </div>
        </div>
        <div>
          <div class="text-3xl font-bold text-white">&lt;1m</div>
          <div class="text-sm text-blue-200">
            {{ currentLang === 'id' ? 'Generate' : 'To Generate' }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
