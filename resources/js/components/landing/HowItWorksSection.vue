<script setup lang="ts">
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();
</script>

<template>
  <!-- How It Works Section -->
  <section id="how-it-works" class="py-20 bg-white dark:bg-slate-800/50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4">
          {{ t.landing?.howItWorks?.title || 'How It Works' }}
        </h2>
        <p class="text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
          {{ t.landing?.howItWorks?.subtitle || 'Three simple steps to your perfect template' }}
        </p>
      </div>

      <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <!-- Step 1 -->
        <div class="relative group">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 dark:from-blue-500/5 dark:to-indigo-500/5 rounded-2xl blur-xl group-hover:blur-2xl transition-all"></div>
          <div class="relative bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white text-2xl font-bold mb-6">
              1
            </div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">
              {{ t.landing?.howItWorks?.step1?.title || 'Configure' }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
              {{ t.landing?.howItWorks?.step1?.desc || 'Choose your framework, category, and preferences through our 3-step wizard.' }}
            </p>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="relative group">
          <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-500/10 dark:from-purple-500/5 dark:to-pink-500/5 rounded-2xl blur-xl group-hover:blur-2xl transition-all"></div>
          <div class="relative bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center text-white text-2xl font-bold mb-6">
              2
            </div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">
              {{ t.landing?.howItWorks?.step2?.title || 'Generate' }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
              {{ t.landing?.howItWorks?.step2?.desc || 'System converts your choices into a valid blueprint and generates code.' }}
            </p>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="relative group">
          <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-teal-500/10 dark:from-green-500/5 dark:to-teal-500/5 rounded-2xl blur-xl group-hover:blur-2xl transition-all"></div>
          <div class="relative bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-teal-600 rounded-xl flex items-center justify-center text-white text-2xl font-bold mb-6">
              3
            </div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">
              {{ t.landing?.howItWorks?.step3?.title || 'Download' }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
              {{ t.landing?.howItWorks?.step3?.desc || 'Get ready-to-use template code with full structure and components.' }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
