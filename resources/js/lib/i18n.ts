/**
 * Internationalization (i18n) System
 * 
 * Bilingual support for Indonesian (id) and English (en).
 * DEFAULT LANGUAGE: English (en)
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
  return 'en'; // Default to English
}

// Global reactive language state - defaults to English
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
    admin: string;
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
    verifyEmail: string;
    verifyEmailMessage: string;
    verificationLinkSent: string;
    resendVerificationEmail: string;
    agreeToTerms: string;
    showPassword: string;
    hidePassword: string;
  };

  // Landing Page
  landing: {
    title: string;
    hero: {
      title: string;
      subtitle: string;
      cta: string;
      ctaSecondary: string;
      badge: string;
    };
    nav: {
      home: string;
      howItWorks: string;
      categories: string;
      faq: string;
      getStarted: string;
    };
    howItWorks: {
      title: string;
      subtitle: string;
      step1: {
        title: string;
        desc: string;
      };
      step2: {
        title: string;
        desc: string;
      };
      step3: {
        title: string;
        desc: string;
      };
    };
    categories: {
      title: string;
      subtitle: string;
      admin: {
        title: string;
        desc: string;
      };
      company: {
        title: string;
        desc: string;
      };
      landing: {
        title: string;
        desc: string;
      };
      saas: {
        title: string;
        desc: string;
      };
      blog: {
        title: string;
        desc: string;
      };
      portfolio: {
        title: string;
        desc: string;
      };
    };
    cta: {
      title: string;
      subtitle: string;
      button: string;
      free: string;
    };
    footer: {
      product: string;
      features: string;
      pricing: string;
      docs: string;
      changelog: string;
      company: string;
      about: string;
      blog: string;
      careers: string;
      contact: string;
      legal: string;
      privacy: string;
      terms: string;
      rights: string;
    };
    faq: {
      title: string;
      subtitle: string;
    };
  };

  // Dashboard
  dashboard: {
    title: string;
    welcome: string;
    totalTemplates: string;
    thisMonth: string;
    credits: string;
    lastGenerated: string;
    never: string;
    vsLastMonth: string;
    quickActions: string;
    quickActionsDesc: string;
    newTemplate: string;
    newTemplateDesc: string;
    browseTemplates: string;
    browseTemplatesDesc: string;
    gettingStarted: string;
    gettingStartedDesc: string;
    step1Title: string;
    step1Desc: string;
    step2Title: string;
    step2Desc: string;
    step3Title: string;
    step3Desc: string;
    startCreating: string;
    faqTitle: string;
    faqDesc: string;
  };

  // Theme
  theme: {
    light: string;
    dark: string;
    system: string;
  };

  // FAQ
  faq: Array<{
    question: string;
    answer: string;
  }>;
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
    admin: 'Admin',
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
        title: 'Pilih Model AI',
        description: 'Pilih tingkat kualitas AI yang sesuai dengan kebutuhan template Anda.',
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
        // Model Types
        fast: 'Cepat',
        fastTitle: 'Generasi Cepat',
        fastDesc: 'Generasi cepat dengan hasil berkualitas baik untuk template sederhana. Cocok untuk prototyping dan template dasar.',
        professional: 'Profesional',
        professionalTitle: 'Kualitas Profesional',
        professionalDesc: 'Hasil berkualitas tinggi dengan keseimbangan kecepatan dan detail. Ideal untuk proyek production-ready.',
        expert: 'Expert',
        expertTitle: 'Kualitas Expert',
        expertDesc: 'Kualitas terbaik untuk template kompleks dengan fitur lengkap. Memberikan hasil paling detail dan teroptimasi.',
        noCreditTitle: 'Kredit Habis',
        noCreditDesc: 'Anda tidak memiliki kredit. Silakan isi ulang kredit untuk melanjutkan generasi.',
        ready: 'Siap untuk Generate!',
        readyDesc: 'Anda telah menyelesaikan semua 3 langkah. Klik tombol "Generate Template" untuk membuat template kustom Anda.',
        insufficientCredits: 'Kredit tidak cukup',
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
    verifyEmail: 'Verifikasi Email',
    verifyEmailMessage: 'Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengklik link yang baru saja kami kirimkan. Jika Anda tidak menerima email, kami akan dengan senang hati mengirimkan yang baru.',
    verificationLinkSent: 'Link verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.',
    resendVerificationEmail: 'Kirim Ulang Email Verifikasi',
    agreeToTerms: 'Saya setuju dengan Syarat dan Ketentuan',
    showPassword: 'Tampilkan Password',
    hidePassword: 'Sembunyikan Password',
  },
  landing: {
    title: 'SatsetUI - Buat Template Frontend dengan Mudah & Sat-set!',
    hero: {
      title: 'Buat Template Frontend Profesional dalam Hitungan Menit',
      subtitle: 'Platform wizard-driven untuk menghasilkan template UI yang konsisten, dapat diprediksi, dan siap produksi. Tanpa coding manual, tanpa hasil yang acak.',
      cta: 'Mulai Gratis',
      ctaSecondary: 'Lihat Demo',
      badge: 'Deterministik & Dapat Diulang',
    },
    nav: {
      home: 'Beranda',
      howItWorks: 'Cara Kerja',
      categories: 'Kategori',
      faq: 'FAQ',
      getStarted: 'Mulai Sekarang',
    },
    howItWorks: {
      title: 'Cara Kerja',
      subtitle: 'Tiga langkah sederhana untuk template sempurna Anda',
      step1: {
        title: 'Konfigurasi Wizard 3 Langkah',
        desc: 'Pilih Framework & Kategori (Step 1), Desain Visual & Konten (Step 2), Model LLM (Step 3). Semua keputusan eksplisit dan terstruktur.',
      },
      step2: {
        title: 'Sistem Menghasilkan Blueprint',
        desc: 'Pilihan Anda dikonversi menjadi blueprint JSON yang valid dan MCP prompt yang lengkap. Setiap halaman di-generate secara terpisah untuk hasil optimal.',
      },
      step3: {
        title: 'Unduh & Gunakan',
        desc: 'Dapatkan kode template yang siap digunakan, lengkap dengan struktur file dan komponen. Hasil deterministik dan dapat diulang.',
      },
    },
    categories: {
      title: 'Kategori Template',
      subtitle: 'Pilih dari berbagai kategori template yang telah disesuaikan atau buat custom',
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
        title: 'E-Commerce',
        desc: 'Product catalogs, shopping cart, checkout',
      },
    },
    cta: {
      title: 'Siap Membuat Template Profesional?',
      subtitle: 'Bergabunglah dengan developer yang telah mempercepat workflow mereka',
      button: 'Mulai Membuat Template',
      free: 'Gratis untuk memulai, dapat kredit awal 25 kredit',
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
    faq: {
      title: 'Pertanyaan yang Sering Ditanyakan',
      subtitle: 'Temukan jawaban atas pertanyaan umum tentang SatsetUI',
    },
  },
  dashboard: {
    title: 'Dashboard',
    welcome: 'Selamat datang kembali! Berikut ringkasan template Anda.',
    totalTemplates: 'Total Template',
    thisMonth: 'Bulan Ini',
    credits: 'Kredit',
    lastGenerated: 'Terakhir Dibuat',
    never: 'Belum Pernah',
    vsLastMonth: 'vs bulan lalu',
    quickActions: 'Aksi Cepat',
    quickActionsDesc: 'Mulai tugas baru atau jelajahi template yang ada',
    newTemplate: 'Template Baru',
    newTemplateDesc: 'Mulai wizard untuk membuat template baru',
    browseTemplates: 'Jelajahi Template',
    browseTemplatesDesc: 'Lihat dan kelola template Anda',
    gettingStarted: 'Memulai',
    gettingStartedDesc: 'Baru menggunakan SatsetUI? Ikuti 3 langkah berikut',
    step1Title: 'Step 1: Framework, Kategori & Output Format',
    step1Desc: 'Pilih CSS framework (Tailwind/Bootstrap/Pure CSS), kategori template, dan format output (HTML+CSS, React, Vue, Angular, Svelte, atau Custom).',
    step2Title: 'Step 2: Desain Visual & Konten',
    step2Desc: 'Pilih halaman, konfigurasi layout & navigasi, atur tema (warna, mode dark/light), dan pilih komponen yang dibutuhkan.',
    step3Title: 'Step 3: Pilih Model LLM',
    step3Desc: 'Pilih model AI untuk generasi (Free: Gemini Flash, Premium: GPT-4, Claude, dll). Lihat estimasi biaya kredit sebelum generate.',
    startCreating: 'Mulai Membuat Template',
    faqTitle: 'Pertanyaan yang Sering Diajukan (FAQ)',
    faqDesc: 'Temukan jawaban untuk pertanyaan umum tentang SatsetUI',
  },
  theme: {
    light: 'Terang',
    dark: 'Gelap',
    system: 'Sistem',
  },
  faq: [
    {
      question: 'Apa itu SatsetUI?',
      answer: 'SatsetUI ("sat-set" = cepat & efisien) adalah platform wizard-driven untuk menghasilkan template frontend yang deterministic dan siap produksi. Berbeda dengan sistem prompt-to-design, kami menggunakan konfigurasi terstruktur melalui wizard 3 langkah yang menjamin hasil identik untuk pilihan yang sama. LLM hanya mengimplementasikan keputusan Anda, tidak menebak atau menginterpretasi.'
    },
    {
      question: 'Bagaimana cara menggunakan SatsetUI?',
      answer: 'Klik "Mulai Membuat Template" di dashboard. Wizard 3 langkah akan memandu Anda: (1) Pilih Framework CSS (Tailwind/Bootstrap/Pure CSS), Kategori Template, dan Output Format (HTML+CSS/React/Vue/Angular/Svelte), (2) Konfigurasi Desain Visual & Konten - pilih halaman, layout navigasi, tema warna, UI density, dan komponen UI, (3) Pilih Model LLM. Estimasi kredit ditampilkan sebelum generate.'
    },
    {
      question: 'Framework dan teknologi apa saja yang didukung?',
      answer: 'CSS Framework: Tailwind CSS (utility-first), Bootstrap (component-based), Pure CSS (vanilla tanpa framework). Output Format: HTML+CSS (static), React JS (JSX + Hooks), Vue.js (Composition API), Angular (TypeScript), Svelte, atau Custom Format. Chart Library: Chart.js atau Apache ECharts. Untuk HTML+CSS, jika Anda pilih Tailwind/Bootstrap, CDN akan otomatis di-embed di <head>.'
    },
    {
      question: 'Apa perbedaan user Gratis dan Premium?',
      answer: 'User Gratis: Dapat kredit awal 25 kredit saat registrasi, hanya bisa menggunakan Gemini 2.5 Flash (3 kredit per generasi standar). User Premium: Bisa membeli kredit tambahan (1 kredit = Rp 1.000), akses ke 5 model LLM premium (GPT-5.1 Codex Mini: 2 kredit, Claude Haiku 4.5: 6 kredit, GPT-5.1 Codex: 10 kredit, Gemini 3 Pro: 12 kredit, Claude Sonnet 4.5: 15 kredit).'
    },
    {
      question: 'Bagaimana perhitungan kredit bekerja?',
      answer: 'Formula: (Biaya Model + Extra Halaman + Extra Komponen) × (1 + Error Margin 10%) × (1 + Profit Margin 5%), dibulatkan ke atas. Kuota Dasar: 5 halaman (free), 6 komponen (free). Extra: +1 kredit per halaman tambahan, +0.5 kredit per komponen tambahan. Contoh: Gemini Flash (3kr) + 4 halaman (0kr) + 5 komponen (0kr) = 3kr × 1.10 × 1.05 = 4 kredit. Margin dapat dikonfigurasi admin.'
    },
    {
      question: 'Apa itu per-page generation dan kenapa penting?',
      answer: 'Sistem kami generate setiap halaman secara terpisah, bukan sekaligus. Keuntungan: (1) Fokus LLM lebih baik per halaman, (2) Progress tracking real-time, (3) Error recovery - satu halaman gagal tidak ganggu yang lain, (4) Akurasi kredit - token usage aktual tercatat per halaman untuk learning. Proses berjalan di background queue, Anda bisa tutup browser.'
    },
    {
      question: 'Apakah hasil generasi benar-benar deterministik?',
      answer: 'Ya! Dengan pilihan wizard yang identik, hasil akan sama persis setiap kali. Ini karena: (1) Tidak ada prompt bebas yang subjektif, (2) Semua keputusan dibuat lewat wizard terstruktur, (3) MCP Prompt Builder menerjemahkan blueprint ke instruksi eksplisit, (4) LLM tidak punya kebebasan kreatif. Setting otomatis: Responsiveness (fully-responsive), Interaction (moderate), Code Style (documented).'
    },
    {
      question: 'Berapa lama waktu generasi dan bagaimana prosesnya?',
      answer: 'Setiap halaman memakan waktu ~30-60 detik, tergantung kompleksitas dan model yang dipilih. Proses: Submit wizard → Validasi blueprint → Queue background job → Generate per halaman → Simpan code → Notifikasi selesai. Total waktu: jumlah halaman × 30-60 detik. Proses async, Anda tidak perlu tunggu di browser. Notifikasi dikirim saat selesai.'
    },
    {
      question: 'Bisakah saya custom kategori, halaman, dan komponen?',
      answer: 'Sangat bisa! Kategori: 6 preset (Admin Dashboard, Company Profile, Landing Page, SaaS, Blog, E-Commerce) + opsi Custom dengan nama dan deskripsi sendiri. Halaman: 10 preset (Login, Register, Dashboard, User Management, Charts, Tables, dll) + custom pages tanpa batas. Komponen: 8 preset (Buttons, Forms, Modals, Charts, dll) + custom components. Custom pages ditrack untuk statistik admin - yang populer bisa jadi preset di masa depan!'
    },
    {
      question: 'Bagaimana sistem layout dan tema bekerja?',
      answer: 'Layout: Pilih navigasi (Sidebar/Top Navigation/Hybrid), sidebar state (collapsed/expanded default), breadcrumbs (on/off), footer (minimal/full), custom nav items. Tema: Primary color (preset atau color picker), secondary color, mode (light/dark), background (solid/gradient). UI: Density (compact/comfortable/spacious), Border radius (sharp/rounded). Semua tersimpan di blueprint dan diterapkan konsisten ke semua halaman.'
    },
    {
      question: 'Bagaimana jika generasi gagal atau error?',
      answer: 'Sistem kami punya mekanisme refund otomatis. Jika generasi gagal: (1) Kredit dikembalikan 100%, (2) Error detail dicatat di GenerationFailure table, (3) History tetap tersimpan untuk debugging, (4) Anda bisa lihat last error lewat dashboard, (5) Retry tanpa charge ulang. Semua cost tracking transparan di CreditTransaction dan GenerationCost tables. Admin bisa monitor failure rate.'
    },
    {
      question: 'Apa itu History Recording dan Credit Learning?',
      answer: 'Setiap generasi mencatat: Full MCP prompt yang dikirim ke LLM, Response code yang dihasilkan, Token usage aktual (input/output), Waktu proses, Model yang digunakan. Data ini dipakai untuk: (1) Transparansi - Anda bisa lihat apa yang di-generate, (2) Debugging - Admin bisa cek kenapa generasi gagal, (3) Credit Learning - Estimasi kredit jadi lebih akurat dari data historis, (4) Model comparison - Analisa performa tiap model.'
    },
    {
      question: 'Apakah template yang sudah dibuat bisa diakses lagi?',
      answer: 'Ya! Semua template tersimpan permanen di menu "Templates". Anda bisa: (1) Lihat detail lengkap blueprint, (2) Preview code yang digenerate, (3) Download ulang tanpa biaya kredit tambahan, (4) Lihat history generasi (tanggal, model, kredit terpakai), (5) Generate ulang dengan konfigurasi sama atau berbeda. Template tidak expire.'
    },
    {
      question: 'Apa keuntungan wizard-based dibanding prompt-to-design?',
      answer: 'Prompt-based: "Buat admin dashboard modern dengan chart" → Hasil bervariasi, tidak reproducible, AI menebak apa arti "modern". Wizard-based: Framework=Tailwind, Category=Admin, Pages=[Dashboard,Charts], Layout=Sidebar, Theme=Blue/Dark, Density=Comfortable, Model=Gemini → Hasil identik setiap kali, no guessing, full control. Wizard memaksa Anda membuat keputusan eksplisit, menghasilkan output yang predictable dan professional.'
    },
    {
      question: 'Bagaimana model LLM dipilih dan apa perbedaannya?',
      answer: 'Free tier: Otomatis pakai Gemini 2.5 Flash (cepat, kualitas good, cocok testing/basic template). Premium: Bisa pilih sesuai kebutuhan. GPT-5.1 Codex Mini (2kr, very good, landing page), Claude Haiku 4.5 (6kr, excellent balance, dashboard), GPT-5.1 Codex (10kr, excellent, complex apps), Gemini 3 Pro (12kr, outstanding, enterprise), Claude Sonnet 4.5 (15kr, outstanding, production-ready critical projects). Trade-off: Speed vs Quality vs Cost.'
    }
  ],
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
    admin: 'Admin',
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
        title: 'Select AI Model',
        description: 'Choose the AI quality level that suits your template needs.',
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
        // Model Types
        fast: 'Fast',
        fastTitle: 'Fast Generation',
        fastDesc: 'Quick generation with good quality results for simple templates. Perfect for prototyping and basic templates.',
        professional: 'Professional',
        professionalTitle: 'Professional Quality',
        professionalDesc: 'High-quality results with balanced speed and detail. Ideal for production-ready projects.',
        expert: 'Expert',
        expertTitle: 'Expert Quality',
        expertDesc: 'Best quality for complex templates with comprehensive features. Delivers the most detailed and optimized results.',
        noCreditTitle: 'No Credits',
        noCreditDesc: 'You have no credits. Please top up to continue generation.',
        ready: 'Ready to Generate!',
        readyDesc: 'You\'ve completed all 3 steps. Click the "Generate Template" button to create your custom template.',
        insufficientCredits: 'Insufficient Credits',
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
    verifyEmail: 'Verify Email',
    verifyEmailMessage: 'Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.',
    verificationLinkSent: 'A new verification link has been sent to the email address you provided during registration.',
    resendVerificationEmail: 'Resend Verification Email',
    agreeToTerms: 'I agree to the Terms and Conditions',
    showPassword: 'Show Password',
    hidePassword: 'Hide Password',
  },
  landing: {
    title: 'SatsetUI - Create Frontend Templates Easily & Quickly!',
    hero: {
      title: 'Create Professional Frontend Templates in Minutes',
      subtitle: 'A wizard-driven platform for generating consistent, predictable, and production-ready UI templates. No manual coding, no random results.',
      cta: 'Start Free',
      ctaSecondary: 'View Demo',
      badge: 'Deterministic & Repeatable',
    },
    nav: {
      home: 'Home',
      howItWorks: 'How It Works',
      categories: 'Categories',
      faq: 'FAQ',
      getStarted: 'Get Started',
    },
    howItWorks: {
      title: 'How It Works',
      subtitle: 'Three simple steps to your perfect template',
      step1: {
        title: '3-Step Wizard Configuration',
        desc: 'Choose Framework & Category (Step 1), Visual Design & Content (Step 2), LLM Model (Step 3). All decisions are explicit and structured.',
      },
      step2: {
        title: 'System Generates Blueprint',
        desc: 'Your choices are converted into a valid JSON blueprint and complete MCP prompt. Each page is generated separately for optimal results.',
      },
      step3: {
        title: 'Download & Use',
        desc: 'Get ready-to-use template code, complete with file structure and components. Deterministic and repeatable results.',
      },
    },
    categories: {
      title: 'Template Categories',
      subtitle: 'Choose from various tailored template categories or create custom',
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
        title: 'E-Commerce',
        desc: 'Product catalogs, shopping cart, checkout',
      },
    },
    cta: {
      title: 'Ready to Create Professional Templates?',
      subtitle: 'Join developers who have accelerated their workflow',
      button: 'Start Creating Templates',
      free: 'Free to start, get 25 initial credits',
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
    faq: {
      title: 'Frequently Asked Questions',
      subtitle: 'Find answers to common questions about SatsetUI',
    },
  },
  dashboard: {
    title: 'Dashboard',
    welcome: "Welcome back! Here's an overview of your templates.",
    totalTemplates: 'Total Templates',
    thisMonth: 'This Month',
    credits: 'Credits',
    lastGenerated: 'Last Generated',
    never: 'Never',
    vsLastMonth: 'vs last month',
    quickActions: 'Quick Actions',
    quickActionsDesc: 'Start a new task or browse existing templates',
    newTemplate: 'New Template',
    newTemplateDesc: 'Start wizard to create new template',
    browseTemplates: 'Browse Templates',
    browseTemplatesDesc: 'View and manage your templates',
    gettingStarted: 'Getting Started',
    gettingStartedDesc: 'New to SatsetUI? Follow these 3 steps',
    step1Title: 'Step 1: Framework, Category & Output Format',
    step1Desc: 'Choose CSS framework (Tailwind/Bootstrap/Pure CSS), template category, and output format (HTML+CSS, React, Vue, Angular, Svelte, or Custom).',
    step2Title: 'Step 2: Visual Design & Content',
    step2Desc: 'Select pages, configure layout & navigation, set theme (colors, dark/light mode), and choose needed components.',
    step3Title: 'Step 3: Choose LLM Model',
    step3Desc: 'Select AI model for generation (Free: Gemini Flash, Premium: GPT-4, Claude, etc). View credit cost estimate before generating.',
    startCreating: 'Start Creating Template',
    faqTitle: 'Frequently Asked Questions (FAQ)',
    faqDesc: 'Find answers to common questions about SatsetUI',
  },
  theme: {
    light: 'Light',
    dark: 'Dark',
    system: 'System',
  },
  faq: [
    {
      question: 'What is SatsetUI?',
      answer: 'SatsetUI ("sat-set" = quick & efficient in Indonesian slang) is a wizard-driven platform for generating deterministic, production-ready frontend templates. Unlike prompt-to-design systems, we use structured configuration through a 3-step wizard that guarantees identical results for the same choices. The LLM only implements your decisions, never guesses or interprets.'
    },
    {
      question: 'How to use SatsetUI?',
      answer: 'Click "Start Creating Template" on dashboard. The 3-step wizard guides you: (1) Choose CSS Framework (Tailwind/Bootstrap/Pure CSS), Template Category, and Output Format (HTML+CSS/React/Vue/Angular/Svelte), (2) Configure Visual Design & Content - select pages, navigation layout, theme colors, UI density, and UI components, (3) Choose LLM Model. Credit estimate shown before generation.'
    },
    {
      question: 'What frameworks and technologies are supported?',
      answer: 'CSS Frameworks: Tailwind CSS (utility-first), Bootstrap (component-based), Pure CSS (vanilla without framework). Output Format: HTML+CSS (static), React JS (JSX + Hooks), Vue.js (Composition API), Angular (TypeScript), Svelte, or Custom Format. Chart Libraries: Chart.js or Apache ECharts. For HTML+CSS, if you choose Tailwind/Bootstrap, CDN will be auto-embedded in <head>.'
    },
    {
      question: 'What is the difference between Free and Premium users?',
      answer: 'Free Users: Get 25 initial credits upon registration, can only use Gemini 2.5 Flash (3 credits per standard generation). Premium Users: Can purchase additional credits (1 credit = Rp 1,000), access to 5 premium LLM models (GPT-5.1 Codex Mini: 2 credits, Claude Haiku 4.5: 6 credits, GPT-5.1 Codex: 10 credits, Gemini 3 Pro: 12 credits, Claude Sonnet 4.5: 15 credits).'
    },
    {
      question: 'How does credit calculation work?',
      answer: 'Formula: (Model Cost + Extra Pages + Extra Components) × (1 + Error Margin 10%) × (1 + Profit Margin 5%), rounded up. Base Quota: 5 pages (free), 6 components (free). Extra: +1 credit per additional page, +0.5 credit per additional component. Example: Gemini Flash (3cr) + 4 pages (0cr) + 5 components (0cr) = 3cr × 1.10 × 1.05 = 4 credits. Margins are admin-configurable.'
    },
    {
      question: 'What is per-page generation and why is it important?',
      answer: 'Our system generates each page separately, not all at once. Benefits: (1) Better LLM focus per page, (2) Real-time progress tracking, (3) Error recovery - one page failing doesn\'t affect others, (4) Credit accuracy - actual token usage recorded per page for learning. Process runs in background queue, you can close browser.'
    },
    {
      question: 'Are generation results truly deterministic?',
      answer: 'Yes! With identical wizard choices, results will be exactly the same every time. This is because: (1) No subjective free-form prompts, (2) All decisions made through structured wizard, (3) MCP Prompt Builder translates blueprint to explicit instructions, (4) LLM has no creative freedom. Auto settings: Responsiveness (fully-responsive), Interaction (moderate), Code Style (documented).'
    },
    {
      question: 'How long does generation take and what is the process?',
      answer: 'Each page takes ~30-60 seconds, depending on complexity and chosen model. Process: Submit wizard → Validate blueprint → Queue background job → Generate per page → Save code → Completion notification. Total time: number of pages × 30-60 seconds. Async process, no need to wait in browser. Notification sent when complete.'
    },
    {
      question: 'Can I customize categories, pages, and components?',
      answer: 'Absolutely! Categories: 6 presets (Admin Dashboard, Company Profile, Landing Page, SaaS, Blog, E-Commerce) + Custom option with your own name and description. Pages: 10 presets (Login, Register, Dashboard, User Management, Charts, Tables, etc) + unlimited custom pages. Components: 8 presets (Buttons, Forms, Modals, Charts, etc) + custom components. Custom pages are tracked for admin statistics - popular ones may become presets in future!'
    },
    {
      question: 'How do layout and theme systems work?',
      answer: 'Layout: Choose navigation (Sidebar/Top Navigation/Hybrid), sidebar state (collapsed/expanded default), breadcrumbs (on/off), footer (minimal/full), custom nav items. Theme: Primary color (preset or color picker), secondary color, mode (light/dark), background (solid/gradient). UI: Density (compact/comfortable/spacious), Border radius (sharp/rounded). All saved in blueprint and applied consistently to all pages.'
    },
    {
      question: 'What if generation fails or errors occur?',
      answer: 'Our system has automatic refund mechanism. If generation fails: (1) Credits refunded 100%, (2) Error details recorded in GenerationFailure table, (3) History still saved for debugging, (4) You can view last error via dashboard, (5) Retry without additional charge. All cost tracking transparent in CreditTransaction and GenerationCost tables. Admin can monitor failure rate.'
    },
    {
      question: 'What is History Recording and Credit Learning?',
      answer: 'Every generation records: Full MCP prompt sent to LLM, Generated response code, Actual token usage (input/output), Processing time, Model used. This data is used for: (1) Transparency - You can see what was generated, (2) Debugging - Admin can check why generation failed, (3) Credit Learning - Credit estimates become more accurate from historical data, (4) Model comparison - Analyze performance of each model.'
    },
    {
      question: 'Can previously created templates be accessed again?',
      answer: 'Yes! All templates are saved permanently in "Templates" menu. You can: (1) View complete blueprint details, (2) Preview generated code, (3) Re-download without additional credit cost, (4) View generation history (date, model, credits used), (5) Re-generate with same or different configuration. Templates don\'t expire.'
    },
    {
      question: 'What are the advantages of wizard-based vs prompt-to-design?',
      answer: 'Prompt-based: "Create modern admin dashboard with charts" → Varying results, not reproducible, AI guesses what "modern" means. Wizard-based: Framework=Tailwind, Category=Admin, Pages=[Dashboard,Charts], Layout=Sidebar, Theme=Blue/Dark, Density=Comfortable, Model=Gemini → Identical results every time, no guessing, full control. Wizard forces you to make explicit decisions, producing predictable and professional output.'
    },
    {
      question: 'How are LLM models chosen and what are their differences?',
      answer: 'Free tier: Automatically uses Gemini 2.5 Flash (fast, good quality, suitable for testing/basic templates). Premium: Can choose based on needs. GPT-5.1 Codex Mini (2cr, very good, landing pages), Claude Haiku 4.5 (6cr, excellent balance, dashboards), GPT-5.1 Codex (10cr, excellent, complex apps), Gemini 3 Pro (12cr, outstanding, enterprise), Claude Sonnet 4.5 (15cr, outstanding, production-ready critical projects). Trade-off: Speed vs Quality vs Cost.'
    }
  ],
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
    
    // Also send to backend if user is authenticated
    const pageProps = (window as any).__INERTIA__?.props?.['initialPage']?.props;
    if (pageProps?.auth?.user) {
      // Async call to update user language in database
      // Using fetch to avoid circular dependencies with vue router
      fetch('/language', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': ((document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''),
        },
        body: JSON.stringify({ language: lang }),
      }).catch(err => console.error('Failed to save language preference:', err));
    }
  }
}

/**
 * Toggle between Indonesian and English
 */
export function toggleLanguage(): void {
  const newLang = currentLanguage.value === 'id' ? 'en' : 'id';
  setLanguage(newLang);
}

/**
 * Initialize language from localStorage, page props, or default
 */
export function initLanguage(): void {
  if (typeof window !== 'undefined') {
    // First check localStorage
    const saved = localStorage.getItem('app-language') as Language;
    if (saved && (saved === 'id' || saved === 'en')) {
      currentLanguage.value = saved;
      return;
    }

    // Then check page props (from authenticated user)
    const pageProps = (window as any).__INERTIA__?.props?.['initialPage']?.props;
    if (pageProps?.userLanguage) {
      const userLang = pageProps.userLanguage as Language;
      if (userLang === 'id' || userLang === 'en') {
        currentLanguage.value = userLang;
        return;
      }
    }
  }
  
  // Default to English if nothing else found
  currentLanguage.value = 'en';
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
    toggleLanguage: toggleLanguage,
    getCurrentLang: getCurrentLanguage,
  };
}

/**
 * Get translation without composable (for use outside components)
 */
export function getTranslation(): Translations {
  return translations[currentLanguage.value];
}
