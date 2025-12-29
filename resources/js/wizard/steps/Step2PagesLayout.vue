<script setup lang="ts">
import { wizardState, Page, NavigationType, SidebarState, FooterStyle, suggestedPages, shouldShowSidebarState, syncSidebarState } from '../wizardState';
import { computed, watch } from 'vue';
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

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

const navigationOptions: { value: NavigationType; label: string; description: string }[] = [
  { value: 'sidebar', label: 'Sidebar', description: 'Menu vertikal, ideal untuk banyak item' },
  { value: 'topbar', label: 'Top Navigation', description: 'Menu horizontal, minimalis' },
  { value: 'hybrid', label: 'Hybrid (Sidebar + Topbar)', description: 'Kombinasi keduanya' },
];

const sidebarStates: { value: SidebarState; label: string }[] = [
  { value: 'expanded', label: 'Expanded by Default' },
  { value: 'collapsed', label: 'Collapsed by Default' },
];

const footerOptions: { value: FooterStyle; label: string }[] = [
  { value: 'minimal', label: 'Minimal (copyright only)' },
  { value: 'full', label: 'Full (with links)' },
];

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

function selectNavigation(nav: NavigationType) {
  wizardState.layout.navigation = nav;
  syncSidebarState();
}

watch(() => wizardState.layout.navigation, () => {
  syncSidebarState();
});
</script>

<template>
  <div class="space-y-10">
    <!-- Pages Selection -->
    <div class="space-y-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.pages?.title || 'Pilih Halaman' }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400 mb-4">
          {{ t.wizard?.steps?.pages?.description || 'Pilih halaman-halaman yang akan disertakan dalam template Anda. Pilih minimal satu halaman.' }}
        </p>
        <button
          @click="selectSuggested"
          class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors text-sm font-medium"
        >
          Gunakan Halaman yang Disarankan untuk {{ wizardState.category }}
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
                  Disarankan
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
          ⚠️ Silakan pilih minimal satu halaman untuk melanjutkan
        </p>
      </div>
      <div v-else class="text-sm text-slate-600 dark:text-slate-400">
        Dipilih: <strong>{{ wizardState.pages.length }}</strong> halaman
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t-2 border-slate-200 dark:border-slate-700"></div>

    <!-- Layout Configuration -->
    <div class="space-y-8">
      <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
          {{ t.wizard?.steps?.layout?.title || 'Layout & Navigasi' }}
        </h2>
        <p class="text-slate-600 dark:text-slate-400">
          {{ t.wizard?.steps?.layout?.description || 'Konfigurasi pola navigasi struktural dan elemen layout untuk template Anda.' }}
        </p>
      </div>

      <!-- Navigation Style -->
      <div class="space-y-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          Gaya Navigasi
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
          Status Default Sidebar
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
          Elemen Tambahan
        </h3>
        <label class="flex items-center justify-between p-4 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer hover:border-blue-400 transition-all">
          <div>
            <div class="font-medium text-slate-900 dark:text-white">Aktifkan Breadcrumbs</div>
            <div class="text-sm text-slate-600 dark:text-slate-400">Tampilkan breadcrumb navigasi pada halaman</div>
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
          Gaya Footer
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
  </div>
</template>




