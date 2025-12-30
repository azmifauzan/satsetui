/**
 * Internationalization (i18n) System
 * 
 * Bilingual support for Indonesian (id) and English (en).
 * DEFAULT LANGUAGE: Indonesian (id)
 * All user-facing strings must be translated.
 * 
 * Usage:
 * import { useI18n } from '@/lib/i18n';
 * const { t, currentLang, setLang } = useI18n();
 */

import { ref, computed } from 'vue';
import type { ComputedRef } from 'vue';

export type Language = 'id' | 'en';

// Initialize language from localStorage or default to Indonesian
function getInitialLanguage(): Language {
  if (typeof window !== 'undefined') {
    const saved = localStorage.getItem('app-language') as Language;
    if (saved && (saved === 'id' || saved === 'en')) {
      return saved;
    }
  }
  return 'id'; // Default to Indonesian
}

// Global reactive language state - defaults to Indonesian
const currentLanguage = ref<Language>(getInitialLanguage());

// Translation keys type
export interface Translations {
  // Common
  common: {
    save: string;
    cancel: string;
    delete: string;
    edit: string;
    create: string;
    update: string;
    close: string;
    back: string;
    next: string;
    previous: string;
    submit: string;
    loading: string;
    success: string;
    error: string;
    warning: string;
    info: string;
    confirm: string;
    yes: string;
    no: string;
    search: string;
    filter: string;
    reset: string;
    credits: string;
    of: string;
  };

  // Navigation
  nav: {
    dashboard: string;
    wizard: string;
    projects: string;
    templates: string;
    profile: string;
    settings: string;
    logout: string;
  };

  // Wizard
  wizard: {
    title: string;
    description: string;
    stepOf: string;
    stepDescriptions: {
      step1: string;
      step2: string;
      step3: string;
    };
    stepTitles: {
      step1: string;
      step2: string;
      step3: string;
    };
    generateTemplate: string;
    generating: string;
    generatingDescription: string;
    startingGeneration: string;
    generatingPage: string;
    pageOf: string;
    generationFailed: string;
    blueprintPreview: string;
    showBlueprint: string;
    hideBlueprint: string;
    wizardFirst: string;
    wizardFirstDescription: string;
    
    // Steps
    steps: {
      framework: {
        title: string;
        description: string;
        tailwind: string;
        tailwindDesc: string;
        bootstrap: string;
        bootstrapDesc: string;
        pureCss: string;
        pureCssDesc: string;
      };
      category: {
        title: string;
        description: string;
        adminLabel: string;
        adminDesc: string;
        companyLabel: string;
        companyDesc: string;
        landingLabel: string;
        landingDesc: string;
        saasLabel: string;
        saasDesc: string;
        blogLabel: string;
        blogDesc: string;
        ecommerceLabel: string;
        ecommerceDesc: string;
        adminDashboard: string;
        adminDashboardDesc: string;
        companyProfile: string;
        companyProfileDesc: string;
        landingPage: string;
        landingPageDesc: string;
        saasApplication: string;
        saasApplicationDesc: string;
        blogContentSite: string;
        blogContentSiteDesc: string;
        eCommerce: string;
        eCommerceDesc: string;
        // Custom category
        custom: string;
        customDesc: string;
        customInputTitle: string;
        customNameLabel: string;
        customNamePlaceholder: string;
        customDescLabel: string;
        customDescPlaceholder: string;
        customMinChars: string;
      };
      pages: {
        title: string;
        description: string;
        useSuggested: string;
        suggested: string;
        selectAtLeastOne: string;
        selectedCount: string;
        authCategory: string;
        appCategory: string;
        publicCategory: string;
        authentication: string;
        application: string;
        public: string;
        login: string;
        register: string;
        forgotPassword: string;
        dashboard: string;
        userManagement: string;
        settings: string;
        charts: string;
        tables: string;
        profile: string;
        about: string;
        contact: string;
        // Custom pages
        customPagesTitle: string;
        customPagesDesc: string;
        addCustomPage: string;
        customPageName: string;
        customPageNamePlaceholder: string;
        customPageDesc: string;
        customPageDescPlaceholder: string;
        customPageMinChars: string;
        extraPagesNote: string;
      };
      layout: {
        title: string;
        description: string;
        navigationStyle: string;
        sidebar: string;
        sidebarDesc: string;
        topbar: string;
        topbarDesc: string;
        hybrid: string;
        hybridDesc: string;
        sidebarDefaultState: string;
        expanded: string;
        collapsed: string;
        additionalElements: string;
        enableBreadcrumbs: string;
        breadcrumbsDesc: string;
        footerStyle: string;
        minimal: string;
        minimalFooter: string;
        fullFooter: string;
        // Custom navigation items
        customNavTitle: string;
        customNavDesc: string;
        addCustomNav: string;
        customNavLabel: string;
        customNavLabelPlaceholder: string;
        customNavRoute: string;
        customNavRoutePlaceholder: string;
        customNavIcon: string;
        customNavIconPlaceholder: string;
        customNavMinChars: string;
      };
      theme: {
        title: string;
        description: string;
        primaryColor: string;
        secondaryColor: string;
        colorMode: string;
        lightMode: string;
        darkMode: string;
        backgroundStyle: string;
        solid: string;
        gradient: string;
        preview: string;
        compact: string;
        compactDesc: string;
        comfortable: string;
        comfortableDesc: string;
        spacious: string;
        spaciousDesc: string;
        sharp: string;
        sharpDesc: string;
        rounded: string;
        roundedDesc: string;
        primary: string;
        secondary: string;
      };
      uiDensity: {
        title: string;
        description: string;
        density: string;
        compact: string;
        compactDesc: string;
        comfortable: string;
        comfortableDesc: string;
        spacious: string;
        spaciousDesc: string;
        borderRadius: string;
        sharp: string;
        sharpDesc: string;
        rounded: string;
        roundedDesc: string;
        preview: string;
        buttonExample: string;
        cardContent: string;
      };
      components: {
        title: string;
        description: string;
        selectAtLeastOne: string;
        selectedCount: string;
        chartLibraryRequired: string;
        buttons: string;
        buttonsDesc: string;
        forms: string;
        formsDesc: string;
        modals: string;
        modalsDesc: string;
        dropdowns: string;
        dropdownsDesc: string;
        alerts: string;
        alertsDesc: string;
        cards: string;
        cardsDesc: string;
        tabs: string;
        tabsDesc: string;
        charts: string;
        chartsDesc: string;
        chartLibrary: string;
        chartjs: string;
        chartjsDesc: string;
        echarts: string;
        echartsDesc: string;
        // Custom components
        customComponentsTitle: string;
        customComponentsDesc: string;
        addCustomComponent: string;
        customComponentName: string;
        customComponentNamePlaceholder: string;
        customComponentDescLabel: string;
        customComponentDescPlaceholder: string;
        customComponentMinChars: string;
        extraComponentsNote: string;
        componentPagesNote: string;
        componentsLabel: string;
      };
      interaction: {
        title: string;
        description: string;
        static: string;
        staticDesc: string;
        staticExample: string;
        moderate: string;
        moderateDesc: string;
        moderateExample: string;
        rich: string;
        richDesc: string;
        richExample: string;
      };
      responsiveness: {
        title: string;
        description: string;
        desktopFirst: string;
        desktopFirstDesc: string;
        desktopFirstBest: string;
        mobileFirst: string;
        mobileFirstDesc: string;
        mobileFirstBest: string;
        fullyResponsive: string;
        fullyResponsiveDesc: string;
        fullyResponsiveBest: string;
        bestFor: string;
      };
      outputFormat: {
        title: string;
        description: string;
        htmlCss: string;
        htmlCssDesc: string;
        react: string;
        reactDesc: string;
        vue: string;
        vueDesc: string;
        angular: string;
        angularDesc: string;
        svelte: string;
        svelteDesc: string;
        // Custom output format
        custom: string;
        customDesc: string;
        customInputTitle: string;
        customPlaceholder: string;
        customHint: string;
      };
      llmModel: {
        title: string;
        description: string;
        credits: string;
        freeModels: string;
        premiumModels: string;
        geminiFlash: string;
        geminiFlashDesc: string;
        geminiPro: string;
        geminiProDesc: string;
        gpt4: string;
        gpt4Desc: string;
        claude3: string;
        claude3Desc: string;
        noCreditTitle: string;
        noCreditDesc: string;
        ready: string;
        readyDesc: string;
        insufficientCredits: string;
        economical: string;
        loadingModels: string;
        noModelsAvailable: string;
        allModelsLabel: string;
        selectModel: string;
        creditsRequired: string;
        // Credit breakdown
        modelCost: string;
        extraPages: string;
        extraComponents: string;
        pagesCount: string;
        componentsCount: string;
        included: string;
        totalCost: string;
      };
    };
  };

