<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useTheme } from '@/lib/theme';
import { useI18n } from '@/lib/i18n';
import Navbar from '@/components/landing/Navbar.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import FeaturesSection from '@/components/landing/FeaturesSection.vue';
import HowItWorksSection from '@/components/landing/HowItWorksSection.vue';
import FaqSection from '@/components/landing/FaqSection.vue';
import CtaSection from '@/components/landing/CtaSection.vue';
import Footer from '@/components/landing/Footer.vue';

const { isDark, toggleTheme } = useTheme();
const { toggleLanguage } = useI18n();

const page = usePage();
const auth = computed(() => (page.props as any).auth);

function scrollToSection(sectionId: string) {
  const el = document.getElementById(sectionId);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
}
</script>

<template>
  <div class="min-h-screen" :class="isDark ? 'dark' : ''">
    <div class="bg-white dark:bg-slate-900 transition-colors duration-200">
      <Navbar
        :auth="auth"
        :is-dark="isDark"
        @toggle-theme="toggleTheme"
        @toggle-lang="toggleLanguage"
        @scroll-to-section="scrollToSection"
      />
      <main>
        <HeroSection />
        <FeaturesSection />
        <HowItWorksSection />
        <FaqSection />
        <CtaSection />
      </main>
      <Footer />
    </div>
  </div>
</template>
