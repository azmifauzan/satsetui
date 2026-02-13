<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/lib/i18n';
import { useTheme } from '@/lib/theme';

interface User {
  id: number;
  name: string;
  email: string;
  language?: string;
  is_admin?: boolean;
}

const page = usePage();
const user = computed(() => page.props.auth?.user as User);
const { t, currentLang, setLang } = useI18n();
const { toggleTheme, isDark } = useTheme();

const sidebarOpen = ref(true);
const userMenuOpen = ref(false);

const logout = () => {
  router.post('/logout');
};

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const changeLanguage = (lang: 'id' | 'en') => {
  setLang(lang);
  router.post('/language', { language: lang }, {
    preserveScroll: true,
    onSuccess: () => {
      // Language changed successfully
    }
  });
};

const navigation = computed(() => {
  const nav = [
    { name: t.value?.nav?.dashboard || 'Dashboard', href: '/dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', current: page.url === '/dashboard' },
    { name: t.value?.nav?.templates || 'My Satsets', href: '/templates', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', current: page.url === '/templates' },
  ];
  
  // Add admin menu if user is admin
  if (user.value?.is_admin) {
    nav.push({
      name: t.value?.nav?.admin || 'Admin',
      href: '/admin',
      icon: 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
      current: page.url.startsWith('/admin')
    });
  }
  
  return nav;
});
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 transform transition-transform duration-200 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- Logo -->
      <div class="flex items-center justify-between h-16 px-6 border-b border-slate-200 dark:border-slate-700">
        <Link href="/" class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
          </div>
          <span class="text-xl font-bold text-slate-900 dark:text-white">Satset<span class="text-blue-600">UI</span></span>
        </Link>
        <button
          @click="toggleSidebar"
          class="lg:hidden text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 py-6 space-y-1">
        <Link
          v-for="item in navigation"
          :key="item.name"
          :href="item.href"
          :class="[
            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
            item.current
              ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
              : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50'
          ]"
        >
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
          </svg>
          {{ item.name }}
        </Link>
      </nav>
    </aside>

    <!-- Main Content -->
    <div :class="['transition-all duration-200', sidebarOpen ? 'lg:ml-64' : 'ml-0']">
      <!-- Top Bar -->
      <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-40">
        <div class="flex items-center justify-between h-16 px-6">
          <button
            @click="toggleSidebar"
            class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <div class="flex items-center space-x-4">
            <!-- Credit Display -->
            <div class="flex items-center gap-1.5 px-3 py-2 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7 3h10l4 4-9 14L3 7l4-4zm5 2L9 7h6l-3-2zm-6 3l3.5 8L5 8zm7.5 8L17 8l-3.5 8z"/>
              </svg>
              <span class="text-sm font-bold text-blue-700 dark:text-blue-300">{{ page.props.auth?.user?.credits || 0 }}</span>
              <span class="text-xs text-blue-600 dark:text-blue-400">{{ t.common?.credits || 'kredit' }}</span>
            </div>

            <!-- Language Selector -->
            <div class="flex items-center space-x-1 p-1 bg-slate-100 dark:bg-slate-700 rounded-lg">
              <button
                @click="changeLanguage('id')"
                :class="[
                  'px-3 py-1 text-sm font-medium rounded transition-colors',
                  currentLang === 'id'
                    ? 'bg-white dark:bg-slate-600 text-blue-600 dark:text-blue-400 shadow-sm'
                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
              >
                ID
              </button>
              <button
                @click="changeLanguage('en')"
                :class="[
                  'px-3 py-1 text-sm font-medium rounded transition-colors',
                  currentLang === 'en'
                    ? 'bg-white dark:bg-slate-600 text-blue-600 dark:text-blue-400 shadow-sm'
                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
              >
                EN
              </button>
            </div>

            <!-- Theme Toggle -->
            <button
              @click="toggleTheme"
              class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
              :title="isDark ? (t.theme?.light || 'Light Mode') : (t.theme?.dark || 'Dark Mode')"
            >
              <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
              </svg>
            </button>

            <!-- Notifications -->
            <button class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 relative">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- User Menu -->
            <div class="relative">
              <button
                @click="userMenuOpen = !userMenuOpen"
                class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
              >
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
                  {{ user?.name?.charAt(0).toUpperCase() }}
                </div>
                <div class="hidden md:block text-left">
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ user?.name }}</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">{{ user?.email }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <div
                v-if="userMenuOpen"
                class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-2 z-50"
              >
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                  <p class="text-sm font-medium text-slate-900 dark:text-white">{{ user?.name }}</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">{{ user?.email }}</p>
                </div>

                <Link href="/dashboard" class="flex items-center px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                  </svg>
                  {{ t.nav?.dashboard || 'Dashboard' }}
                </Link>

                <Link href="/profile" class="flex items-center px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {{ t.nav?.profile || 'Profile' }}
                </Link>

                <div class="border-t border-slate-200 dark:border-slate-700 my-2"></div>

                <button
                  @click="logout"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                >
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  {{ t.nav?.logout || 'Logout' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-6">
        <slot />
      </main>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div
      v-if="sidebarOpen"
      @click="toggleSidebar"
      class="lg:hidden fixed inset-0 bg-slate-900/50 z-40"
    ></div>
  </div>
</template>




