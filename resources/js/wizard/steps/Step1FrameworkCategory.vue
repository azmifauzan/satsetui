<script setup lang="ts">
import { wizardState, Framework, Category, PredefinedCategory } from '../wizardState';
import { useI18n } from '@/lib/i18n';
import { computed, ref, watch } from 'vue';

const { t } = useI18n();

// Custom category input refs
const showCustomCategoryInput = ref(false);

const frameworks = computed(() => [
  {
    value: 'tailwind' as Framework,
    label: 'Tailwind CSS',
    description: t.value.wizard?.steps?.framework?.tailwindDesc || 'Utility-first framework, highly customizable, modern approach',
  },
  {
    value: 'bootstrap' as Framework,
    label: 'Bootstrap',
    description: t.value.wizard?.steps?.framework?.bootstrapDesc || 'Component-based, rapid prototyping, extensive ecosystem',
  },
  {
    value: 'pure-css' as Framework,
    label: 'Pure CSS',
    description: t.value.wizard?.steps?.framework?.pureCssDesc || 'Vanilla CSS, no framework, full control, lightweight',
  },
]);

const predefinedCategories = computed(() => [
  {
    value: 'admin-dashboard' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.adminLabel || 'Admin Dashboard',
    description: t.value.wizard?.steps?.category?.adminDesc || 'Internal tools, data management, CRUD operations',
    icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  },
  {
    value: 'company-profile' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.companyLabel || 'Company Profile',
    description: t.value.wizard?.steps?.category?.companyDesc || 'Public-facing, content showcase, about/services pages',
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
  },
  {
    value: 'landing-page' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.landingLabel || 'Landing Page',
    description: t.value.wizard?.steps?.category?.landingDesc || 'Marketing-focused, conversion-optimized, hero sections',
    icon: 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
  },
  {
    value: 'saas-application' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.saasLabel || 'SaaS Application',
    description: t.value.wizard?.steps?.category?.saasDesc || 'User accounts, feature sections, pricing pages',
    icon: 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
  },
  {
    value: 'blog-content-site' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.blogLabel || 'Blog / Content Site',
    description: t.value.wizard?.steps?.category?.blogDesc || 'Article listings, reading experience, categories',
    icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
  },
  {
    value: 'e-commerce' as PredefinedCategory,
    label: t.value.wizard?.steps?.category?.ecommerceLabel || 'E-Commerce',
    description: t.value.wizard?.steps?.category?.ecommerceDesc || 'Product catalogs, shopping cart, checkout pages',
    icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
  },
]);

function selectFramework(framework: Framework) {
  wizardState.framework = framework;
}

function selectCategory(category: Category) {
  wizardState.category = category;
  if (category === 'custom') {
    showCustomCategoryInput.value = true;
  } else {
    showCustomCategoryInput.value = false;
    wizardState.customCategoryName = '';
    wizardState.customCategoryDescription = '';
  }
}

// Watch for custom category selection
watch(() => wizardState.category, (newVal) => {
  showCustomCategoryInput.value = newVal === 'custom';
});
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

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
        <!-- Predefined Categories -->
        <button
          v-for="cat in predefinedCategories"
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

        <!-- Custom Category Option -->
        <button
          @click="selectCategory('custom')"
          :class="[
            'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg border-dashed',
            wizardState.category === 'custom'
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                wizardState.category === 'custom'
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.category === 'custom'"
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
            {{ t.wizard?.steps?.category?.customLabel || 'Kategori Kustom' }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 text-sm">
            {{ t.wizard?.steps?.category?.customDesc || 'Buat kategori template sendiri sesuai kebutuhan spesifik Anda' }}
          </p>
        </button>
      </div>

      <!-- Custom Category Input -->
      <div v-if="wizardState.category === 'custom'" class="mt-6 p-6 bg-slate-50 dark:bg-slate-800/50 rounded-xl border-2 border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
          {{ t.wizard?.steps?.category?.customInputTitle || 'Detail Kategori Kustom' }}
        </h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t.wizard?.steps?.category?.customNameLabel || 'Nama Kategori' }} *
            </label>
            <input
              v-model="wizardState.customCategoryName"
              type="text"
              :placeholder="t.wizard?.steps?.category?.customNamePlaceholder || 'Contoh: Portal Berita, Aplikasi Sekolah, Sistem Inventory'"
              class="w-full px-4 py-3 border-2 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 transition-all"
            />
            <p v-if="wizardState.customCategoryName.length > 0 && wizardState.customCategoryName.length < 3" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ t.wizard?.steps?.category?.customNameMinLength || 'Minimal 3 karakter' }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              {{ t.wizard?.steps?.category?.customDescLabel || 'Deskripsi Kategori' }}
            </label>
            <textarea
              v-model="wizardState.customCategoryDescription"
              rows="3"
              :placeholder="t.wizard?.steps?.category?.customDescPlaceholder || 'Jelaskan tujuan dan karakteristik template yang ingin Anda buat. Misalnya: Template untuk portal berita dengan fitur artikel, kategori, dan pencarian.'"
              class="w-full px-4 py-3 border-2 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 transition-all resize-none"
            ></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>




