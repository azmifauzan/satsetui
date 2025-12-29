<script setup lang="ts">
import { wizardState, CodeStyle, OutputIntent } from '../wizardState';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

const codeStyleOptions: { value: CodeStyle; label: string; description: string; targetAudience: string }[] = [
  {
    value: 'minimal',
    label: 'Clean & Minimal',
    description: 'Kode ringkas, komentar minimal',
    targetAudience: 'Developer berpengalaman'
  },
  {
    value: 'verbose',
    label: 'Verbose & Explicit',
    description: 'Nama variabel eksplisit, logika lebih panjang',
    targetAudience: 'Developer menengah'
  },
  {
    value: 'documented',
    label: 'Commented for Learning',
    description: 'Dokumentasi berat, penjelasan lengkap',
    targetAudience: 'Pemula, tim belajar'
  },
];

const outputIntentOptions: { value: OutputIntent; label: string; description: string; includes: string[] }[] = [
  {
    value: 'mvp',
    label: 'MVP-Ready Scaffold',
    description: 'Memulai cepat dengan konten placeholder dan fungsionalitas dasar',
    includes: ['Struktur dasar', 'Konten placeholder', 'Fungsi sederhana', 'Prototyping cepat']
  },
  {
    value: 'production',
    label: 'Production-Ready Base',
    description: 'Kode robust dengan error handling dan pertimbangan aksesibilitas',
    includes: ['Error handling', 'Aksesibilitas (ARIA)', 'Validasi', 'Best practices']
  },
  {
    value: 'design-system',
    label: 'Design System Starter',
    description: 'Library komponen dengan dokumentasi dan pola yang dapat digunakan ulang',
    includes: ['Dokumentasi komponen', 'Pola reusable', 'Style guide', 'Standar']
  },
];
</script>

<template>
  <div class="space-y-10">
    <!-- Code Preferences -->
    <div class="space-y-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.codeStyle?.title || 'Preferensi Kode' }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400">
          {{ t.wizard?.steps?.codeStyle?.description || 'Kontrol gaya kode dan verbosity dalam output yang dihasilkan.' }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
          v-for="style in codeStyleOptions"
          :key="style.value"
          @click="wizardState.codeStyle = style.value"
          :class="[
            'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg',
            wizardState.codeStyle === style.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ style.label }}
            </h3>
            <div
              :class="[
                'w-6 h-6 rounded-full border-2 flex items-center justify-center',
                wizardState.codeStyle === style.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.codeStyle === style.value"
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
          <p class="text-slate-600 dark:text-slate-400 text-sm mb-3">
            {{ style.description }}
          </p>
          <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
            <p class="text-xs text-slate-500 dark:text-slate-500">
              <strong>Target:</strong> {{ style.targetAudience }}
            </p>
          </div>
        </button>
      </div>

      <!-- Code Examples -->
      <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Contoh Dampak</h3>
        <div class="text-xs font-mono">
          <div v-if="wizardState.codeStyle === 'minimal'" class="text-slate-600 dark:text-slate-400">
            <code>const u = users.filter(u => u.active);</code>
          </div>
          <div v-else-if="wizardState.codeStyle === 'verbose'" class="text-slate-600 dark:text-slate-400">
            <code>const activeUsers = allUsers.filter(user => user.isActive === true);</code>
          </div>
          <div v-else class="text-slate-600 dark:text-slate-400 space-y-1">
            <div><code>// Filter hanya user aktif dari daftar lengkap user</code></div>
            <div><code>const activeUsers = allUsers.filter(user => {</code></div>
            <div><code>  return user.isActive === true;</code></div>
            <div><code>});</code></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t-2 border-slate-200 dark:border-slate-700"></div>

    <!-- Output Intent -->
    <div class="space-y-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.outputIntent?.title || 'Output Intent' }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400">
          {{ t.wizard?.steps?.outputIntent?.description || 'Pilih tingkat kematangan yang diharapkan dari kode yang dihasilkan.' }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
          v-for="intent in outputIntentOptions"
          :key="intent.value"
          @click="wizardState.outputIntent = intent.value"
          :class="[
            'p-5 rounded-xl border-2 text-left transition-all hover:shadow-lg',
            wizardState.outputIntent === intent.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ intent.label }}
            </h3>
            <div
              :class="[
                'w-6 h-6 rounded-full border-2 flex items-center justify-center',
                wizardState.outputIntent === intent.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.outputIntent === intent.value"
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
          <p class="text-slate-600 dark:text-slate-400 text-sm mb-3">
            {{ intent.description }}
          </p>
          <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Termasuk:</p>
            <ul class="space-y-1">
              <li v-for="item in intent.includes" :key="item" class="text-xs text-slate-500 dark:text-slate-500 flex items-start">
                <svg class="w-3 h-3 text-blue-500 mr-1 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                {{ item }}
              </li>
            </ul>
          </div>
        </button>
      </div>

      <!-- Summary Box -->
      <div class="p-6 rounded-xl border-2 border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
          🎉 Siap untuk Generate!
        </h3>
        <p class="text-slate-600 dark:text-slate-400 text-sm">
          Anda telah menyelesaikan semua 5 langkah. Klik tombol "Next" atau "Generate Template" untuk membuat template kustom Anda berdasarkan pilihan yang telah dibuat.
        </p>
      </div>
    </div>
  </div>
</template>




