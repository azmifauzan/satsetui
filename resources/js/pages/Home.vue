<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Props {
  auth?: {
    user: {
      name: string;
      email: string;
    };
  };
}

const props = defineProps<Props>();

const currentLang = ref<'id' | 'en'>('id'); // Default: Indonesian
const isDark = ref(false); // Default: Light mode

const logout = () => {
  router.post('/logout');
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

const translations = {
  id: {
    title: 'Template Generator - Buat Template Frontend dengan Mudah',
    nav: {
      home: 'Beranda',
      features: 'Fitur',
      howItWorks: 'Cara Kerja',
      pricing: 'Harga',
      docs: 'Dokumentasi',
      getStarted: 'Mulai Sekarang',
    },
    hero: {
      title: 'Buat Template Frontend Profesional dalam Hitungan Menit',
      subtitle: 'Platform wizard-driven untuk menghasilkan template UI yang konsisten, dapat diprediksi, dan siap produksi. Tanpa coding manual, tanpa hasil yang acak.',
      cta: 'Mulai Gratis',
      ctaSecondary: 'Lihat Demo',
      badge: 'Deterministik & Dapat Diulang',
    },
    stats: {
      templates: 'Template Dibuat',
      users: 'Pengguna Aktif',
      pages: 'Tipe Halaman',
      satisfaction: 'Kepuasan',
    },
    features: {
      title: 'Mengapa Memilih Template Generator?',
      subtitle: 'Solusi lengkap untuk kebutuhan template frontend Anda',
      wizard: {
        title: 'Wizard Terstruktur',
        desc: 'Konfigurasi melalui 11 langkah jelas tanpa prompt bebas. Setiap pilihan eksplisit dan terbatas.',
      },
      deterministic: {
        title: 'Hasil Deterministik',
        desc: 'Pilihan yang sama menghasilkan output yang sama, setiap saat. Dapat diulang dan dapat diprediksi.',
      },
      frameworks: {
        title: 'Multi Framework',
        desc: 'Dukungan untuk Tailwind CSS dan Bootstrap dengan komponen siap pakai.',
      },
      professional: {
        title: 'Kode Profesional',
        desc: 'Output bersih, terstruktur, dan mengikuti best practices industri.',
      },
      customizable: {
        title: 'Sangat Dapat Disesuaikan',
        desc: 'Pilih tema, layout, komponen, dan tingkat interaktivitas sesuai kebutuhan.',
      },
      instant: {
        title: 'Preview Instan',
        desc: 'Lihat hasil template Anda secara real-time sebelum diunduh.',
      },
    },
    howItWorks: {
      title: 'Cara Kerja',
      subtitle: 'Tiga langkah sederhana untuk template sempurna Anda',
      step1: {
        title: 'Pilih Konfigurasi',
        desc: 'Ikuti wizard 11 langkah untuk menentukan framework, kategori, halaman, layout, tema, dan preferensi lainnya.',
      },
      step2: {
        title: 'Sistem Menghasilkan Blueprint',
        desc: 'Pilihan Anda dikonversi menjadi blueprint JSON yang valid dan MCP prompt yang lengkap.',
      },
      step3: {
        title: 'Unduh & Gunakan',
        desc: 'Dapatkan kode template yang siap digunakan, lengkap dengan struktur file dan komponen.',
      },
    },
    categories: {
      title: 'Kategori Template',
      subtitle: 'Pilih dari berbagai kategori template yang telah disesuaikan',
      admin: {
        title: 'Admin Dashboard',
        desc: 'Internal tools, data-heavy, operasi CRUD',
      },
      company: {
        title: 'Company Profile',
        desc: 'Public-facing, showcase konten perusahaan',
      },
      landing: {
        title: 'Landing Page',
        desc: 'Marketing-focused, conversion-optimized',
      },
      saas: {
        title: 'SaaS Application',
        desc: 'User accounts, fitur lengkap, pricing',
      },
      blog: {
        title: 'Blog / Content',
        desc: 'Artikel, reading experience, categories',
      },
      portfolio: {
        title: 'Portfolio / Agency',
        desc: 'Showcase projects, case studies, gallery',
      },
    },
    cta: {
      title: 'Siap Membuat Template Profesional?',
      subtitle: 'Bergabunglah dengan ribuan developer yang telah mempercepat workflow mereka',
      button: 'Mulai Membuat Template',
      free: 'Gratis untuk memulai, tidak perlu kartu kredit',
    },
    footer: {
      product: 'Produk',
      features: 'Fitur',
      pricing: 'Harga',
      docs: 'Dokumentasi',
      changelog: 'Changelog',
      company: 'Perusahaan',
      about: 'Tentang',
      blog: 'Blog',
      careers: 'Karir',
      contact: 'Kontak',
      legal: 'Legal',
      privacy: 'Privasi',
      terms: 'Syarat & Ketentuan',
      rights: 'Hak Cipta Dilindungi.',
    },
  },
  en: {
    title: 'Template Generator - Create Frontend Templates Easily',
    nav: {
      home: 'Home',
      features: 'Features',
      howItWorks: 'How It Works',
      pricing: 'Pricing',
      docs: 'Documentation',
      getStarted: 'Get Started',
    },
    hero: {
      title: 'Create Professional Frontend Templates in Minutes',
      subtitle: 'A wizard-driven platform for generating consistent, predictable, and production-ready UI templates. No manual coding, no random results.',
      cta: 'Start Free',
      ctaSecondary: 'View Demo',
      badge: 'Deterministic & Repeatable',
    },
    stats: {
      templates: 'Templates Created',
      users: 'Active Users',
      pages: 'Page Types',
      satisfaction: 'Satisfaction',
    },
    features: {
      title: 'Why Choose Template Generator?',
      subtitle: 'Complete solution for your frontend template needs',
      wizard: {
        title: 'Structured Wizard',
        desc: 'Configure through 11 clear steps without free-form prompts. Every choice is explicit and constrained.',
      },
      deterministic: {
        title: 'Deterministic Results',
        desc: 'Same selections produce same output, every time. Repeatable and predictable.',
      },
      frameworks: {
        title: 'Multi Framework',
        desc: 'Support for Tailwind CSS and Bootstrap with ready-to-use components.',
      },
      professional: {
        title: 'Professional Code',
        desc: 'Clean, structured output following industry best practices.',
      },
      customizable: {
        title: 'Highly Customizable',
        desc: 'Choose themes, layouts, components, and interaction levels to fit your needs.',
      },
      instant: {
        title: 'Instant Preview',
        desc: 'See your template results in real-time before downloading.',
      },
    },
    howItWorks: {
      title: 'How It Works',
      subtitle: 'Three simple steps to your perfect template',
      step1: {
        title: 'Choose Configuration',
        desc: 'Follow the 11-step wizard to define framework, category, pages, layout, theme, and other preferences.',
      },
      step2: {
        title: 'System Generates Blueprint',
        desc: 'Your choices are converted into a valid JSON blueprint and complete MCP prompt.',
      },
      step3: {
        title: 'Download & Use',
        desc: 'Get ready-to-use template code, complete with file structure and components.',
      },
    },
    categories: {
      title: 'Template Categories',
      subtitle: 'Choose from various tailored template categories',
      admin: {
        title: 'Admin Dashboard',
        desc: 'Internal tools, data-heavy, CRUD operations',
      },
      company: {
        title: 'Company Profile',
        desc: 'Public-facing, company content showcase',
      },
      landing: {
        title: 'Landing Page',
        desc: 'Marketing-focused, conversion-optimized',
      },
      saas: {
        title: 'SaaS Application',
        desc: 'User accounts, full features, pricing',
      },
      blog: {
        title: 'Blog / Content',
        desc: 'Articles, reading experience, categories',
      },
      portfolio: {
        title: 'Portfolio / Agency',
        desc: 'Showcase projects, case studies, gallery',
      },
    },
    cta: {
      title: 'Ready to Create Professional Templates?',
      subtitle: 'Join thousands of developers who have accelerated their workflow',
      button: 'Start Creating Templates',
      free: 'Free to start, no credit card required',
    },
    footer: {
      product: 'Product',
      features: 'Features',
      pricing: 'Pricing',
      docs: 'Documentation',
      changelog: 'Changelog',
      company: 'Company',
      about: 'About',
      blog: 'Blog',
      careers: 'Careers',
      contact: 'Contact',
      legal: 'Legal',
      privacy: 'Privacy',
      terms: 'Terms & Conditions',
      rights: 'All Rights Reserved.',
    },
  },
};

const t = () => translations[currentLang.value];

const toggleLanguage = () => {
  currentLang.value = currentLang.value === 'id' ? 'en' : 'id';
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
    <Head :title="t().title" />

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
            <button @click="scrollToSection('why-choose')" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
              {{ currentLang === 'id' ? 'Mengapa Memilih' : 'Why Choose' }}
            </button>
            <button @click="scrollToSection('how-it-works')" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
              {{ t().nav.howItWorks }}
            </button>
            <button @click="scrollToSection('categories')" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
              {{ currentLang === 'id' ? 'Kategori' : 'Categories' }}
            </button>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center space-x-3">
            <!-- Dark Mode Toggle -->
            <button 
              @click="toggleTheme"
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
            <button @click="toggleLanguage" class="text-sm text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium px-2 py-1">
              {{ currentLang === 'id' ? 'EN' : 'ID' }}
            </button>

            <!-- Not Logged In -->
            <template v-if="!props.auth?.user">
              <!-- Login Button -->
              <Link href="/login" class="hidden sm:inline-flex items-center px-4 py-2 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium">
                {{ currentLang === 'id' ? 'Masuk' : 'Login' }}
              </Link>

              <!-- Get Started Button -->
              <Link href="/register" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium shadow-md hover:shadow-lg">
                {{ t().nav.getStarted }}
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </Link>
            </template>

            <!-- Logged In -->
            <template v-else>
              <!-- User Name -->
              <div class="hidden sm:flex items-center space-x-2 text-slate-600 dark:text-slate-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="font-medium">{{ props.auth.user.name }}</span>
              </div>

              <!-- Wizard Button -->
              <Link href="/wizard" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium shadow-md hover:shadow-lg">
                {{ currentLang === 'id' ? 'Buat Template' : 'Create Template' }}
              </Link>

              <!-- Logout Button -->
              <button 
                @click="logout"
                class="inline-flex items-center px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors font-medium"
              >
                {{ currentLang === 'id' ? 'Keluar' : 'Logout' }}
              </button>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
      <div class="container mx-auto">
        <div class="text-center max-w-4xl mx-auto">
          <div class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium mb-6">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            {{ t().hero.badge }}
          </div>
          
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 dark:text-white mb-6 leading-tight">
            {{ t().hero.title }}
          </h1>
          
          <p class="text-xl text-slate-600 dark:text-slate-300 mb-10 leading-relaxed">
            {{ t().hero.subtitle }}
          </p>
          
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link href="/register" class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all font-semibold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
              {{ t().hero.cta }}
              <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Section -->
    <section id="why-choose" class="py-20 bg-white dark:bg-slate-800/50">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white mb-4">
            {{ t().features.title }}
          </h2>
          <p class="text-lg text-slate-600 dark:text-slate-300">
            {{ t().features.subtitle }}
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <!-- Feature 1 -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-3">
              {{ t().features.wizard.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().features.wizard.desc }}
            </p>
          </div>

          <!-- Feature 2 -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-3">
              {{ t().features.deterministic.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().features.deterministic.desc }}
            </p>
          </div>

          <!-- Feature 3 -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-3">
              {{ t().features.frameworks.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().features.frameworks.desc }}
            </p>
          </div>

          <!-- Feature 4 -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-3">
              {{ t().features.professional.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().features.professional.desc }}
            </p>
          </div>

          <!-- Feature 5 -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-orange-600 rounded-xl flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-3">
              {{ t().features.customizable.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().features.customizable.desc }}
            </p>
          </div>

          <!-- Feature 6 -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow">
            <div class="w-12 h-12 bg-pink-600 rounded-xl flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-3">
              {{ t().features.instant.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().features.instant.desc }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white mb-4">
            {{ t().howItWorks.title }}
          </h2>
          <p class="text-lg text-slate-600 dark:text-slate-300">
            {{ t().howItWorks.subtitle }}
          </p>
        </div>

        <div class="max-w-5xl mx-auto">
          <div class="relative">
            <!-- Connection Line -->
            <div class="hidden md:block absolute top-1/2 left-0 right-0 h-1 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 -translate-y-1/2"></div>
            
            <div class="grid md:grid-cols-3 gap-8 relative">
              <!-- Step 1 -->
              <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg relative z-10">
                  <span class="text-3xl font-bold text-white">1</span>
                </div>
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-lg">
                  <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-3">
                    {{ t().howItWorks.step1.title }}
                  </h3>
                  <p class="text-slate-600 dark:text-slate-300">
                    {{ t().howItWorks.step1.desc }}
                  </p>
                </div>
              </div>

              <!-- Step 2 -->
              <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg relative z-10">
                  <span class="text-3xl font-bold text-white">2</span>
                </div>
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-lg">
                  <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-3">
                    {{ t().howItWorks.step2.title }}
                  </h3>
                  <p class="text-slate-600 dark:text-slate-300">
                    {{ t().howItWorks.step2.desc }}
                  </p>
                </div>
              </div>

              <!-- Step 3 -->
              <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-600 to-purple-700 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg relative z-10">
                  <span class="text-3xl font-bold text-white">3</span>
                </div>
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-lg">
                  <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-3">
                    {{ t().howItWorks.step3.title }}
                  </h3>
                  <p class="text-slate-600 dark:text-slate-300">
                    {{ t().howItWorks.step3.desc }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-20 bg-white dark:bg-slate-800/50">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white mb-4">
            {{ t().categories.title }}
          </h2>
          <p class="text-lg text-slate-600 dark:text-slate-300">
            {{ t().categories.subtitle }}
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
          <div class="group bg-gradient-to-br from-blue-50 to-blue-100 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all cursor-pointer border-2 border-transparent hover:border-blue-600">
            <div class="text-4xl mb-4">📊</div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
              {{ t().categories.admin.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().categories.admin.desc }}
            </p>
          </div>

          <div class="group bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all cursor-pointer border-2 border-transparent hover:border-indigo-600">
            <div class="text-4xl mb-4">🏢</div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
              {{ t().categories.company.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().categories.company.desc }}
            </p>
          </div>

          <div class="group bg-gradient-to-br from-purple-50 to-purple-100 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all cursor-pointer border-2 border-transparent hover:border-purple-600">
            <div class="text-4xl mb-4">🚀</div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
              {{ t().categories.landing.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().categories.landing.desc }}
            </p>
          </div>

          <div class="group bg-gradient-to-br from-green-50 to-green-100 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all cursor-pointer border-2 border-transparent hover:border-green-600">
            <div class="text-4xl mb-4">⚡</div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
              {{ t().categories.saas.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().categories.saas.desc }}
            </p>
          </div>

          <div class="group bg-gradient-to-br from-orange-50 to-orange-100 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all cursor-pointer border-2 border-transparent hover:border-orange-600">
            <div class="text-4xl mb-4">📝</div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
              {{ t().categories.blog.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().categories.blog.desc }}
            </p>
          </div>

          <div class="group bg-gradient-to-br from-pink-50 to-pink-100 dark:from-slate-800 dark:to-slate-700 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all cursor-pointer border-2 border-transparent hover:border-pink-600">
            <div class="text-4xl mb-4">🎨</div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
              {{ t().categories.portfolio.title }}
            </h3>
            <p class="text-slate-600 dark:text-slate-300">
              {{ t().categories.portfolio.desc }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-blue-600 to-indigo-700">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-3xl mx-auto">
          <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
            {{ t().cta.title }}
          </h2>
          <p class="text-xl text-blue-100 mb-10">
            {{ t().cta.subtitle }}
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link href="/register" class="inline-flex items-center px-8 py-4 bg-white hover:bg-blue-50 text-blue-600 rounded-lg transition-all font-semibold text-lg shadow-lg hover:shadow-xl">
              {{ t().cta.button }}
              <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </Link>
          </div>
          <p class="text-blue-100 mt-6">{{ t().cta.free }}</p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 py-12">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8 mb-8">
          <div>
            <div class="flex items-center space-x-2 mb-4">
              <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
              </div>
              <span class="text-xl font-bold text-white">Template<span class="text-blue-400">Gen</span></span>
            </div>
            <p class="text-sm text-slate-400">
              {{ currentLang === 'id' ? 'Platform wizard-driven untuk menghasilkan template frontend yang konsisten dan profesional.' : 'A wizard-driven platform for generating consistent and professional frontend templates.' }}
            </p>
          </div>

          <div>
            <h4 class="font-semibold text-white mb-4">{{ currentLang === 'id' ? 'Menu' : 'Menu' }}</h4>
            <ul class="space-y-2 text-sm">
              <li><Link href="/" class="hover:text-blue-400 transition-colors">{{ currentLang === 'id' ? 'Beranda' : 'Home' }}</Link></li>
              <li><button @click="scrollToSection('why-choose')" class="hover:text-blue-400 transition-colors">{{ currentLang === 'id' ? 'Mengapa Memilih' : 'Why Choose' }}</button></li>
              <li><button @click="scrollToSection('how-it-works')" class="hover:text-blue-400 transition-colors">{{ currentLang === 'id' ? 'Cara Kerja' : 'How It Works' }}</button></li>
              <li><button @click="scrollToSection('categories')" class="hover:text-blue-400 transition-colors">{{ currentLang === 'id' ? 'Kategori' : 'Categories' }}</button></li>
            </ul>
          </div>

          <div>
            <h4 class="font-semibold text-white mb-4">{{ t().footer.product }}</h4>
            <ul class="space-y-2 text-sm">
              <li><Link href="/wizard" class="hover:text-blue-400 transition-colors">{{ currentLang === 'id' ? 'Buat Template' : 'Create Template' }}</Link></li>
              <li><Link href="/register" class="hover:text-blue-400 transition-colors">{{ currentLang === 'id' ? 'Daftar' : 'Register' }}</Link></li>
            </ul>
          </div>
        </div>

        <div class="border-t border-slate-800 pt-8 text-center text-sm text-slate-400">
          <p>&copy; {{ new Date().getFullYear() }} TemplateGen. {{ t().footer.rights }}</p>
        </div>
      </div>
    </footer>
  </div>
</template>
