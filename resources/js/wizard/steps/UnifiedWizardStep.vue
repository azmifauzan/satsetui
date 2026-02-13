<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { wizardState, type Category } from '../wizardState';
import { useI18n } from '@/lib/i18n';

const { currentLang } = useI18n();

// ========== SECTION 1: What to Build ==========
const categories = [
  { 
    id: 'landing-page', 
    icon: 'M13 10V3L4 14h7v7l9-11h-7z',
    labelEn: 'Landing Page',
    labelId: 'Landing Page',
    descEn: 'Modern landing page with hero, features, testimonials, CTA sections',
    descId: 'Landing page modern dengan hero, fitur, testimonial, CTA',
    color: 'from-orange-500 to-red-500',
  },
  { 
    id: 'company-profile', 
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    labelEn: 'Company Profile',
    labelId: 'Profil Perusahaan',
    descEn: 'Professional company website with about, services, team, contact',
    descId: 'Website perusahaan profesional dengan tentang, layanan, tim, kontak',
    color: 'from-purple-500 to-pink-500',
  },
  { 
    id: 'mobile-apps', 
    icon: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
    labelEn: 'Mobile App UI',
    labelId: 'UI Aplikasi Mobile',
    descEn: 'Mobile-first responsive app interface with navigation and screens',
    descId: 'Interface aplikasi responsif mobile-first dengan navigasi dan layar',
    color: 'from-cyan-500 to-blue-500',
  },
  { 
    id: 'e-commerce', 
    icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
    labelEn: 'E-Commerce',
    labelId: 'E-Commerce',
    descEn: 'Online store with product listing, cart, checkout, and user account',
    descId: 'Toko online dengan listing produk, keranjang, checkout, dan akun',
    color: 'from-green-500 to-teal-500',
  },
  { 
    id: 'dashboard', 
    icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    labelEn: 'Dashboard',
    labelId: 'Dashboard',
    descEn: 'Admin dashboard with charts, tables, stats cards, and sidebar navigation',
    descId: 'Dashboard admin dengan grafik, tabel, kartu statistik, dan navigasi sidebar',
    color: 'from-blue-500 to-indigo-500',
  },
];

// Default pages per category
const categoryDefaultPages: Record<string, { id: string; labelEn: string; labelId: string }[]> = {
  'landing-page': [
    { id: 'home', labelEn: 'Home', labelId: 'Beranda' },
    { id: 'about', labelEn: 'About', labelId: 'Tentang' },
    { id: 'features', labelEn: 'Features', labelId: 'Fitur' },
    { id: 'pricing', labelEn: 'Pricing', labelId: 'Harga' },
    { id: 'contact', labelEn: 'Contact', labelId: 'Kontak' },
  ],
  'company-profile': [
    { id: 'home', labelEn: 'Home', labelId: 'Beranda' },
    { id: 'about', labelEn: 'About Us', labelId: 'Tentang Kami' },
    { id: 'services', labelEn: 'Services', labelId: 'Layanan' },
    { id: 'team', labelEn: 'Team', labelId: 'Tim' },
    { id: 'contact', labelEn: 'Contact', labelId: 'Kontak' },
  ],
  'mobile-apps': [
    { id: 'login', labelEn: 'Login', labelId: 'Login' },
    { id: 'home', labelEn: 'Home', labelId: 'Beranda' },
    { id: 'profile', labelEn: 'Profile', labelId: 'Profil' },
    { id: 'settings', labelEn: 'Settings', labelId: 'Pengaturan' },
  ],
  'e-commerce': [
    { id: 'home', labelEn: 'Home', labelId: 'Beranda' },
    { id: 'products', labelEn: 'Products', labelId: 'Produk' },
    { id: 'product-detail', labelEn: 'Product Detail', labelId: 'Detail Produk' },
    { id: 'cart', labelEn: 'Cart', labelId: 'Keranjang' },
    { id: 'checkout', labelEn: 'Checkout', labelId: 'Checkout' },
    { id: 'login', labelEn: 'Login', labelId: 'Login' },
  ],
  'dashboard': [
    { id: 'login', labelEn: 'Login', labelId: 'Login' },
    { id: 'dashboard', labelEn: 'Dashboard', labelId: 'Dashboard' },
    { id: 'tables', labelEn: 'Data Tables', labelId: 'Tabel Data' },
    { id: 'charts', labelEn: 'Charts', labelId: 'Grafik' },
    { id: 'settings', labelEn: 'Settings', labelId: 'Pengaturan' },
  ],
};

