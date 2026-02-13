<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/lib/i18n';

const { currentLang } = useI18n();
</script>

<template>
  <section class="relative pt-32 pb-24 overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-20 right-10 w-96 h-96 bg-blue-500/10 dark:bg-blue-500/5 rounded-full blur-3xl"></div>
      <div class="absolute bottom-20 left-10 w-[500px] h-[500px] bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full blur-3xl"></div>
      <div class="absolute top-40 left-1/3 w-72 h-72 bg-purple-500/10 dark:bg-purple-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="max-w-5xl mx-auto text-center">
        <!-- Badge -->
        <div class="inline-flex items-center px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium mb-8">
          <span class="mr-2">⚡</span>
          {{ currentLang === 'id' ? 'Wizard-Driven • Deterministik • Tanpa Prompt' : 'Wizard-Driven • Deterministic • No Prompts' }}
        </div>

        <!-- Title -->
        <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-slate-900 dark:text-white mb-6 leading-tight">
          {{ currentLang === 'id' ? 'Buat UI Profesional' : 'Build Professional UI' }}
          <br />
          <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Sat-set!</span>
        </h1>

        <!-- Subtitle -->
        <p class="text-xl md:text-2xl text-slate-600 dark:text-slate-300 mb-10 max-w-3xl mx-auto leading-relaxed">
          {{ currentLang === 'id' 
            ? 'Pilih, konfigurasi, generate. Tidak perlu prompt — wizard kami yang mengurus sisanya. Cepat, konsisten, dan deterministik.' 
            : 'Choose, configure, generate. No prompts needed — our wizard handles the rest. Fast, consistent, and deterministic.' }}
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
          <Link href="/wizard" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl transition-all font-semibold text-lg shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/30 hover:scale-105">
            {{ currentLang === 'id' ? 'Mulai Membuat — Gratis' : 'Start Building — Free' }}
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </Link>
          <button @click="$emit('scrollToFeatures')" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 border-2 border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-600 text-slate-700 dark:text-slate-300 rounded-xl transition-all font-semibold text-lg">
            {{ currentLang === 'id' ? 'Lihat Fitur' : 'See Features' }}
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
        </div>

        <!-- Wizard Preview Card -->
        <div class="max-w-4xl mx-auto bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
          <div class="bg-slate-100 dark:bg-slate-700/50 px-6 py-3 flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-red-400"></div>
            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
            <div class="w-3 h-3 rounded-full bg-green-400"></div>
            <span class="ml-3 text-xs text-slate-500 dark:text-slate-400 font-mono">satsetui.com/wizard</span>
          </div>
          <div class="p-6 md:p-8">
            <!-- Mock wizard sections -->
            <div class="grid md:grid-cols-3 gap-4">
              <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                <div class="text-2xl mb-2">🎯</div>
                <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-1">
                  {{ currentLang === 'id' ? '1. Pilih Jenis' : '1. Choose Type' }}
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">Landing Page, Dashboard, E-Commerce...</p>
              </div>
              <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-4 border border-purple-200 dark:border-purple-800">
                <div class="text-2xl mb-2">🎨</div>
                <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-1">
                  {{ currentLang === 'id' ? '2. Kustomisasi' : '2. Customize' }}
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ currentLang === 'id' ? 'Warna, font, gaya, navigasi' : 'Colors, fonts, style, nav' }}</p>
              </div>
              <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 border border-green-200 dark:border-green-800">
                <div class="text-2xl mb-2">⚡</div>
                <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-1">
                  {{ currentLang === 'id' ? '3. Generate' : '3. Generate' }}
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ currentLang === 'id' ? 'Kode siap pakai, langsung!' : 'Production-ready code, instant!' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
