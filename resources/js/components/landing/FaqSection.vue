<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from '@/lib/i18n';

const { currentLang } = useI18n();

const faqs = ref([
  {
    question_en: 'Is SatsetUI a drag-and-drop builder?',
    answer_en: 'No. SatsetUI uses a structured wizard — you select options and AI generates production-ready code. No dragging, no prompts, just fast choices.',
    question_id: 'Apakah SatsetUI builder drag-and-drop?',
    answer_id: 'Tidak. SatsetUI menggunakan wizard terstruktur — kamu pilih opsi dan AI generate kode siap pakai. Tanpa drag, tanpa prompt, cuma pilihan cepat.',
  },
  {
    question_en: 'What output formats are supported?',
    answer_en: 'We support HTML+CSS with Tailwind CSS, Bootstrap, or Pure CSS. More output formats like React, Vue, and Angular components coming soon.',
    question_id: 'Format output apa yang didukung?',
    answer_id: 'Kami mendukung HTML+CSS dengan Tailwind CSS, Bootstrap, atau Pure CSS. Format lain seperti React, Vue, dan Angular segera hadir.',
  },
  {
    question_en: 'Do I need to know how to code?',
    answer_en: 'Not at all. The wizard guides you through every choice. The generated code is clean, documented, and ready to use in any project.',
    question_id: 'Apakah harus bisa coding?',
    answer_id: 'Tidak perlu. Wizard memandu setiap pilihan kamu. Kode yang dihasilkan bersih, terdokumentasi, dan siap pakai di proyek apa pun.',
  },
  {
    question_en: 'Is it free to use?',
    answer_en: 'Free accounts get access to Gemini 2.5 Flash for generation. Premium accounts can choose from multiple AI models and get priority generation.',
    question_id: 'Apakah gratis?',
    answer_id: 'Akun gratis bisa pakai Gemini 2.5 Flash untuk generate. Akun Premium bisa pilih dari berbagai model AI dan dapat prioritas generate.',
  },
  {
    question_en: 'Will the same config always produce the same result?',
    answer_en: 'Yes! SatsetUI is deterministic — the same wizard selections always produce identical output. No randomness, no surprises.',
    question_id: 'Apakah konfigurasi yang sama selalu hasilkan output yang sama?',
    answer_id: 'Ya! SatsetUI bersifat deterministik — pilihan wizard yang sama selalu menghasilkan output yang identik. Tanpa random, tanpa kejutan.',
  },
]);

const openIndex = ref<number | null>(null);

function toggle(index: number) {
  openIndex.value = openIndex.value === index ? null : index;
}
</script>

<template>
  <section id="faq" class="py-24 bg-white dark:bg-slate-800">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4">
          {{ currentLang === 'id' ? 'Pertanyaan Umum' : 'Frequently Asked Questions' }}
        </h2>
        <p class="text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
          {{ currentLang === 'id' ? 'Jawaban untuk pertanyaan yang sering ditanyakan.' : 'Answers to the most common questions.' }}
        </p>
      </div>

      <div class="max-w-3xl mx-auto space-y-4">
        <div 
          v-for="(faq, index) in faqs" 
          :key="index"
          class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-all"
          :class="openIndex === index ? 'bg-slate-50 dark:bg-slate-700/50' : 'bg-white dark:bg-slate-800'"
        >
          <button 
            @click="toggle(index)"
            class="w-full flex items-center justify-between px-6 py-5 text-left transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/50"
          >
            <span class="text-lg font-semibold text-slate-900 dark:text-white pr-4">
              {{ currentLang === 'id' ? faq.question_id : faq.question_en }}
            </span>
            <svg 
              class="w-5 h-5 text-slate-500 dark:text-slate-400 flex-shrink-0 transition-transform duration-200"
              :class="{ 'rotate-180': openIndex === index }"
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div 
            v-show="openIndex === index"
            class="px-6 pb-5"
          >
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
              {{ currentLang === 'id' ? faq.answer_id : faq.answer_en }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