const currentDefaultPages = computed(() => categoryDefaultPages[wizardState.category] || []);

const selectCategory = (id: string) => {
  wizardState.category = id as Category;
};

// Watch category changes -> auto-select default pages
watch(() => wizardState.category, (newCat) => {
  const defaults = categoryDefaultPages[newCat];
  if (defaults) {
    wizardState.pages = defaults.map(p => p.id);
  } else {
    wizardState.pages = [];
  }
});

// Page selection toggles
const isPageSelected = (id: string) => wizardState.pages.includes(id);
const togglePage = (id: string) => {
  const idx = wizardState.pages.indexOf(id);
  if (idx > -1) {
    wizardState.pages.splice(idx, 1);
  } else {
    wizardState.pages.push(id);
  }
};

// Custom pages
const customPageName = ref('');
const addCustomPageHandler = () => {
  const name = customPageName.value.trim();
  if (name && name.length >= 2) {
    wizardState.pages.push(`custom:${name}`);
    customPageName.value = '';
  }
};
const removeCustomPage = (page: string) => {
  const idx = wizardState.pages.indexOf(page);
  if (idx > -1) wizardState.pages.splice(idx, 1);
};

// ========== Category-Specific Inputs ==========
const companyName = ref('');
const companyDescription = ref('');
const appName = ref('');
const storeName = ref('');
const storeDescription = ref('');

// ========== SECTION 2: Theme & Colors ==========
const fontOptions = [
  { id: 'inter', name: 'Inter', preview: 'font-sans' },
  { id: 'poppins', name: 'Poppins', preview: 'font-sans' },
  { id: 'roboto', name: 'Roboto', preview: 'font-sans' },
  { id: 'playfair', name: 'Playfair Display', preview: 'font-serif' },
  { id: 'mono', name: 'JetBrains Mono', preview: 'font-mono' },
];

const stylePresets = [
  { id: 'modern', labelEn: 'Modern & Clean', labelId: 'Modern & Bersih', icon: '✨' },
  { id: 'minimal', labelEn: 'Minimalist', labelId: 'Minimalis', icon: '🎯' },
  { id: 'bold', labelEn: 'Bold & Vibrant', labelId: 'Tegas & Cerah', icon: '🎨' },
  { id: 'elegant', labelEn: 'Elegant', labelId: 'Elegan', icon: '💎' },
  { id: 'playful', labelEn: 'Playful', labelId: 'Ceria', icon: '🌈' },
];

const colorPresets = [
  { id: 'blue', name: 'Ocean Blue', primary: '#3B82F6', secondary: '#6366F1' },
  { id: 'green', name: 'Forest Green', primary: '#10B981', secondary: '#059669' },
  { id: 'purple', name: 'Royal Purple', primary: '#8B5CF6', secondary: '#7C3AED' },
  { id: 'red', name: 'Ruby Red', primary: '#EF4444', secondary: '#DC2626' },
  { id: 'amber', name: 'Warm Amber', primary: '#F59E0B', secondary: '#D97706' },
  { id: 'slate', name: 'Slate Gray', primary: '#64748B', secondary: '#475569' },
];

// Custom color support
const useCustomColor = ref(false);
const customPrimaryColor = ref('#3B82F6');
const customSecondaryColor = ref('#6366F1');

watch([useCustomColor, customPrimaryColor, customSecondaryColor], () => {
  if (useCustomColor.value) {
    wizardState.colorScheme = 'custom';
    wizardState.theme.primary = customPrimaryColor.value;
    wizardState.theme.secondary = customSecondaryColor.value;
  }
});

