<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/lib/i18n';
import { useTheme } from '@/lib/theme';

const { currentLang, setLang } = useI18n();
const { isDark, toggleTheme } = useTheme();
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
    <!-- Language & Theme Switcher -->
    <div class="absolute top-4 right-4 flex items-center gap-2">
      <!-- Language Switcher -->
      <button
        @click="setLang(currentLang === 'id' ? 'en' : 'id')"
        class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-white/50 dark:hover:bg-slate-800/50 rounded-lg transition-colors"
      >
        {{ currentLang === 'id' ? '🇬🇧 EN' : '🇮🇩 ID' }}
      </button>

      <!-- Theme Toggle -->
      <button
        @click="toggleTheme"
        class="p-2 text-slate-600 dark:text-slate-300 hover:bg-white/50 dark:hover:bg-slate-800/50 rounded-lg transition-colors"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
      >
        <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
      </button>
    </div>

    <div class="max-w-md w-full">
      <!-- Logo -->
      <div class="text-center mb-8">
        <Link href="/" class="inline-flex items-center space-x-2 mb-6">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
          </div>
          <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
            SatsetUI
          </span>
        </Link>
      </div>

      <!-- Content Card -->
      <div class="bg-white dark:bg-slate-800 shadow-2xl rounded-2xl p-8">
        <slot />
      </div>
    </div>
  </div>
</template>
