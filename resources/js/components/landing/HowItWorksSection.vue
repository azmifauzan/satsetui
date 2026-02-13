<script setup lang="ts">
import { useI18n } from '@/lib/i18n';
import { Link } from '@inertiajs/vue3';

const { currentLang } = useI18n();
</script>

<template>
  <section id="how-it-works" class="py-24 bg-slate-50 dark:bg-slate-900">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4">
          {{ currentLang === 'id' ? 'Cara Kerja' : 'How It Works' }}
        </h2>
        <p class="text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
          {{ currentLang === 'id' ? 'Tiga bagian simpel, satu halaman wizard.' : 'Three simple sections, one wizard page.' }}
        </p>
      </div>

      <div class="max-w-4xl mx-auto">
        <!-- Step 1 -->
        <div class="flex flex-col md:flex-row items-start gap-6 mb-12">
          <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-blue-500/25">
            1
          </div>
          <div class="flex-1">
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
              {{ currentLang === 'id' ? 'Pilih Jenis Proyek' : 'Choose Project Type' }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
              {{ currentLang === 'id' 
                ? 'Landing Page, Company Profile, Mobile App, E-Commerce, atau Dashboard. Pilih satu yang sesuai kebutuhan kamu.' 
                : 'Landing Page, Company Profile, Mobile App, E-Commerce, or Dashboard. Pick the one that fits your needs.' }}
            </p>
            <div class="flex flex-wrap gap-2">
              <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-xs font-medium">Landing Page</span>
              <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full text-xs font-medium">Dashboard</span>
              <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full text-xs font-medium">E-Commerce</span>
              <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-full text-xs font-medium">Company Profile</span>
              <span class="px-3 py-1 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 rounded-full text-xs font-medium">Mobile App</span>
            </div>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="flex flex-col md:flex-row items-start gap-6 mb-12">
          <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-purple-500/25">
            2
          </div>
          <div class="flex-1">
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
              {{ currentLang === 'id' ? 'Kustomisasi Tampilan' : 'Customize Appearance' }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
              {{ currentLang === 'id' 
                ? 'Pilih skema warna, font, gaya desain, tipe navigasi, dan upload logo jika diperlukan. Semua melalui pilihan terstruktur — tanpa prompt.' 
                : 'Choose color scheme, fonts, design style, navigation type, and upload logo if needed. All through structured choices — no prompts.' }}
            </p>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="flex flex-col md:flex-row items-start gap-6 mb-12">
          <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-green-500 to-teal-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-green-500/25">
            3
          </div>
          <div class="flex-1">
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
              {{ currentLang === 'id' ? 'Generate & Unduh' : 'Generate & Download' }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
              {{ currentLang === 'id' 
                ? 'Klik Generate dan AI akan membuat kode siap pakai berdasarkan konfigurasi kamu. Hasilnya selalu konsisten dan deterministik!' 
                : 'Click Generate and AI builds production-ready code from your configuration. Results are always consistent and deterministic!' }}
            </p>
            <Link href="/wizard" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-semibold transition-all hover:from-blue-700 hover:to-indigo-700 shadow-lg shadow-blue-500/25 hover:shadow-xl hover:scale-105">
              {{ currentLang === 'id' ? 'Coba Sekarang' : 'Try It Now' }}
              <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
