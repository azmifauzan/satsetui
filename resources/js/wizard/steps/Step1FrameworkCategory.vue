<script setup lang="ts">
import { wizardState, Framework, Category } from '../wizardState';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

const frameworks: { value: Framework; label: string; description: string }[] = [
  {
    value: 'tailwind',
    label: 'Tailwind CSS',
    description: 'Utility-first framework, highly customizable, modern approach',
  },
  {
    value: 'bootstrap',
    label: 'Bootstrap',
    description: 'Component-based, rapid prototyping, extensive ecosystem',
  },
];

const categories: { value: Category; label: string; description: string; icon: string }[] = [
  {
    value: 'admin-dashboard',
    label: 'Admin Dashboard',
    description: 'Internal tools, data management, CRUD operations',
    icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  },
  {
    value: 'company-profile',
    label: 'Company Profile',
    description: 'Public-facing, content showcase, about/services pages',
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
  },
  {
    value: 'landing-page',
    label: 'Landing Page',
    description: 'Marketing-focused, conversion-optimized, hero sections',
    icon: 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
  },
  {
    value: 'saas-application',
    label: 'SaaS Application',
    description: 'User accounts, feature sections, pricing pages',
    icon: 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
  },
  {
    value: 'blog-content-site',
    label: 'Blog / Content Site',
    description: 'Article listings, reading experience, categories',
    icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
  },
];

function selectFramework(framework: Framework) {
  wizardState.framework = framework;
}

function selectCategory(category: Category) {
  wizardState.category = category;
}
</script>

<template>
  <div class="space-y-8">
    <!-- Framework Selection -->
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        {{ t.wizard?.steps?.framework?.title || 'Pilih Framework CSS' }}
      </h2>
      <p class="text-slate-600 dark:text-slate-400 mb-6">
        {{ t.wizard?.steps?.framework?.description || 'Pilih fondasi framework CSS untuk template Anda. Keputusan ini memengaruhi struktur komponen dan pola styling.' }}
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <button
          v-for="fw in frameworks"
          :key="fw.value"
          @click="selectFramework(fw.value)"
          :class="[
            'p-6 rounded-xl border-2 text-left transition-all hover:shadow-lg',
            wizardState.framework === fw.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-start justify-between mb-3">
            <h3 class="text-xl font-semibold text-slate-900 dark:text-white">
              {{ fw.label }}
            </h3>
            <div
              :class="[
                'w-6 h-6 rounded-full border-2 flex items-center justify-center',
                wizardState.framework === fw.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.framework === fw.value"
                class="w-4 h-4 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </div>
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            {{ fw.description }}
          </p>
        </button>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-slate-200 dark:border-slate-700"></div>

    <!-- Category Selection -->
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        {{ t.wizard?.steps?.category?.title || 'Pilih Kategori Template' }}
      </h2>
      <p class="text-slate-600 dark:text-slate-400 mb-6">
        {{ t.wizard?.steps?.category?.description || 'Pilih use case utama untuk template Anda. Ini akan memengaruhi rekomendasi halaman dan pola layout.' }}
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <button
          v-for="cat in categories"
          :key="cat.value"
          @click="selectCategory(cat.value)"
          :class="[
            'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg',
            wizardState.category === cat.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="cat.icon" />
              </svg>
            </div>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                wizardState.category === cat.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.category === cat.value"
                class="w-3 h-3 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </div>
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
            {{ cat.label }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            {{ cat.description }}
          </p>
        </button>
      </div>
    </div>
  </div>
</template>




