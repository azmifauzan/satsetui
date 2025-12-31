<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

const openIndex = ref<number | null>(null);

const toggleFaq = (index: number) => {
  openIndex.value = openIndex.value === index ? null : index;
};
</script>

<template>
  <div class="space-y-4">
    <div 
      v-for="(item, index) in t.faq" 
      :key="index"
      class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden"
    >
      <button
        @click="toggleFaq(index)"
        class="w-full px-6 py-4 text-left flex items-center justify-between bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
      >
        <span class="font-semibold text-slate-900 dark:text-white">
          {{ item.question }}
        </span>
        <svg
          class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform"
          :class="{ 'rotate-180': openIndex === index }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      <div
        v-show="openIndex === index"
        class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700"
      >
        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
          {{ item.answer }}
        </p>
      </div>
    </div>
  </div>
</template>