// Custom style support
const useCustomStyle = ref(false);
const customStyleName = ref('');

watch([useCustomStyle, customStyleName], () => {
  if (useCustomStyle.value && customStyleName.value.trim()) {
    wizardState.stylePreset = 'custom';
  }
});

const selectColor = (color: typeof colorPresets[0]) => {
  useCustomColor.value = false;
  wizardState.colorScheme = color.id;
  wizardState.theme.primary = color.primary;
  wizardState.theme.secondary = color.secondary;
};

// Custom font support
const useCustomFont = ref(false);
const customFontName = ref('');

watch([useCustomFont, customFontName], () => {
  if (useCustomFont.value && customFontName.value.trim()) {
    wizardState.fontFamily = `custom:${customFontName.value.trim()}`;
  }
});

const selectFont = (fontId: string) => {
  useCustomFont.value = false;
  wizardState.fontFamily = fontId;
};

// Watch category-specific inputs + custom style/font to store in customInstructions AND projectInfo
watch([companyName, companyDescription, appName, storeName, storeDescription, useCustomStyle, customStyleName, useCustomFont, customFontName], () => {
  // Save to structured projectInfo for consistent usage across all pages
  if (wizardState.category === 'company-profile') {
    wizardState.projectInfo.companyName = companyName.value.trim();
    wizardState.projectInfo.companyDescription = companyDescription.value.trim();
  } else if (wizardState.category === 'e-commerce') {
    wizardState.projectInfo.storeName = storeName.value.trim();
    wizardState.projectInfo.storeDescription = storeDescription.value.trim();
  } else if (wizardState.category === 'mobile-apps') {
    wizardState.projectInfo.appName = appName.value.trim();
  } else if (wizardState.category === 'dashboard') {
    wizardState.projectInfo.appName = appName.value.trim();
  }

  // Also keep in customInstructions for backward compatibility
  let contextInfo = '';
  if (wizardState.category === 'company-profile') {
    if (companyName.value) contextInfo += `Company Name: ${companyName.value}. `;
    if (companyDescription.value) contextInfo += `Company Info: ${companyDescription.value}. `;
  } else if (wizardState.category === 'e-commerce') {
    if (storeName.value) contextInfo += `Store Name: ${storeName.value}. `;
    if (storeDescription.value) contextInfo += `Store Description: ${storeDescription.value}. `;
  } else if (wizardState.category === 'mobile-apps') {
    if (appName.value) contextInfo += `App Name: ${appName.value}. `;
  } else if (wizardState.category === 'dashboard') {
    if (appName.value) contextInfo += `Dashboard Name: ${appName.value}. `;
  }
  // Add custom style info
  if (useCustomStyle.value && customStyleName.value.trim()) {
    contextInfo += `Custom Style: ${customStyleName.value}. `;
  }
  // Add custom font info
  if (useCustomFont.value && customFontName.value.trim()) {
    contextInfo += `Custom Font: ${customFontName.value}. `;
  }
  // Store context info (merge with custom instructions if any)
  const baseInstructions = wizardState.customInstructions.replace(/\[AUTO_CONTEXT\].*?\[\/AUTO_CONTEXT\]/g, '').trim();
  wizardState.customInstructions = contextInfo ? `[AUTO_CONTEXT]${contextInfo}[/AUTO_CONTEXT] ${baseInstructions}` : baseInstructions;
});

const navStyles = [
  { id: 'top', labelEn: 'Top Navigation', labelId: 'Navigasi Atas', icon: '▬' },
  { id: 'sidebar', labelEn: 'Sidebar Navigation', labelId: 'Navigasi Samping', icon: '▮' },
  { id: 'both', labelEn: 'Top + Sidebar', labelId: 'Atas + Samping', icon: '▣' },
];

