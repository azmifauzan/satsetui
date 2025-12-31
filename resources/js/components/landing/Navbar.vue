<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from '@/lib/i18n';

interface Props {
  auth?: {
    user: {
      name: string;
      email: string;
    };
  };
  isDark: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  toggleTheme: [];
  toggleLang: [];
  scrollToSection: [sectionId: string];
}>();

const { currentLang } = useI18n();

const mobileMenuOpen = ref(false);

const logout = () => {
  router.post('/logout');
};

const scrollToSection = (sectionId: string) => {
  mobileMenuOpen.value = false;
  emit('scrollToSection', sectionId);
};
</script>

<template>
  <!-- Navbar -->
  <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-b border-slate-200 dark:border-slate-700">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center">
          <Link href="/" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
              </svg>
            </div>
            <span class="text-xl font-bold text-slate-900 dark:text-white">Template<span class="text-blue-600">Gen</span></span>
          </Link>
        </div>

        <!-- Desktop Menu -->
        <div class="hidden md:flex items-center space-x-8">
          <Link href="/" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
            {{ currentLang === 'id' ? 'Beranda' : 'Home' }}
          </Link>
          <button @click="emit('scrollToSection', 'how-it-works')" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
            {{ currentLang === 'id' ? 'Cara Kerja' : 'How It Works' }}
          </button>
          <button @click="emit('scrollToSection', 'categories')" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
            {{ currentLang === 'id' ? 'Kategori' : 'Categories' }}
          </button>
          <button @click="emit('scrollToSection', 'faq')" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
            FAQ
          </button>
        </div>

        <!-- Action Buttons (Desktop) -->
        <div class="hidden md:flex items-center space-x-3">
          <!-- Dark Mode Toggle -->
          <button 
            @click="emit('toggleTheme')"
            class="p-2 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
            :title="isDark ? 'Light Mode' : 'Dark Mode'"
          >
            <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <!-- Language Toggle -->
          <button @click="emit('toggleLang')" class="text-sm text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium px-2 py-1">
            {{ currentLang === 'id' ? 'EN' : 'ID' }}
          </button>

          <!-- Not Logged In -->
          <template v-if="!auth?.user">
            <Link href="/login" class="inline-flex items-center px-4 py-2 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium">
              {{ currentLang === 'id' ? 'Masuk' : 'Login' }}
            </Link>
            <Link href="/register" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium shadow-md hover:shadow-lg">
              {{ currentLang === 'id' ? 'Mulai Sekarang' : 'Get Started' }}
              <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </Link>
          </template>

          <!-- Logged In -->
          <template v-else>
            <Link href="/dashboard" class="inline-flex items-center px-4 py-2 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium">
              Dashboard
            </Link>
            <div class="flex items-center space-x-2 text-slate-600 dark:text-slate-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span class="font-medium">{{ auth.user.name }}</span>
            </div>
            <Link href="/wizard" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium shadow-md hover:shadow-lg">
              {{ currentLang === 'id' ? 'Buat Template' : 'Create Template' }}
            </Link>
            <button 
              @click="logout"
              class="inline-flex items-center px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors font-medium"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
            </button>
          </template>
        </div>

        <!-- Mobile Action Buttons -->
        <div class="flex md:hidden items-center space-x-2">
          <!-- Dark Mode Toggle (Mobile) -->
          <button 
            @click="emit('toggleTheme')"
            class="p-2 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
          >
            <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <!-- Language Toggle (Mobile) -->
          <button @click="emit('toggleLang')" class="text-sm text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium px-2 py-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
            {{ currentLang === 'id' ? 'EN' : 'ID' }}
          </button>

          <!-- Hamburger Menu Button -->
          <button 
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="p-2 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
          >
            <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div 
          v-if="mobileMenuOpen" 
          class="md:hidden py-4 border-t border-slate-200 dark:border-slate-700"
        >
          <!-- Navigation Links -->
          <div class="space-y-1 mb-4">
            <Link 
              href="/" 
              class="block px-4 py-3 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
              @click="mobileMenuOpen = false"
            >
              {{ currentLang === 'id' ? 'Beranda' : 'Home' }}
            </Link>
            <button 
              @click="scrollToSection('how-it-works')" 
              class="w-full text-left px-4 py-3 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
            >
              {{ currentLang === 'id' ? 'Cara Kerja' : 'How It Works' }}
            </button>
            <button 
              @click="scrollToSection('categories')" 
              class="w-full text-left px-4 py-3 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
            >
              {{ currentLang === 'id' ? 'Kategori' : 'Categories' }}
            </button>
            <button 
              @click="scrollToSection('faq')" 
              class="w-full text-left px-4 py-3 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
            >
              FAQ
            </button>
          </div>

          <!-- Auth Buttons (Mobile) -->
          <div class="space-y-2 pt-4 border-t border-slate-200 dark:border-slate-700">
            <template v-if="!auth?.user">
              <Link 
                href="/login" 
                class="block w-full px-4 py-3 text-center text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors font-medium"
                @click="mobileMenuOpen = false"
              >
                {{ currentLang === 'id' ? 'Masuk' : 'Login' }}
              </Link>
              <Link 
                href="/register" 
                class="block w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg transition-colors font-medium shadow-md"
                @click="mobileMenuOpen = false"
              >
                {{ currentLang === 'id' ? 'Mulai Sekarang' : 'Get Started' }}
              </Link>
            </template>

            <template v-else>
              <!-- User Info (Mobile) -->
              <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                <div class="flex items-center space-x-2 text-slate-600 dark:text-slate-300">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span class="font-medium">{{ auth.user.name }}</span>
                </div>
              </div>

              <Link 
                href="/dashboard" 
                class="block w-full px-4 py-3 text-center text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors font-medium"
                @click="mobileMenuOpen = false"
              >
                Dashboard
              </Link>
              
              <Link 
                href="/wizard" 
                class="block w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg transition-colors font-medium shadow-md"
                @click="mobileMenuOpen = false"
              >
                {{ currentLang === 'id' ? 'Buat Template' : 'Create Template' }}
              </Link>
              
              <button 
                @click="logout"
                class="w-full px-4 py-3 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors font-medium flex items-center justify-center space-x-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>{{ currentLang === 'id' ? 'Keluar' : 'Logout' }}</span>
              </button>
            </template>
          </div>
        </div>
      </transition>
    </div>
  </nav>
</template>
