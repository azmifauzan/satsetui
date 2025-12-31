<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from '@/lib/i18n';
import Navbar from '@/components/landing/Navbar.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import HowItWorksSection from '@/components/landing/HowItWorksSection.vue';
import CategoriesSection from '@/components/landing/CategoriesSection.vue';
import FaqSection from '@/components/landing/FaqSection.vue';
import CtaSection from '@/components/landing/CtaSection.vue';
import Footer from '@/components/landing/Footer.vue';

interface Props {
  auth?: {
    user: {
      name: string;
      email: string;
    };
  };
}

const props = defineProps<Props>();

const { t, setLang, currentLang } = useI18n();
const isDark = ref(false); // Default: Light mode

const toggleLang = () => {
  setLang(currentLang.value === 'id' ? 'en' : 'id');
};

// Initialize dark mode from localStorage
onMounted(() => {
  const savedTheme = localStorage.getItem('theme');
  isDark.value = savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
  updateTheme();
});

const toggleTheme = () => {
  isDark.value = !isDark.value;
  updateTheme();
};

const updateTheme = () => {
  if (isDark.value) {
    document.documentElement.classList.add('dark');
    localStorage.setItem('theme', 'dark');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('theme', 'light');
  }
};

const scrollToSection = (sectionId: string) => {
  const element = document.getElementById(sectionId);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
  }
};
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <Head :title="t.landing?.title || 'Template Generator'" />

    <!-- Navbar -->
    <Navbar 
      :auth="auth" 
      :isDark="isDark"
      @toggle-theme="toggleTheme"
      @toggle-lang="toggleLang"
      @scroll-to-section="scrollToSection"
    />

    <!-- Hero Section -->
    <HeroSection />

    <!-- How It Works Section -->
    <HowItWorksSection />

    <!-- Categories Section -->
    <CategoriesSection />

    <!-- FAQ Section -->
    <FaqSection />

    <!-- CTA Section -->
    <CtaSection />

    <!-- Footer -->
    <Footer :on-scroll-to-section="scrollToSection" />
  </div>
</template>