// Sync navStyle with layout.navigation
watch(() => wizardState.navStyle, (newNavStyle) => {
  // Map navStyle to layout.navigation
  const navMapping: Record<string, 'topbar' | 'sidebar' | 'hybrid'> = {
    'top': 'topbar',
    'sidebar': 'sidebar',
    'both': 'hybrid',
  };
  
  if (newNavStyle && navMapping[newNavStyle]) {
    wizardState.layout.navigation = navMapping[newNavStyle];
  }
}, { immediate: true });

const themeMode = computed({
  get: () => wizardState.themeMode || 'dark',
  set: (val: string) => { wizardState.themeMode = val; }
});

const logoFile = ref<File | null>(null);
const logoPreviewUrl = ref<string | null>(null);

const handleLogoUpload = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files && input.files[0]) {
    logoFile.value = input.files[0];
    logoPreviewUrl.value = URL.createObjectURL(input.files[0]);
    wizardState.logoFile = input.files[0];
  }
};

const removeLogo = () => {
  logoFile.value = null;
  logoPreviewUrl.value = null;
  wizardState.logoFile = null;
};

// ========== SECTION 3: Custom Modifications (Optional) ==========
const availableComponents = [
  { id: 'hero', labelEn: 'Hero Section', labelId: 'Bagian Hero', icon: '🏠' },
  { id: 'features', labelEn: 'Features Grid', labelId: 'Grid Fitur', icon: '⚡' },
  { id: 'testimonials', labelEn: 'Testimonials', labelId: 'Testimonial', icon: '💬' },
  { id: 'pricing', labelEn: 'Pricing Table', labelId: 'Tabel Harga', icon: '💰' },
  { id: 'contact', labelEn: 'Contact Form', labelId: 'Form Kontak', icon: '📧' },
  { id: 'gallery', labelEn: 'Image Gallery', labelId: 'Galeri Gambar', icon: '🖼️' },
  { id: 'charts', labelEn: 'Charts & Graphs', labelId: 'Grafik & Chart', icon: '📊' },
  { id: 'tables', labelEn: 'Data Tables', labelId: 'Tabel Data', icon: '📋' },
  { id: 'cards', labelEn: 'Card Components', labelId: 'Komponen Kartu', icon: '🃏' },
  { id: 'modals', labelEn: 'Modal Dialogs', labelId: 'Dialog Modal', icon: '🪟' },
  { id: 'forms', labelEn: 'Advanced Forms', labelId: 'Form Lanjutan', icon: '📝' },
  { id: 'maps', labelEn: 'Map Integration', labelId: 'Integrasi Peta', icon: '🗺️' },
];

const toggleComponent = (id: string) => {
  const idx = wizardState.components.indexOf(id);
  if (idx > -1) {
    wizardState.components.splice(idx, 1);
  } else {
    wizardState.components.push(id);
  }
};

const isComponentSelected = (id: string) => wizardState.components.includes(id);
</script>