  // Auth
  auth: {
    login: string;
    register: string;
    logout: string;
    email: string;
    password: string;
    rememberMe: string;
    forgotPassword: string;
    name: string;
    confirmPassword: string;
  };

  // Theme
  theme: {
    light: string;
    dark: string;
    system: string;
  };
}

// Indonesian translations
const id: Translations = {
  common: {
    save: 'Simpan',
    cancel: 'Batal',
    delete: 'Hapus',
    edit: 'Edit',
    create: 'Buat',
    update: 'Perbarui',
    close: 'Tutup',
    back: 'Kembali',
    next: 'Selanjutnya',
    previous: 'Sebelumnya',
    submit: 'Kirim',
    loading: 'Memuat...',
    success: 'Berhasil',
    error: 'Kesalahan',
    warning: 'Peringatan',
    info: 'Informasi',
    confirm: 'Konfirmasi',
    yes: 'Ya',
    no: 'Tidak',
    search: 'Cari',
    filter: 'Filter',
    reset: 'Reset',
    credits: 'kredit',
    of: 'dari',
  },
  nav: {
    dashboard: 'Dashboard',
    wizard: 'Wizard',
    projects: 'Proyek',
    templates: 'Template',
    profile: 'Profil',
    settings: 'Pengaturan',
    logout: 'Keluar',
  },
  wizard: {
    title: 'Wizard Template',
    description: 'Buat template frontend kustom Anda dalam 3 langkah terstruktur',
    stepOf: 'Langkah',
    stepDescriptions: {
      step1: 'Pilih framework CSS, kategori template, dan format output Anda',
      step2: 'Konfigurasikan halaman, layout, tema, kepadatan UI, dan komponen',
      step3: 'Pilih model LLM untuk generasi dan tinjau kredit yang diperlukan',
    },
    stepTitles: {
      step1: 'Framework, Kategori & Output',
      step2: 'Desain Visual & Konten',
      step3: 'Seleksi Model LLM',
    },
    generateTemplate: 'Generate Template',
    generating: 'Menghasilkan Template...',
    generatingDescription: 'Membuat template kustom Anda berdasarkan pilihan Anda. Ini mungkin memakan waktu sejenak.',
    startingGeneration: 'Memulai generasi...',
    generatingPage: 'Menghasilkan halaman',
    pageOf: 'Halaman',
    generationFailed: 'Generasi Gagal',
    blueprintPreview: 'Blueprint JSON',
    showBlueprint: 'Tampilkan Blueprint',
    hideBlueprint: 'Sembunyikan Blueprint',
    wizardFirst: 'Desain Wizard-First',
    wizardFirstDescription: 'Semua konfigurasi dilakukan melalui langkah-langkah terstruktur. Tidak ada prompt bebas. Pilihan Anda bersifat deterministik dan dapat direproduksi.',
    steps: {
      framework: {
        title: 'Pilih Framework CSS',
        description: 'Pilih fondasi framework CSS untuk template Anda. Keputusan ini mempengaruhi semua struktur komponen dan pola styling selanjutnya.',
        tailwind: 'Tailwind CSS',
        tailwindDesc: 'Framework utility-first, sangat dapat disesuaikan, pendekatan modern',
        bootstrap: 'Bootstrap',
        bootstrapDesc: 'Berbasis komponen, prototyping cepat, ekosistem ekstensif',
        pureCss: 'Pure CSS',
        pureCssDesc: 'CSS vanilla, tanpa framework, kontrol penuh, ringan',
      },
      category: {
        title: 'Pilih Kategori Template',
        description: 'Pilih kasus penggunaan utama untuk template Anda. Ini akan mempengaruhi rekomendasi halaman default dan pola layout.',
        adminLabel: 'Admin Dashboard',
        adminDesc: 'Alat internal, manajemen data, operasi CRUD',
        companyLabel: 'Profil Perusahaan',
        companyDesc: 'Tampilan publik, showcase konten, halaman tentang/layanan',
        landingLabel: 'Landing Page',
        landingDesc: 'Fokus pemasaran, dioptimalkan untuk konversi, bagian hero',
        saasLabel: 'Aplikasi SaaS',
        saasDesc: 'Akun pengguna, bagian fitur, halaman harga',
        blogLabel: 'Blog / Situs Konten',
        blogDesc: 'Daftar artikel, pengalaman membaca, kategori',
        ecommerceLabel: 'E-Commerce',
        ecommerceDesc: 'Katalog produk, keranjang belanja, halaman checkout',
        adminDashboard: 'Admin Dashboard',
        adminDashboardDesc: 'Alat internal, manajemen data, operasi CRUD',
        companyProfile: 'Profil Perusahaan',
        companyProfileDesc: 'Tampilan publik, showcase konten, halaman tentang/layanan',
        landingPage: 'Landing Page',
        landingPageDesc: 'Fokus pemasaran, dioptimalkan untuk konversi, bagian hero',
        saasApplication: 'Aplikasi SaaS',
        saasApplicationDesc: 'Akun pengguna, bagian fitur, halaman harga',
        blogContentSite: 'Blog / Situs Konten',
        blogContentSiteDesc: 'Daftar artikel, pengalaman membaca, kategori',
        eCommerce: 'E-Commerce',
        eCommerceDesc: 'Katalog produk, keranjang belanja, halaman checkout',
        // Custom category
        custom: 'Kategori Kustom',
        customDesc: 'Tentukan kategori template kustom sesuai kebutuhan spesifik Anda',
        customInputTitle: 'Detail Kategori Kustom',
        customNameLabel: 'Nama Kategori',
        customNamePlaceholder: 'Contoh: Portal Kesehatan, Sistem Inventori, Platform Edukasi',
        customDescLabel: 'Deskripsi Kategori',
        customDescPlaceholder: 'Jelaskan tujuan dan fitur utama dari kategori template Anda...',
        customMinChars: 'Minimal 3 karakter',
      },
      pages: {
        title: 'Pilih Halaman',
        description: 'Pilih halaman spesifik yang akan disertakan dalam template Anda. Pilih minimal satu halaman.',
        useSuggested: 'Gunakan Halaman yang Disarankan untuk',
        suggested: 'Disarankan',
        selectAtLeastOne: 'Silakan pilih minimal satu halaman untuk melanjutkan',
        selectedCount: 'Dipilih',
        authCategory: 'Autentikasi',
        appCategory: 'Aplikasi',
        publicCategory: 'Publik',
        authentication: 'Autentikasi',
        application: 'Aplikasi',
        public: 'Publik',
        login: 'Login',
        register: 'Register',
        forgotPassword: 'Lupa Password',
        dashboard: 'Dashboard',
        userManagement: 'Manajemen Pengguna',
        settings: 'Pengaturan',
        charts: 'Charts / Analytics',
        tables: 'Tables / Data List',
        profile: 'Profil',
        about: 'Tentang',
        contact: 'Kontak',
        // Custom pages
        customPagesTitle: 'Halaman Kustom',
        customPagesDesc: 'Tambahkan halaman khusus yang tidak ada di pilihan standar',
        addCustomPage: 'Tambah Halaman Kustom',
        customPageName: 'Nama Halaman',
        customPageNamePlaceholder: 'Contoh: Inventory, Reports, Analytics Dashboard',
        customPageDesc: 'Deskripsi Halaman',
        customPageDescPlaceholder: 'Jelaskan fungsi dan konten dari halaman ini...',
        customPageMinChars: 'Minimal 2 karakter',
        extraPagesNote: 'Halaman melebihi kuota dasar akan dikenakan kredit tambahan',
      },
      layout: {
        title: 'Layout & Navigasi',
        description: 'Konfigurasi pola navigasi struktural dan elemen layout untuk template Anda.',
        navigationStyle: 'Gaya Navigasi',
        sidebar: 'Sidebar',
        sidebarDesc: 'Menu vertikal, ideal untuk banyak item',
        topbar: 'Navigasi Atas',
        topbarDesc: 'Menu horizontal, bersih',
        hybrid: 'Hybrid (Sidebar + Topbar)',
        hybridDesc: 'Yang terbaik dari keduanya',
        sidebarDefaultState: 'Status Default Sidebar',
        expanded: 'Diperluas secara Default',
        collapsed: 'Diciutkan secara Default',
        additionalElements: 'Elemen Tambahan',
        enableBreadcrumbs: 'Aktifkan Breadcrumbs',
        breadcrumbsDesc: 'Tampilkan breadcrumbs navigasi pada halaman',
        footerStyle: 'Gaya Footer',
        minimal: 'Minimal (hanya copyright)',
        minimalFooter: 'Minimal (hanya copyright)',
        fullFooter: 'Lengkap (dengan link)',
        // Custom navigation items
        customNavTitle: 'Item Navigasi Kustom',
        customNavDesc: 'Tambahkan item menu navigasi khusus',
        addCustomNav: 'Tambah Item Navigasi',
        customNavLabel: 'Label Menu',
        customNavLabelPlaceholder: 'Contoh: Reports, Analytics, Inventory',
        customNavRoute: 'Route/URL',
        customNavRoutePlaceholder: 'Contoh: /reports, /analytics',
        customNavIcon: 'Icon (opsional)',
        customNavIconPlaceholder: 'Contoh: chart-bar, document, folder',
        customNavMinChars: 'Minimal 2 karakter',
      },
      theme: {
        title: 'Tema & Identitas Visual',
        description: 'Tentukan skema warna dan preferensi mode visual Anda.',
        primaryColor: 'Warna Utama',
        secondaryColor: 'Warna Sekunder',
        colorMode: 'Mode Warna',
        lightMode: 'Mode Terang',
        darkMode: 'Mode Gelap',
        backgroundStyle: 'Gaya Background',
        solid: 'Solid',
        gradient: 'Gradien Halus',
        preview: 'Pratinjau',
        compact: 'Kompak',
        compactDesc: 'Spasi ketat, font kecil, padat data',
        comfortable: 'Nyaman',
        comfortableDesc: 'Spasi seimbang, mudah dibaca',
        spacious: 'Luas',
        spaciousDesc: 'Whitespace murah hati, target sentuh besar',
        sharp: 'Tajam',
        sharpDesc: 'Radius 0-2px, modern/teknis',
        rounded: 'Membulat',
        roundedDesc: 'Radius 4-8px, ramah/mudah didekati',
        primary: 'Utama',
        secondary: 'Sekunder',
      },
      uiDensity: {
        title: 'Kepadatan & Gaya UI',
        description: 'Kontrol spasi, ukuran, dan bobot visual template Anda.',
        density: 'Kepadatan',
        compact: 'Kompak',
        compactDesc: 'Spasi ketat, font kecil, padat data',
        comfortable: 'Nyaman',
        comfortableDesc: 'Spasi seimbang, mudah dibaca',
        spacious: 'Luas',
        spaciousDesc: 'Whitespace murah hati, target sentuh besar',
        borderRadius: 'Border Radius',
        sharp: 'Tajam',
        sharpDesc: 'Radius 0-2px, estetika modern/teknis',
        rounded: 'Membulat',
        roundedDesc: 'Radius 4-8px, ramah/mudah didekati',
        preview: 'Pratinjau',
        buttonExample: 'Contoh Tombol',
        cardContent: 'Konten kartu dengan pengaturan kepadatan dan border saat ini',
      },
      components: {
        title: 'Komponen',
        description: 'Pilih komponen UI yang akan disertakan dalam template Anda. Pilih minimal satu komponen.',
        selectAtLeastOne: 'Silakan pilih minimal satu komponen untuk melanjutkan',
        selectedCount: 'Dipilih',
        chartLibraryRequired: 'Silakan pilih library chart karena Anda telah memilih untuk menyertakan charts',
        buttons: 'Buttons',
        buttonsDesc: 'Primary, Secondary, Outline, Icon buttons',
        forms: 'Forms',
        formsDesc: 'Text input, Select, Checkbox, Radio, Textarea',
        modals: 'Modals',
        modalsDesc: 'Dialog box, prompt konfirmasi',
        dropdowns: 'Dropdowns',
        dropdownsDesc: 'Menu dropdown, alternatif select',
        alerts: 'Alerts / Toasts',
        alertsDesc: 'Notifikasi success, error, warning, info',
        cards: 'Cards',
        cardsDesc: 'Container konten dengan header/body/footer',
        tabs: 'Tabs',
        tabsDesc: 'Navigasi tab horizontal/vertikal',
        charts: 'Charts',
        chartsDesc: 'Visualisasi data (memerlukan pilihan library)',
        chartLibrary: 'Library Chart',
        chartjs: 'Chart.js',
        chartjsDesc: 'Sederhana, fleksibel, populer',
        echarts: 'Apache ECharts',
        echartsDesc: 'Powerful, kaya fitur, tingkat enterprise',
        // Custom components
        customComponentsTitle: 'Komponen Kustom',
        customComponentsDesc: 'Tambahkan komponen UI khusus yang tidak ada di pilihan standar',
        addCustomComponent: 'Tambah Komponen Kustom',
        customComponentName: 'Nama Komponen',
        customComponentNamePlaceholder: 'Contoh: Kanban Board, Calendar, File Upload',
        customComponentDescLabel: 'Deskripsi Komponen',
        customComponentDescPlaceholder: 'Jelaskan fungsi dan fitur dari komponen ini...',
        customComponentMinChars: 'Minimal 2 karakter',
        extraComponentsNote: 'Komponen melebihi kuota dasar akan dikenakan kredit tambahan',
        componentPagesNote: 'Setiap komponen = 1 halaman showcase',
        componentsLabel: 'komponen',
        componentPagesNote: 'Setiap komponen = 1 halaman showcase',
        componentsLabel: 'komponen',
      },
      interaction: {
        title: 'Level Interaksi',
        description: 'Tentukan animasi dan tingkat kekayaan interaksi untuk template Anda.',
        static: 'Statis',
        staticDesc: 'Tanpa animasi, transisi instan, interaktivitas minimal',
        staticExample: 'Performa maksimum, kesederhanaan',
        moderate: 'Sedang',
        moderateDesc: 'Efek hover, transisi halus, feedback dasar',
        moderateExample: 'Direkomendasikan untuk sebagian besar aplikasi',
        rich: 'Kaya',
        richDesc: 'Animasi, micro-interaction, loading skeleton, parallax',
        richExample: 'Situs marketing, nuansa premium',
      },
      responsiveness: {
        title: 'Responsivitas',
        description: 'Tentukan pendekatan desain responsif untuk template Anda.',
        desktopFirst: 'Desktop-First',
        desktopFirstDesc: 'Dioptimalkan untuk desktop, scale down ke mobile',
        desktopFirstBest: 'Alat internal, panel admin',
        mobileFirst: 'Mobile-First',
        mobileFirstDesc: 'Dioptimalkan untuk mobile, scale up ke desktop',
        mobileFirstBest: 'Situs publik, aplikasi konsumen',
        fullyResponsive: 'Fully Responsive',
        fullyResponsiveDesc: 'Optimasi setara untuk semua ukuran layar',
        fullyResponsiveBest: 'Aplikasi multi-device',
        bestFor: 'Terbaik untuk:',
      },
      outputFormat: {
        title: 'Pilih Format Output',
        description: 'Pilih framework atau format teknologi untuk template yang akan dihasilkan.',
        htmlCss: 'HTML + CSS',
        htmlCssDesc: 'Pure HTML dengan CSS murni, tanpa framework JS',
        react: 'React JS',
        reactDesc: 'React components dengan JSX dan hooks',
        vue: 'Vue.js',
        vueDesc: 'Vue 3 components dengan Composition API',
        angular: 'Angular',
        angularDesc: 'Angular components dengan TypeScript',
        svelte: 'Svelte',
        svelteDesc: 'Svelte components dengan compile-time optimization',
        // Custom output format
        custom: 'Format Kustom',
        customDesc: 'Tentukan format output kustom sesuai kebutuhan Anda',
        customInputTitle: 'Deskripsi Format Kustom',
        customPlaceholder: 'Contoh: PHP dengan Laravel Blade templates dan Alpine.js untuk interaktivitas. Gunakan Tailwind CSS untuk styling. Sertakan helper functions dan Eloquent models.',
        customHint: 'Jelaskan teknologi, framework, atau format spesifik yang Anda inginkan.',
      },
      llmModel: {
        title: 'Pilih Model LLM',
        description: 'Pilih model AI yang akan menghasilkan template Anda.',
        // Template Summary
        summaryTitle: 'Ringkasan Template',
        summaryDesc: 'Berikut adalah template yang akan digenerate berdasarkan konfigurasi Anda:',
        totalPages: 'Total Halaman',
        pagesList: 'Daftar Halaman',
        totalComponents: 'Total Komponen',
        predefinedPages: 'Halaman Standar',
        customPages: 'Halaman Kustom',
        componentShowcasePages: 'Halaman Showcase Komponen',
        customComponentPages: 'Halaman Komponen Kustom',
        predefinedComponents: 'Komponen Standar',
        customComponents: 'Komponen Kustom',
        credits: 'Kredit Anda:',
        freeModels: 'Model Gratis',
        premiumModels: 'Model Premium',
        geminiFlash: 'Gemini Flash',
        geminiFlashDesc: 'Gratis untuk semua pengguna, generasi cepat',
        geminiPro: 'Gemini Pro',
        geminiProDesc: 'Model premium Google, hasil lebih detail',
        gpt4: 'GPT-4',
        gpt4Desc: 'OpenAI GPT-4, kualitas tertinggi',
        claude3: 'Claude 3',
        claude3Desc: 'Anthropic Claude, fokus keamanan & akurasi',
        noCreditTitle: 'Kredit Habis',
        noCreditDesc: 'Anda tidak memiliki kredit. Silakan isi ulang kredit untuk menggunakan model premium.',
        ready: 'Siap untuk Generate!',
        readyDesc: 'Anda telah menyelesaikan semua 3 langkah. Klik tombol "Generate Template" untuk membuat template kustom Anda.',
        insufficientCredits: 'Kredit tidak cukup',
        economical: 'Ekonomis',
        loadingModels: 'Memuat model...',
        noModelsAvailable: 'Tidak ada model yang tersedia saat ini.',
        allModelsLabel: 'SEMUA MODEL',
        selectModel: 'Pilih Model yang Sesuai',
        creditsRequired: 'Kredit Dibutuhkan:',
        // Credit breakdown
        modelCost: 'Model:',
        extraPages: 'Halaman Extra:',
        extraComponents: 'Komponen Extra:',
        pagesCount: 'Jumlah Halaman:',
        componentsCount: 'Jumlah Komponen:',
        included: 'termasuk',
        totalCost: 'Total:',
      },
    },
  },
  auth: {
    login: 'Masuk',
    register: 'Daftar',
    logout: 'Keluar',
    email: 'Email',
    password: 'Password',
    rememberMe: 'Ingat Saya',
    forgotPassword: 'Lupa Password',
    name: 'Nama',
    confirmPassword: 'Konfirmasi Password',
  },
  theme: {
    light: 'Terang',
    dark: 'Gelap',
    system: 'Sistem',
  },
};

