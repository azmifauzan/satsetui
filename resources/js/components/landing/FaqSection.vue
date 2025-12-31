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
  <!-- FAQ Section -->
  <section id="faq" class="py-20 bg-white dark:bg-slate-800/50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4">
          {{ t.landing?.faq?.title || 'Frequently Asked Questions' }}
        </h2>
        <p class="text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
          {{ t.landing?.faq?.subtitle || 'Find answers to common questions about Template Generator' }}
        </p>
      </div>
      
      <div class="max-w-4xl mx-auto">
        <div class="space-y-4">
          <div
            v-for="(faq, index) in (t.faq || [])"
            :key="index"
            class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden transition-all duration-200 bg-white dark:bg-slate-800"
          >
            <button
              @click="toggleFaq(index)"
              class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
            >
              <span class="font-semibold text-slate-900 dark:text-white pr-8">
                {{ faq.question }}
              </span>
              <svg
                class="w-5 h-5 text-slate-500 dark:text-slate-400 flex-shrink-0 transition-transform duration-200"
                :class="openIndex === index ? 'rotate-180' : ''"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <div
              v-if="openIndex === index"
              class="px-6 pb-4 text-slate-600 dark:text-slate-300 animate-fadeIn"
            >
              {{ faq.answer }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}
</style>
