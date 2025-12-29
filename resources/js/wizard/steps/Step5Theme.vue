<script setup lang="ts">
import { wizardState, ThemeMode, BackgroundStyle } from '../wizardState';

const presetColors = [
  { name: 'Blue', value: '#3B82F6' },
  { name: 'Green', value: '#10B981' },
  { name: 'Purple', value: '#8B5CF6' },
  { name: 'Red', value: '#EF4444' },
  { name: 'Orange', value: '#F59E0B' },
  { name: 'Pink', value: '#EC4899' },
  { name: 'Indigo', value: '#6366F1' },
  { name: 'Teal', value: '#14B8A6' },
];

const modes: { value: ThemeMode; label: string; icon: string }[] = [
  { value: 'light', label: 'Light Mode', icon: 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z' },
  { value: 'dark', label: 'Dark Mode', icon: 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z' },
];

const backgrounds: { value: BackgroundStyle; label: string }[] = [
  { value: 'solid', label: 'Solid' },
  { value: 'gradient', label: 'Subtle Gradient' },
];
</script>

<template>
  <div class="space-y-8">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        Theme & Visual Identity
      </h2>
      <p class="text-slate-600 dark:text-slate-400">
        Define your color scheme and visual mode preferences.
      </p>
    </div>

    <!-- Primary Color -->
    <div class="space-y-3">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
        Primary Color
      </h3>
      <div class="flex flex-wrap gap-3 items-center">
        <button
          v-for="color in presetColors"
          :key="color.value"
          @click="wizardState.theme.primary = color.value"
          :class="[
            'w-12 h-12 rounded-lg transition-all hover:scale-110',
            wizardState.theme.primary === color.value
              ? 'ring-4 ring-blue-500 ring-offset-2 dark:ring-offset-slate-900'
              : 'hover:ring-2 hover:ring-slate-300'
          ]"
          :style="{ backgroundColor: color.value }"
          :title="color.name"
        />
        <div class="flex items-center gap-2 ml-2">
          <input
            v-model="wizardState.theme.primary"
            type="color"
            class="w-12 h-12 rounded-lg cursor-pointer"
          />
          <input
            v-model="wizardState.theme.primary"
            type="text"
            class="px-3 py-2 border-2 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono text-sm uppercase w-24"
            pattern="^#[0-9A-Fa-f]{6}$"
          />
        </div>
      </div>
    </div>

    <!-- Secondary Color -->
    <div class="space-y-3">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
        Secondary Color
      </h3>
      <div class="flex flex-wrap gap-3 items-center">
        <button
          v-for="color in presetColors"
          :key="color.value"
          @click="wizardState.theme.secondary = color.value"
          :class="[
            'w-12 h-12 rounded-lg transition-all hover:scale-110',
            wizardState.theme.secondary === color.value
              ? 'ring-4 ring-blue-500 ring-offset-2 dark:ring-offset-slate-900'
              : 'hover:ring-2 hover:ring-slate-300'
          ]"
          :style="{ backgroundColor: color.value }"
          :title="color.name"
        />
        <div class="flex items-center gap-2 ml-2">
          <input
            v-model="wizardState.theme.secondary"
            type="color"
            class="w-12 h-12 rounded-lg cursor-pointer"
          />
          <input
            v-model="wizardState.theme.secondary"
            type="text"
            class="px-3 py-2 border-2 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono text-sm uppercase w-24"
            pattern="^#[0-9A-Fa-f]{6}$"
          />
        </div>
      </div>
    </div>

    <!-- Color Mode -->
    <div class="space-y-3">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
        Color Mode
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <button
          v-for="mode in modes"
          :key="mode.value"
          @click="wizardState.theme.mode = mode.value"
          :class="[
            'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
            wizardState.theme.mode === mode.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="mode.icon" />
                </svg>
              </div>
              <span class="font-medium text-slate-900 dark:text-white">{{ mode.label }}</span>
            </div>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                wizardState.theme.mode === mode.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.theme.mode === mode.value"
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
        </button>
      </div>
    </div>

    <!-- Background Style -->
    <div class="space-y-3">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
        Background Style
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <button
          v-for="bg in backgrounds"
          :key="bg.value"
          @click="wizardState.theme.background = bg.value"
          :class="[
            'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
            wizardState.theme.background === bg.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-center justify-between">
            <span class="font-medium text-slate-900 dark:text-white">{{ bg.label }}</span>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                wizardState.theme.background === bg.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.theme.background === bg.value"
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
        </button>
      </div>
    </div>

    <!-- Preview -->
    <div class="p-6 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
      <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Preview</h3>
      <div class="flex gap-4 items-center">
        <div class="flex gap-2 items-center">
          <div class="w-16 h-16 rounded-lg shadow-lg" :style="{ backgroundColor: wizardState.theme.primary }" />
          <span class="text-sm text-slate-600 dark:text-slate-400">Primary</span>
        </div>
        <div class="flex gap-2 items-center">
          <div class="w-16 h-16 rounded-lg shadow-lg" :style="{ backgroundColor: wizardState.theme.secondary }" />
          <span class="text-sm text-slate-600 dark:text-slate-400">Secondary</span>
        </div>
      </div>
    </div>
  </div>
</template>