// English translations
const en: Translations = {
  common: {
    save: 'Save',
    cancel: 'Cancel',
    delete: 'Delete',
    edit: 'Edit',
    create: 'Create',
    update: 'Update',
    close: 'Close',
    back: 'Back',
    next: 'Next',
    previous: 'Previous',
    submit: 'Submit',
    loading: 'Loading...',
    success: 'Success',
    error: 'Error',
    warning: 'Warning',
    info: 'Info',
    confirm: 'Confirm',
    yes: 'Yes',
    no: 'No',
    search: 'Search',
    filter: 'Filter',
    reset: 'Reset',
    credits: 'credits',
    of: 'of',
  },
  nav: {
    dashboard: 'Dashboard',
    wizard: 'Wizard',
    projects: 'Projects',
    templates: 'Templates',
    profile: 'Profile',
    settings: 'Settings',
    logout: 'Logout',
  },
  wizard: {
    title: 'Template Wizard',
    description: 'Create your custom frontend template in 3 structured steps',
    stepOf: 'Step',
    stepDescriptions: {
      step1: 'Choose your CSS framework, template category, and output format',
      step2: 'Configure pages, layout, theme, UI density, and components',
      step3: 'Select the LLM model for generation and review credits',
    },
    stepTitles: {
      step1: 'Framework, Category & Output',
      step2: 'Visual Design & Content',
      step3: 'LLM Model Selection',
    },
    generateTemplate: 'Generate Template',
    generating: 'Generating Template...',
    generatingDescription: 'Creating your custom template based on your selections. This may take a moment.',
    startingGeneration: 'Starting generation...',
    generatingPage: 'Generating',
    pageOf: 'Page',
    generationFailed: 'Generation Failed',
    blueprintPreview: 'Blueprint JSON',
    showBlueprint: 'Show Blueprint',
    hideBlueprint: 'Hide Blueprint',
    wizardFirst: 'Wizard-First Design',
    wizardFirstDescription: 'All configuration is done through structured steps. No free-form prompts. Your selections are deterministic and reproducible.',
    steps: {
      framework: {
        title: 'Choose CSS Framework',
        description: 'Select the CSS framework foundation for your template. This decision affects all subsequent component structures and styling patterns.',
        tailwind: 'Tailwind CSS',
        tailwindDesc: 'Utility-first framework, highly customizable, modern approach',
        bootstrap: 'Bootstrap',
        bootstrapDesc: 'Component-based, rapid prototyping, extensive ecosystem',
        pureCss: 'Pure CSS',
        pureCssDesc: 'Vanilla CSS, no framework, full control, lightweight',
      },
      category: {
        title: 'Select Template Category',
        description: 'Choose the primary use case for your template. This will influence default page recommendations and layout patterns.',
        adminLabel: 'Admin Dashboard',
        adminDesc: 'Internal tools, data management, CRUD operations',
        companyLabel: 'Company Profile',
        companyDesc: 'Public-facing, content showcase, about/services pages',
        landingLabel: 'Landing Page',
        landingDesc: 'Marketing-focused, conversion-optimized, hero sections',
        saasLabel: 'SaaS Application',
        saasDesc: 'User accounts, feature sections, pricing pages',
        blogLabel: 'Blog / Content Site',
        blogDesc: 'Article listings, reading experience, categories',
        ecommerceLabel: 'E-Commerce',
        ecommerceDesc: 'Product catalogs, shopping cart, checkout pages',
        adminDashboard: 'Admin Dashboard',
        adminDashboardDesc: 'Internal tools, data management, CRUD operations',
        companyProfile: 'Company Profile',
        companyProfileDesc: 'Public-facing, content showcase, about/services pages',
        landingPage: 'Landing Page',
        landingPageDesc: 'Marketing-focused, conversion-optimized, hero sections',
        saasApplication: 'SaaS Application',
        saasApplicationDesc: 'User accounts, feature sections, pricing pages',
        blogContentSite: 'Blog / Content Site',
        blogContentSiteDesc: 'Article listings, reading experience, categories',
        eCommerce: 'E-Commerce',
        eCommerceDesc: 'Product catalogs, shopping cart, checkout pages',
        // Custom category
        custom: 'Custom Category',
        customDesc: 'Define a custom template category to match your specific needs',
        customInputTitle: 'Custom Category Details',
        customNameLabel: 'Category Name',
        customNamePlaceholder: 'Example: Health Portal, Inventory System, Education Platform',
        customDescLabel: 'Category Description',
        customDescPlaceholder: 'Describe the purpose and main features of your template category...',
        customMinChars: 'Minimum 3 characters',
      },
      pages: {
        title: 'Select Pages',
        description: 'Choose the specific pages to include in your template. Select at least one page.',
        useSuggested: 'Use Suggested Pages for',
        suggested: 'Suggested',
        selectAtLeastOne: 'Please select at least one page to continue',
        selectedCount: 'Selected',
        authCategory: 'Authentication',
        appCategory: 'Application',
        publicCategory: 'Public',
        authentication: 'Authentication',
        application: 'Application',
        public: 'Public',
        login: 'Login',
        register: 'Register',
        forgotPassword: 'Forgot Password',
        dashboard: 'Dashboard',
        userManagement: 'User Management',
        settings: 'Settings',
        charts: 'Charts / Analytics',
        tables: 'Tables / Data List',
        profile: 'Profile',
        about: 'About',
        contact: 'Contact',
        // Custom pages
        customPagesTitle: 'Custom Pages',
        customPagesDesc: 'Add custom pages not available in standard options',
        addCustomPage: 'Add Custom Page',
        customPageName: 'Page Name',
        customPageNamePlaceholder: 'Example: Inventory, Reports, Analytics Dashboard',
        customPageDesc: 'Page Description',
        customPageDescPlaceholder: 'Describe the function and content of this page...',
        customPageMinChars: 'Minimum 2 characters',
        extraPagesNote: 'Pages exceeding base quota will incur additional credits',
      },
      layout: {
        title: 'Layout & Navigation',
        description: 'Configure the structural navigation patterns and layout elements for your template.',
        navigationStyle: 'Navigation Style',
        sidebar: 'Sidebar',
        sidebarDesc: 'Vertical menu, ideal for many items',
        topbar: 'Top Navigation',
        topbarDesc: 'Horizontal menu bar, clean',
        hybrid: 'Hybrid (Sidebar + Topbar)',
        hybridDesc: 'Best of both worlds',
        sidebarDefaultState: 'Sidebar Default State',
        expanded: 'Expanded by Default',
        collapsed: 'Collapsed by Default',
        additionalElements: 'Additional Elements',
        enableBreadcrumbs: 'Enable Breadcrumbs',
        breadcrumbsDesc: 'Show navigation breadcrumbs on pages',
        footerStyle: 'Footer Style',
        minimal: 'Minimal (copyright only)',
        minimalFooter: 'Minimal (copyright only)',
        fullFooter: 'Full (with links)',
        // Custom navigation items
        customNavTitle: 'Custom Navigation Items',
        customNavDesc: 'Add custom menu navigation items',
        addCustomNav: 'Add Navigation Item',
        customNavLabel: 'Menu Label',
        customNavLabelPlaceholder: 'Example: Reports, Analytics, Inventory',
        customNavRoute: 'Route/URL',
        customNavRoutePlaceholder: 'Example: /reports, /analytics',
        customNavIcon: 'Icon (optional)',
        customNavIconPlaceholder: 'Example: chart-bar, document, folder',
        customNavMinChars: 'Minimum 2 characters',
      },
      theme: {
        title: 'Theme & Visual Identity',
        description: 'Define your color scheme and visual mode preferences.',
        primaryColor: 'Primary Color',
        secondaryColor: 'Secondary Color',
        colorMode: 'Color Mode',
        lightMode: 'Light Mode',
        darkMode: 'Dark Mode',
        backgroundStyle: 'Background Style',
        solid: 'Solid',
        gradient: 'Subtle Gradient',
        preview: 'Preview',
        compact: 'Compact',
        compactDesc: 'Tight spacing, small fonts, data-dense',
        comfortable: 'Comfortable',
        comfortableDesc: 'Balanced spacing, readable',
        spacious: 'Spacious',
        spaciousDesc: 'Generous whitespace, large touch targets',
        sharp: 'Sharp',
        sharpDesc: '0-2px radius, modern/technical',
        rounded: 'Rounded',
        roundedDesc: '4-8px radius, friendly/approachable',
        primary: 'Primary',
        secondary: 'Secondary',
      },
      uiDensity: {
        title: 'UI Density & Style',
        description: 'Control spacing, sizing, and visual weight of your template.',
        density: 'Density',
        compact: 'Compact',
        compactDesc: 'Tight spacing, small fonts, data-dense',
        comfortable: 'Comfortable',
        comfortableDesc: 'Balanced spacing, readable',
        spacious: 'Spacious',
        spaciousDesc: 'Generous whitespace, large touch targets',
        borderRadius: 'Border Radius',
        sharp: 'Sharp',
        sharpDesc: '0-2px radius, modern/technical aesthetic',
        rounded: 'Rounded',
        roundedDesc: '4-8px radius, friendly/approachable',
        preview: 'Preview',
        buttonExample: 'Button Example',
        cardContent: 'Card content with current density and border settings',
      },
      components: {
        title: 'Components',
        description: 'Select UI components to include in your template. Choose at least one component.',
        selectAtLeastOne: 'Please select at least one component to continue',
        selectedCount: 'Selected',
        chartLibraryRequired: 'Please select a chart library since you\'ve chosen to include charts',
        buttons: 'Buttons',
        buttonsDesc: 'Primary, Secondary, Outline, Icon buttons',
        forms: 'Forms',
        formsDesc: 'Text inputs, Select, Checkbox, Radio, Textarea',
        modals: 'Modals',
        modalsDesc: 'Dialog boxes, confirmation prompts',
        dropdowns: 'Dropdowns',
        dropdownsDesc: 'Menu dropdowns, select alternatives',
        alerts: 'Alerts / Toasts',
        alertsDesc: 'Success, error, warning, info notifications',
        cards: 'Cards',
        cardsDesc: 'Content containers with header/body/footer',
        tabs: 'Tabs',
        tabsDesc: 'Horizontal/vertical tab navigation',
        charts: 'Charts',
        chartsDesc: 'Data visualizations (requires library selection)',
        chartLibrary: 'Chart Library',
        chartjs: 'Chart.js',
        chartjsDesc: 'Simple, flexible, popular',
        echarts: 'Apache ECharts',
        echartsDesc: 'Powerful, feature-rich, enterprise-grade',
        // Custom components
        customComponentsTitle: 'Custom Components',
        customComponentsDesc: 'Add custom UI components not available in standard options',
        addCustomComponent: 'Add Custom Component',
        customComponentName: 'Component Name',
        customComponentNamePlaceholder: 'Example: Kanban Board, Calendar, File Upload',
        customComponentDescLabel: 'Component Description',
        customComponentDescPlaceholder: 'Describe the function and features of this component...',
        customComponentMinChars: 'Minimum 2 characters',
        extraComponentsNote: 'Components exceeding base quota will incur additional credits',
        componentPagesNote: 'Each component = 1 showcase page',
        componentsLabel: 'components',
      },
      interaction: {
        title: 'Interaction Level',
        description: 'Define animation and interaction richness for your template.',
        static: 'Static',
        staticDesc: 'No animations, instant transitions, minimal interactivity',
        staticExample: 'Maximum performance, simplicity',
        moderate: 'Moderate',
        moderateDesc: 'Hover effects, smooth transitions, basic feedback',
        moderateExample: 'Recommended for most applications',
        rich: 'Rich',
        richDesc: 'Animations, micro-interactions, loading skeletons, parallax',
        richExample: 'Marketing sites, premium feel',
      },
      responsiveness: {
        title: 'Responsiveness',
        description: 'Define the responsive design approach for your template.',
        desktopFirst: 'Desktop-First',
        desktopFirstDesc: 'Optimized for desktop, scales down to mobile',
        desktopFirstBest: 'Internal tools, admin panels',
        mobileFirst: 'Mobile-First',
        mobileFirstDesc: 'Optimized for mobile, scales up to desktop',
        mobileFirstBest: 'Public sites, consumer apps',
        fullyResponsive: 'Fully Responsive',
        fullyResponsiveDesc: 'Equal optimization for all screen sizes',
        fullyResponsiveBest: 'Multi-device applications',
        bestFor: 'Best for:',
      },
      outputFormat: {
        title: 'Select Output Format',
        description: 'Choose the framework or technology format for the generated template.',
        htmlCss: 'HTML + CSS',
        htmlCssDesc: 'Pure HTML with plain CSS, no JS framework',
        react: 'React JS',
        reactDesc: 'React components with JSX and hooks',
        vue: 'Vue.js',
        vueDesc: 'Vue 3 components with Composition API',
        angular: 'Angular',
        angularDesc: 'Angular components with TypeScript',
        svelte: 'Svelte',
        svelteDesc: 'Svelte components with compile-time optimization',
        // Custom output format
        custom: 'Custom Format',
        customDesc: 'Define a custom output format to match your needs',
        customInputTitle: 'Custom Format Description',
        customPlaceholder: 'Example: PHP with Laravel Blade templates and Alpine.js for interactivity. Use Tailwind CSS for styling. Include helper functions and Eloquent models.',
        customHint: 'Describe the specific technology, framework, or format you want.',
      },
      llmModel: {
        title: 'Select LLM Model',
        description: 'Choose the AI model that will generate your template.',
        // Template Summary
        summaryTitle: 'Template Summary',
        summaryDesc: 'Here is the template that will be generated based on your configuration:',
        totalPages: 'Total Pages',
        pagesList: 'Pages List',
        totalComponents: 'Total Components',
        predefinedPages: 'Standard Pages',
        customPages: 'Custom Pages',
        componentShowcasePages: 'Component Showcase Pages',
        customComponentPages: 'Custom Component Pages',
        predefinedComponents: 'Standard Components',
        customComponents: 'Custom Components',
        credits: 'Your Credits:',
        freeModels: 'Free Models',
        premiumModels: 'Premium Models',
        geminiFlash: 'Gemini Flash',
        geminiFlashDesc: 'Free for all users, fast generation',
        geminiPro: 'Gemini Pro',
        geminiProDesc: 'Google premium model, more detailed results',
        gpt4: 'GPT-4',
        gpt4Desc: 'OpenAI GPT-4, highest quality',
        claude3: 'Claude 3',
        claude3Desc: 'Anthropic Claude, focused on safety & accuracy',
        noCreditTitle: 'No Credits',
        noCreditDesc: 'You have no credits. Please top up to use premium models.',
        ready: 'Ready to Generate!',
        readyDesc: 'You\'ve completed all 3 steps. Click the "Generate Template" button to create your custom template.',
        insufficientCredits: 'Insufficient Credits',
        economical: 'Economical',
        loadingModels: 'Loading models...',
        noModelsAvailable: 'No models available at this time.',
        allModelsLabel: 'ALL MODELS',
        selectModel: 'Select a Suitable Model',
        creditsRequired: 'Credits Required:',
        // Credit breakdown
        modelCost: 'Model:',
        extraPages: 'Extra Pages:',
        extraComponents: 'Extra Components:',
        pagesCount: 'Page Count:',
        componentsCount: 'Component Count:',
        included: 'included',
        totalCost: 'Total:',
      },
    },
  },
  auth: {
    login: 'Login',
    register: 'Register',
    logout: 'Logout',
    email: 'Email',
    password: 'Password',
    rememberMe: 'Remember Me',
    forgotPassword: 'Forgot Password',
    name: 'Name',
    confirmPassword: 'Confirm Password',
  },
  theme: {
    light: 'Light',
    dark: 'Dark',
    system: 'System',
  },
};

const translations: Record<Language, Translations> = { id, en };

/**
 * Get current language
 */
export function getCurrentLanguage(): Language {
  return currentLanguage.value;
}

/**
 * Set current language
 */
export function setLanguage(lang: Language): void {
  currentLanguage.value = lang;
  // Persist to localStorage
  if (typeof window !== 'undefined') {
    localStorage.setItem('app-language', lang);
  }
}

/**
 * Initialize language from localStorage or default
 */
export function initLanguage(): void {
  if (typeof window !== 'undefined') {
    const saved = localStorage.getItem('app-language') as Language;
    if (saved && (saved === 'id' || saved === 'en')) {
      currentLanguage.value = saved;
    }
  }
}

/**
 * Main i18n composable
 */
export function useI18n() {
  const t: ComputedRef<Translations> = computed(() => translations[currentLanguage.value]);
  
  return {
    t,
    currentLang: computed(() => currentLanguage.value),
    setLang: setLanguage,
    getCurrentLang: getCurrentLanguage,
  };
}

/**
 * Get translation without composable (for use outside components)
 */
export function getTranslation(): Translations {
  return translations[currentLanguage.value];
}
