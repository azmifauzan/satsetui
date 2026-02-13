<script setup lang="ts">
import { useI18n } from '@/lib/i18n';

const { currentLang } = useI18n();

const features = [
  { icon: '🎛️', colorClass: 'from-blue-500 to-indigo-600', bgClass: 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' },
  { icon: '🔒', colorClass: 'from-purple-500 to-pink-600', bgClass: 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800' },
  { icon: '⚡', colorClass: 'from-amber-500 to-orange-600', bgClass: 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800' },
  { icon: '🧱', colorClass: 'from-green-500 to-teal-600', bgClass: 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' },
  { icon: '📱', colorClass: 'from-cyan-500 to-blue-600', bgClass: 'bg-cyan-50 dark:bg-cyan-900/20 border-cyan-200 dark:border-cyan-800' },
  { icon: '🎨', colorClass: 'from-rose-500 to-pink-600', bgClass: 'bg-rose-50 dark:bg-rose-900/20 border-rose-200 dark:border-rose-800' },
];

const featuresEn = [
  { title: 'Wizard-Driven', desc: 'No free-form prompts. Structured selections ensure consistent, predictable output every time.' },
  { title: 'Deterministic', desc: 'Same configuration always produces identical results. Repeatable and reliable generation.' },
  { title: 'Lightning Fast', desc: 'One-step wizard. Choose, configure, generate. Your project is ready in minutes.' },
  { title: 'Per-Page Generation', desc: 'Each page generated separately for better context, progress tracking, and error recovery.' },
  { title: 'Fully Responsive', desc: 'All generated UI is mobile-first and fully responsive across all device sizes.' },
  { title: 'Multiple Frameworks', desc: 'Support for Tailwind CSS, Bootstrap, and Pure CSS output for any project stack.' },
];

const featuresId = [
  { title: 'Berbasis Wizard', desc: 'Tanpa prompt bebas. Pilihan terstruktur memastikan output yang konsisten dan dapat diprediksi.' },
  { title: 'Deterministik', desc: 'Konfigurasi yang sama selalu menghasilkan hasil identik. Generasi yang dapat diulang dan andal.' },
  { title: 'Sangat Cepat', desc: 'Wizard satu langkah. Pilih, konfigurasi, generate. Proyek kamu siap dalam hitungan menit.' },
  { title: 'Generasi Per-Halaman', desc: 'Setiap halaman dihasilkan secara terpisah untuk konteks, pelacakan, dan pemulihan error lebih baik.' },
  { title: 'Fully Responsive', desc: 'Semua UI yang dihasilkan bersifat mobile-first dan sepenuhnya responsif di semua ukuran perangkat.' },
  { title: 'Multi Framework', desc: 'Dukungan output Tailwind CSS, Bootstrap, dan Pure CSS untuk berbagai stack proyek.' },
];
</script>

<template>
  <section id="features" class="py-24 bg-white dark:bg-slate-800/50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <div class="inline-flex items-center px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-xs font-medium mb-4">
          {{ currentLang === 'id' ? 'Kenapa SatsetUI?' : 'Why SatsetUI?' }}
        </div>
        <h2 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4">
          {{ currentLang === 'id' ? 'Bukan Sekedar AI Builder' : 'Not Just Another AI Builder' }}
        </h2>
        <p class="text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
          {{ currentLang === 'id' 
            ? 'SatsetUI menggunakan pendekatan wizard yang terstruktur, bukan prompt acak.'
            : 'SatsetUI uses a structured wizard approach, not random prompts.' }}
        </p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
        <div
          v-for="(feature, index) in features"
          :key="index"
          :class="[feature.bgClass, 'p-6 rounded-xl border transition-all hover:-translate-y-1 hover:shadow-lg']"
        >
          <div class="text-3xl mb-4">{{ feature.icon }}</div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">
            {{ currentLang === 'id' ? featuresId[index].title : featuresEn[index].title }}
          </h3>
          <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
            {{ currentLang === 'id' ? featuresId[index].desc : featuresEn[index].desc }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