<template>
  <div class="space-y-10">
    <!-- ========== SECTION 1: What to Build ========== -->
    <section>
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">1</div>
        <div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">
            {{ currentLang === 'en' ? 'What do you want to build?' : 'Apa yang ingin kamu buat?' }}
          </h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ currentLang === 'en' ? 'Choose the type of project' : 'Pilih jenis proyek yang ingin dibuat' }}
          </p>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <button
          v-for="cat in categories"
          :key="cat.id"
          @click="selectCategory(cat.id)"
          :class="[
            'relative group p-5 rounded-xl border-2 text-left transition-all duration-200 hover:shadow-lg',
            wizardState.category === cat.id
              ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-md ring-2 ring-blue-500/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-300 dark:hover:border-blue-600'
          ]"
        >
          <div :class="`w-12 h-12 bg-gradient-to-br ${cat.color} rounded-lg flex items-center justify-center text-white mb-3 group-hover:scale-110 transition-transform`">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="cat.icon" />
            </svg>
          </div>
          <h3 class="font-bold text-slate-900 dark:text-white mb-1">
            {{ currentLang === 'en' ? cat.labelEn : cat.labelId }}
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            {{ currentLang === 'en' ? cat.descEn : cat.descId }}
          </p>
          <div v-if="wizardState.category === cat.id" class="absolute top-3 right-3">
            <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
        </button>
      </div>

      <!-- Category-Specific Inputs -->
      <div v-if="wizardState.category === 'company-profile'" class="mt-5 bg-purple-50 dark:bg-purple-900/10 rounded-xl p-5 border border-purple-200 dark:border-purple-800">
        <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm">
          {{ currentLang === 'en' ? 'Company Information' : 'Informasi Perusahaan' }}
        </h3>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
              {{ currentLang === 'en' ? 'Company Name' : 'Nama Perusahaan' }}
            </label>
            <input v-model="companyName" type="text" :placeholder="currentLang === 'en' ? 'e.g. Acme Corp' : 'contoh: PT Maju Jaya'"
              class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
              {{ currentLang === 'en' ? 'Brief Description' : 'Deskripsi Singkat' }}
            </label>
            <input v-model="companyDescription" type="text" :placeholder="currentLang === 'en' ? 'e.g. Technology consulting firm' : 'contoh: Perusahaan konsultan teknologi'"
              class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
        </div>
      </div>

      <div v-if="wizardState.category === 'e-commerce'" class="mt-5 bg-green-50 dark:bg-green-900/10 rounded-xl p-5 border border-green-200 dark:border-green-800">
        <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm">
          {{ currentLang === 'en' ? 'Store Information' : 'Informasi Toko' }}
        </h3>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
              {{ currentLang === 'en' ? 'Store Name' : 'Nama Toko' }}
            </label>
            <input v-model="storeName" type="text" :placeholder="currentLang === 'en' ? 'e.g. TechStore' : 'contoh: TokoTech'"
              class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
              {{ currentLang === 'en' ? 'What do you sell?' : 'Apa yang dijual?' }}
            </label>
            <input v-model="storeDescription" type="text" :placeholder="currentLang === 'en' ? 'e.g. Electronics and gadgets' : 'contoh: Elektronik dan gadget'"
              class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
        </div>
      </div>

      <div v-if="wizardState.category === 'mobile-apps' || wizardState.category === 'dashboard'" class="mt-5 bg-cyan-50 dark:bg-cyan-900/10 rounded-xl p-5 border border-cyan-200 dark:border-cyan-800">
        <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm">
          {{ wizardState.category === 'dashboard' 
            ? (currentLang === 'en' ? 'Dashboard Information' : 'Informasi Dashboard')
            : (currentLang === 'en' ? 'App Information' : 'Informasi Aplikasi') }}
        </h3>
        <div>
          <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
            {{ currentLang === 'en' ? 'App/Dashboard Name' : 'Nama Aplikasi/Dashboard' }}
          </label>
          <input v-model="appName" type="text" :placeholder="currentLang === 'en' ? 'e.g. MyApp' : 'contoh: AplikasiKu'"
            class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent max-w-md" />
        </div>
      </div>

      <!-- Pages Selection -->
      <div class="mt-6 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-5 border border-slate-200 dark:border-slate-700">
        <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm uppercase tracking-wide">
          {{ currentLang === 'en' ? 'Pages to Generate' : 'Halaman yang Akan Dibuat' }}
        </h3>
        <!-- Default pages for category -->
        <div class="flex flex-wrap gap-2 mb-3">
          <button
            v-for="page in currentDefaultPages"
            :key="page.id"
            @click="togglePage(page.id)"
            :class="[
              'flex items-center gap-1.5 px-3 py-1.5 rounded-lg border-2 text-sm font-medium transition-all',
              isPageSelected(page.id)
                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:border-blue-300'
            ]"
          >
            <svg v-if="isPageSelected(page.id)" class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            {{ currentLang === 'en' ? page.labelEn : page.labelId }}
          </button>
        </div>
        <!-- Custom pages added -->
        <div v-if="wizardState.pages.filter(p => p.startsWith('custom:')).length" class="flex flex-wrap gap-2 mb-3">
          <span
            v-for="page in wizardState.pages.filter(p => p.startsWith('custom:'))"
            :key="page"
            class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-300 dark:border-indigo-700 text-sm text-indigo-700 dark:text-indigo-300"
          >
            {{ page.replace('custom:', '') }}
            <button @click="removeCustomPage(page)" class="ml-1 text-indigo-400 hover:text-red-500">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </span>
        </div>
        <!-- Add custom page -->
        <div class="flex items-center gap-2">
          <input
            v-model="customPageName"
            type="text"
            :placeholder="currentLang === 'en' ? 'Add custom page...' : 'Tambah halaman kustom...'"
            class="flex-1 max-w-xs px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @keyup.enter="addCustomPageHandler"
          />
          <button @click="addCustomPageHandler" :disabled="!customPageName.trim() || customPageName.trim().length < 2"
            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:dark:bg-slate-700 text-white text-sm rounded-lg transition-colors">
            +
          </button>
        </div>
      </div>
    </section>

    <!-- ========== SECTION 2: Theme & Colors ========== -->
    <section>
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">2</div>
        <div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">
            {{ currentLang === 'en' ? 'Theme & Style' : 'Tema & Gaya' }}
          </h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ currentLang === 'en' ? 'Customize the look and feel' : 'Sesuaikan tampilan dan nuansa' }}
          </p>
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Color Scheme -->
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-5 border border-slate-200 dark:border-slate-700">
          <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm uppercase tracking-wide">
            {{ currentLang === 'en' ? 'Color Scheme' : 'Skema Warna' }}
          </h3>
          <div class="grid grid-cols-3 gap-2 mb-3">
            <button
              v-for="color in colorPresets"
              :key="color.id"
              @click="selectColor(color)"
              :class="[
                'flex flex-col items-center p-3 rounded-lg border-2 transition-all',
                wizardState.colorScheme === color.id && !useCustomColor
                  ? 'border-blue-500 bg-white dark:bg-slate-800 shadow-sm'
                  : 'border-transparent hover:border-slate-300 dark:hover:border-slate-600'
              ]"
            >
              <div class="flex gap-1 mb-2">
                <div class="w-5 h-5 rounded-full" :style="{ backgroundColor: color.primary }"></div>
                <div class="w-5 h-5 rounded-full" :style="{ backgroundColor: color.secondary }"></div>
              </div>
              <span class="text-xs text-slate-600 dark:text-slate-400 text-center">{{ color.name }}</span>
            </button>
          </div>
          <!-- Custom color picker -->
          <div class="border-t border-slate-200 dark:border-slate-700 pt-3">
            <button
              @click="useCustomColor = !useCustomColor"
              :class="[
                'flex items-center gap-2 text-sm font-medium transition-colors',
                useCustomColor ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400 hover:text-blue-600'
              ]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
              {{ currentLang === 'en' ? 'Custom Color' : 'Warna Kustom' }}
            </button>
            <div v-if="useCustomColor" class="flex items-center gap-3 mt-2">
              <label class="flex items-center gap-1.5">
                <span class="text-xs text-slate-500">Primary</span>
                <input type="color" v-model="customPrimaryColor" class="w-8 h-8 rounded cursor-pointer border-0" />
              </label>
              <label class="flex items-center gap-1.5">
                <span class="text-xs text-slate-500">Secondary</span>
                <input type="color" v-model="customSecondaryColor" class="w-8 h-8 rounded cursor-pointer border-0" />
              </label>
            </div>
          </div>
        </div>

        <!-- Style Preset -->
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-5 border border-slate-200 dark:border-slate-700">
          <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm uppercase tracking-wide">
            {{ currentLang === 'en' ? 'Style' : 'Gaya' }}
          </h3>
          <div class="grid grid-cols-2 gap-2">
            <button
              v-for="style in stylePresets"
              :key="style.id"
              @click="() => { wizardState.stylePreset = style.id; useCustomStyle = false; }"
              :class="[
                'flex items-center gap-2 p-3 rounded-lg border-2 text-left transition-all',
                wizardState.stylePreset === style.id && !useCustomStyle
                  ? 'border-blue-500 bg-white dark:bg-slate-800 shadow-sm'
                  : 'border-transparent hover:border-slate-300 dark:hover:border-slate-600'
              ]"
            >
              <span class="text-lg">{{ style.icon }}</span>
              <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">
                {{ currentLang === 'en' ? style.labelEn : style.labelId }}
              </span>
            </button>
          </div>
          <!-- Custom Style -->
          <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700">
            <label class="flex items-center gap-2 cursor-pointer mb-2">
              <input type="checkbox" v-model="useCustomStyle" class="rounded" />
              <span class="text-xs font-medium text-slate-700 dark:text-slate-300">
                🎨 {{ currentLang === 'en' ? 'Custom Style' : 'Gaya Kustom' }}
              </span>
            </label>
            <input
              v-if="useCustomStyle"
              v-model="customStyleName"
              type="text"
              :placeholder="currentLang === 'en' ? 'e.g., Corporate, Futuristic, Retro...' : 'mis., Korporat, Futuristik, Retro...'"
              class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <!-- Font -->
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-5 border border-slate-200 dark:border-slate-700">
          <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm uppercase tracking-wide">
            Font
          </h3>
          <div class="space-y-2 mb-3">
            <button
              v-for="font in fontOptions"
              :key="font.id"
              @click="selectFont(font.id)"
              :class="[
                'w-full flex items-center justify-between p-3 rounded-lg border-2 transition-all',
                wizardState.fontFamily === font.id && !useCustomFont
                  ? 'border-blue-500 bg-white dark:bg-slate-800 shadow-sm'
                  : 'border-transparent hover:border-slate-300 dark:hover:border-slate-600'
              ]"
            >
              <span :class="[font.preview, 'text-sm text-slate-700 dark:text-slate-300 font-medium']">{{ font.name }}</span>
              <span :class="[font.preview, 'text-xs text-slate-400']">Aa Bb Cc 123</span>
            </button>
          </div>
          <!-- Custom font -->
          <div class="border-t border-slate-200 dark:border-slate-700 pt-3">
            <button
              @click="useCustomFont = !useCustomFont"
              :class="[
                'flex items-center gap-2 text-sm font-medium transition-colors',
                useCustomFont ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400 hover:text-blue-600'
              ]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              {{ currentLang === 'en' ? 'Custom Font' : 'Font Kustom' }}
            </button>
            <div v-if="useCustomFont" class="mt-2">
              <input v-model="customFontName" type="text" :placeholder="currentLang === 'en' ? 'e.g. Lato, Open Sans' : 'contoh: Lato, Open Sans'"
                class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
          </div>
        </div>

        <!-- Navigation Style -->
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-5 border border-slate-200 dark:border-slate-700">
          <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm uppercase tracking-wide">
            {{ currentLang === 'en' ? 'Navigation' : 'Navigasi' }}
          </h3>
          <div class="space-y-2">
            <button
              v-for="nav in navStyles"
              :key="nav.id"
              @click="wizardState.navStyle = nav.id"
              :class="[
                'w-full flex items-center gap-3 p-3 rounded-lg border-2 transition-all text-left',
                wizardState.navStyle === nav.id
                  ? 'border-blue-500 bg-white dark:bg-slate-800 shadow-sm'
                  : 'border-transparent hover:border-slate-300 dark:hover:border-slate-600'
              ]"
            >
              <span class="text-lg opacity-60">{{ nav.icon }}</span>
              <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">
                {{ currentLang === 'en' ? nav.labelEn : nav.labelId }}
              </span>
            </button>
          </div>

          <!-- Theme Mode -->
          <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wide">
              {{ currentLang === 'en' ? 'Theme Mode' : 'Mode Tema' }}
            </h4>
            <div class="flex gap-2">
              <button
                @click="themeMode = 'light'"
                :class="[
                  'flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all border-2',
                  themeMode === 'light'
                    ? 'border-blue-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm'
                    : 'border-transparent text-slate-500 hover:border-slate-300 dark:hover:border-slate-600'
                ]"
              >Light</button>
              <button
                @click="themeMode = 'dark'"
                :class="[
                  'flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all border-2',
                  themeMode === 'dark'
                    ? 'border-blue-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm'
                    : 'border-transparent text-slate-500 hover:border-slate-300 dark:hover:border-slate-600'
                ]"
              >Dark</button>
              <button
                @click="themeMode = 'both'"
                :class="[
                  'flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all border-2',
                  themeMode === 'both'
                    ? 'border-blue-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm'
                    : 'border-transparent text-slate-500 hover:border-slate-300 dark:hover:border-slate-600'
                ]"
              >Both</button>
            </div>
          </div>
        </div>

        <!-- Logo Upload -->
        <div class="lg:col-span-2 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-5 border border-slate-200 dark:border-slate-700">
          <h3 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm uppercase tracking-wide">
            {{ currentLang === 'en' ? 'Logo (Optional)' : 'Logo (Opsional)' }}
          </h3>
          <div class="flex items-center gap-4">
            <div v-if="logoPreviewUrl" class="relative">
              <img :src="logoPreviewUrl" alt="Logo preview" class="w-16 h-16 object-contain rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-1" />
              <button @click="removeLogo" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">
                ✕
              </button>
            </div>
            <label class="flex-1 cursor-pointer">
              <div class="flex items-center justify-center py-4 px-6 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg hover:border-blue-400 dark:hover:border-blue-500 transition-colors bg-white dark:bg-slate-800">
                <div class="text-center">
                  <svg class="w-8 h-8 text-slate-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span class="text-sm text-slate-500 dark:text-slate-400">
                    {{ currentLang === 'en' ? 'Click to upload logo' : 'Klik untuk upload logo' }}
                  </span>
                  <span class="text-xs text-slate-400 block mt-1">PNG, JPG, SVG (max 2MB)</span>
                </div>
              </div>
              <input type="file" class="hidden" accept="image/*" @change="handleLogoUpload" />
            </label>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== SECTION 3: Custom Modifications (Optional) ========== -->
    <section>
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-teal-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">3</div>
        <div>
          <h2 class="text-xl font-bold text-slate-900 dark:text-white">
            {{ currentLang === 'en' ? 'Custom Modifications' : 'Modifikasi Kustom' }}
            <span class="text-sm font-normal text-slate-400 ml-2">({{ currentLang === 'en' ? 'Optional' : 'Opsional' }})</span>
          </h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ currentLang === 'en' ? 'Add extra components to your project' : 'Tambahkan komponen ekstra ke proyek kamu' }}
          </p>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        <button
          v-for="comp in availableComponents"
          :key="comp.id"
          @click="toggleComponent(comp.id)"
          :class="[
            'flex items-center gap-3 p-3 rounded-lg border-2 text-left transition-all',
            isComponentSelected(comp.id)
              ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
              : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-blue-300 dark:hover:border-blue-600'
          ]"
        >
          <span class="text-lg">{{ comp.icon }}</span>
          <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ currentLang === 'en' ? comp.labelEn : comp.labelId }}
          </span>
          <svg v-if="isComponentSelected(comp.id)" class="w-4 h-4 text-blue-500 ml-auto flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>

      <!-- Custom instructions -->
      <div class="mt-4">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
          {{ currentLang === 'en' ? 'Additional instructions (optional)' : 'Instruksi tambahan (opsional)' }}
        </label>
        <textarea
          v-model="wizardState.customInstructions"
          :placeholder="currentLang === 'en' ? 'e.g., Add dark gradient backgrounds, use rounded corners heavily, include animation effects...' : 'mis., Tambahkan latar belakang gradien gelap, gunakan sudut membulat, sertakan efek animasi...'"
          rows="3"
          class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-white text-sm transition-colors resize-none"
        ></textarea>
      </div>
    </section>
  </div>
</template>
