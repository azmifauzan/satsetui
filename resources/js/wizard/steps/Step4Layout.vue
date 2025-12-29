<script setup lang="ts">
import { wizardState, NavigationType, SidebarState, FooterStyle, shouldShowSidebarState, syncSidebarState } from '../wizardState';
import { watch } from 'vue';

const navigationOptions: { value: NavigationType; label: string; description: string }[] = [
  { value: 'sidebar', label: 'Sidebar', description: 'Vertical menu, ideal for many items' },
  { value: 'topbar', label: 'Top Navigation', description: 'Horizontal menu bar, clean' },
  { value: 'hybrid', label: 'Hybrid (Sidebar + Topbar)', description: 'Best of both worlds' },
];

const sidebarStates: { value: SidebarState; label: string }[] = [
  { value: 'expanded', label: 'Expanded by Default' },
  { value: 'collapsed', label: 'Collapsed by Default' },
];

const footerOptions: { value: FooterStyle; label: string }[] = [
  { value: 'minimal', label: 'Minimal (copyright only)' },
  { value: 'full', label: 'Full (with links)' },
];

function selectNavigation(nav: NavigationType) {
  wizardState.layout.navigation = nav;
  syncSidebarState();
}

watch(() => wizardState.layout.navigation, () => {
  syncSidebarState();
});
</script>

<template>
  <div class="space-y-8">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        Layout & Navigation
      </h2>
      <p class="text-slate-600 dark:text-slate-400">
        Configure the structural navigation patterns and layout elements for your template.
      </p>
    </div>

    <!-- Navigation Style -->
    <div class="space-y-3">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
        Navigation Style
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
          v-for="nav in navigationOptions"
          :key="nav.value"
          @click="selectNavigation(nav.value)"
          :class="[
            'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
            wizardState.layout.navigation === nav.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-center justify-between mb-2">
            <h4 class="font-semibold text-slate-900 dark:text-white">{{ nav.label }}</h4>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                wizardState.layout.navigation === nav.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.layout.navigation === nav.value"
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
          <p class="text-sm text-slate-600 dark:text-slate-400">{{ nav.description }}</p>
        </button>
      </div>
    </div>

    <!-- Sidebar Default State (conditional) -->
    <div v-if="shouldShowSidebarState" class="space-y-3">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
        Sidebar Default State
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <button
          v-for="state in sidebarStates"
          :key="state.value"
          @click="wizardState.layout.sidebarDefaultState = state.value"
          :class="[
            'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
            wizardState.layout.sidebarDefaultState === state.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-center justify-between">
            <span class="font-medium text-slate-900 dark:text-white">{{ state.label }}</span>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                wizardState.layout.sidebarDefaultState === state.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.layout.sidebarDefaultState === state.value"
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

    <!-- Breadcrumbs Toggle -->
    <div class="space-y-3">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
        Additional Elements
      </h3>
      <label class="flex items-center justify-between p-4 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer hover:border-blue-400 transition-all">
        <div>
          <div class="font-medium text-slate-900 dark:text-white">Enable Breadcrumbs</div>
          <div class="text-sm text-slate-600 dark:text-slate-400">Show navigation breadcrumbs on pages</div>
        </div>
        <input
          v-model="wizardState.layout.breadcrumbs"
          type="checkbox"
          class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
        />
      </label>
    </div>

    <!-- Footer Style -->
    <div class="space-y-3">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
        Footer Style
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <button
          v-for="footer in footerOptions"
          :key="footer.value"
          @click="wizardState.layout.footer = footer.value"
          :class="[
            'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
            wizardState.layout.footer === footer.value
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-center justify-between">
            <span class="font-medium text-slate-900 dark:text-white">{{ footer.label }}</span>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                wizardState.layout.footer === footer.value
                  ? 'border-blue-600 bg-blue-600'
                  : 'border-slate-300 dark:border-slate-600'
              ]"
            >
              <svg
                v-if="wizardState.layout.footer === footer.value"
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
  </div>
</template>




