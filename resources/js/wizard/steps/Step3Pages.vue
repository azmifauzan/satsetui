<script setup lang="ts">
import { wizardState, Page, suggestedPages } from '../wizardState';
import { computed } from 'vue';

const allPages: { value: Page; label: string; category: string }[] = [
  { value: 'login', label: 'Login', category: 'Authentication' },
  { value: 'register', label: 'Register', category: 'Authentication' },
  { value: 'forgot-password', label: 'Forgot Password', category: 'Authentication' },
  { value: 'dashboard', label: 'Dashboard', category: 'Application' },
  { value: 'user-management', label: 'User Management', category: 'Application' },
  { value: 'settings', label: 'Settings', category: 'Application' },
  { value: 'charts', label: 'Charts / Analytics', category: 'Application' },
  { value: 'tables', label: 'Tables / Data List', category: 'Application' },
  { value: 'profile', label: 'Profile', category: 'Application' },
  { value: 'about', label: 'About', category: 'Public' },
  { value: 'contact', label: 'Contact', category: 'Public' },
];

const pagesByCategory = computed(() => {
  const grouped: Record<string, typeof allPages> = {};
  allPages.forEach(page => {
    if (!grouped[page.category]) {
      grouped[page.category] = [];
    }
    grouped[page.category].push(page);
  });
  return grouped;
});

function togglePage(page: Page) {
  const index = wizardState.pages.indexOf(page);
  if (index > -1) {
    wizardState.pages.splice(index, 1);
  } else {
    wizardState.pages.push(page);
  }
}

function isSelected(page: Page): boolean {
  return wizardState.pages.includes(page);
}

function isSuggested(page: Page): boolean {
  return suggestedPages.value.includes(page);
}

function selectSuggested() {
  wizardState.pages = [...suggestedPages.value];
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        Select Pages
      </h2>
      <p class="text-slate-600 dark:text-slate-400 mb-4">
        Choose the specific pages to include in your template. Select at least one page.
      </p>
      <button
        @click="selectSuggested"
        class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors text-sm font-medium"
      >
        Use Suggested Pages for {{ wizardState.category }}
      </button>
    </div>

    <div v-for="(pages, category) in pagesByCategory" :key="category" class="space-y-3">
      <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
        {{ category }}
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <button
          v-for="page in pages"
          :key="page.value"
          @click="togglePage(page.value)"
          :class="[
            'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md relative',
            isSelected(page.value)
              ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-400'
          ]"
        >
          <div class="flex items-center justify-between">
            <span class="text-slate-900 dark:text-white font-medium">
              {{ page.label }}
            </span>
            <div class="flex items-center gap-2">
              <span
                v-if="isSuggested(page.value) && !isSelected(page.value)"
                class="text-xs px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded"
              >
                Suggested
              </span>
              <div
                :class="[
                  'w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0',
                  isSelected(page.value)
                    ? 'border-blue-600 bg-blue-600'
                    : 'border-slate-300 dark:border-slate-600'
                ]"
              >
                <svg
                  v-if="isSelected(page.value)"
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
          </div>
        </button>
      </div>
    </div>

    <div v-if="wizardState.pages.length === 0" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
      <p class="text-yellow-800 dark:text-yellow-200 text-sm">
        ⚠️ Please select at least one page to continue
      </p>
    </div>
    <div v-else class="text-sm text-slate-600 dark:text-slate-400">
      Selected: <strong>{{ wizardState.pages.length }}</strong> page(s)
    </div>
  </div>
</template>




