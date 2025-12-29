<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import Card from '@/components/dashboard/Card.vue';

interface Props {
  stats: {
    total_templates: number;
    templates_this_month: number;
    credits_remaining: number;
    last_generated: string | null;
  };
  recentActivity: Array<{
    id: number;
    action: string;
    timestamp: string;
  }>;
}

const props = defineProps<Props>();
const page = usePage();
const currentLang = computed(() => (page.props.auth?.user as any)?.language || 'id');

const t = computed(() => {
  return currentLang.value === 'id' ? {
    dashboard: 'Dashboard',
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
    documentation: 'Dokumentasi',
    documentationDesc: 'Pelajari cara menggunakan generator',
    gettingStarted: 'Memulai',
    gettingStartedDesc: 'Baru menggunakan Template Generator? Beginilah cara memulai',
    step1Title: 'Pilih Framework Anda',
    step1Desc: 'Pilih antara Tailwind CSS atau Bootstrap untuk fondasi template Anda.',
    step2Title: 'Pilih Kategori Template',
    step2Desc: 'Pilih dari Admin Dashboard, Landing Page, Aplikasi SaaS, atau kategori lainnya.',
    step3Title: 'Konfigurasi Melalui Wizard',
    step3Desc: 'Ikuti 11 langkah terstruktur untuk menyesuaikan layout, tema, komponen, dan lainnya.',
    step4Title: 'Generate & Download',
    step4Desc: 'Dapatkan template siap produksi dengan kode yang konsisten dan dapat diprediksi.',
    startCreating: 'Mulai Membuat Template',
  } : {
    dashboard: 'Dashboard',
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
    documentation: 'Documentation',
    documentationDesc: 'Learn how to use the generator',
    gettingStarted: 'Getting Started',
    gettingStartedDesc: 'New to Template Generator? Here\'s how to begin',
    step1Title: 'Choose Your Framework',
    step1Desc: 'Select between Tailwind CSS or Bootstrap for your template foundation.',
    step2Title: 'Pick Template Category',
    step2Desc: 'Choose from Admin Dashboard, Landing Page, SaaS App, or other categories.',
    step3Title: 'Configure Through Wizard',
    step3Desc: 'Follow 11 structured steps to customize layout, theme, components, and more.',
    step4Title: 'Generate & Download',
    step4Desc: 'Get your production-ready template with consistent, predictable code.',
    startCreating: 'Start Creating Template',
  };
});

const statCards = computed(() => [
  {
    title: t.totalTemplates || 'Total Templates',
    value: props.stats.total_templates,
    icon: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
    color: 'blue' as const,
    trend: { value: 0, isPositive: true }
  },
  {
    title: t.thisMonth || 'This Month',
    value: props.stats.templates_this_month,
    icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    color: 'green' as const,
    trend: { value: 0, isPositive: true }
  },
  {
    title: t.credits || 'Credits',
    value: props.stats.credits_remaining,
    icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    color: 'purple' as const
  },
  {
    title: t.lastGenerated || 'Last Generated',
    value: props.stats.last_generated || t.never || 'Never',
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    color: 'orange' as const
  }
]);

const quickActions = computed(() => [
  {
    title: t.newTemplate || 'New Template',
    description: t.newTemplateDesc || 'Start wizard to create new template',
    icon: 'M12 4v16m8-8H4',
    color: 'blue',
    href: '/wizard'
  },
  {
    title: t.browseTemplates || 'Browse Templates',
    description: t.browseTemplatesDesc || 'View and manage your templates',
    icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    color: 'green',
    href: '#'
  },
  {
    title: t.documentation || 'Documentation',
    description: t.documentationDesc || 'Learn how to use the generator',
    icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    color: 'purple',
    href: '#'
  },
]);
</script>

<template>
  <AppLayout>
    <Head :title="t.dashboard" />

    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">{{ t.dashboard }}</h1>
      <p class="text-slate-600 dark:text-slate-400">{{ t.welcome }}</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <StatCard
        v-for="stat in statCards"
        :key="stat.title"
        :title="stat.title"
        :value="stat.value"
        :icon="stat.icon"
        :color="stat.color"
        :trend="stat.trend"
      />
    </div>

    <!-- Quick Actions -->
    <Card :title="t.quickActions" :subtitle="t.quickActionsDesc" class="mb-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a
          v-for="action in quickActions"
          :key="action.title"
          :href="action.href"
          class="flex items-start p-4 rounded-lg border-2 border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 transition-all hover:shadow-md group"
        >
          <div :class="[
            'w-10 h-10 rounded-lg flex items-center justify-center mr-4 flex-shrink-0',
            action.color === 'blue' ? 'bg-blue-100 dark:bg-blue-900/20 group-hover:bg-blue-500' :
            action.color === 'green' ? 'bg-green-100 dark:bg-green-900/20 group-hover:bg-green-500' :
            'bg-purple-100 dark:bg-purple-900/20 group-hover:bg-purple-500'
          ]">
            <svg :class="[
              'w-5 h-5 transition-colors',
              action.color === 'blue' ? 'text-blue-600 group-hover:text-white' :
              action.color === 'green' ? 'text-green-600 group-hover:text-white' :
              'text-purple-600 group-hover:text-white'
            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="action.icon" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
              {{ action.title }}
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ action.description }}</p>
          </div>
        </a>
      </div>
    </Card>

    <!-- Getting Started -->
    <Card :title="t.gettingStarted" :subtitle="t.gettingStartedDesc">
      <div class="space-y-4">
        <div class="flex items-start">
          <div class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
            1
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-slate-900 dark:text-white mb-1">{{ t.step1Title }}</h4>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ t.step1Desc }}</p>
          </div>
        </div>

        <div class="flex items-start">
          <div class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
            2
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-slate-900 dark:text-white mb-1">{{ t.step2Title }}</h4>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ t.step2Desc }}</p>
          </div>
        </div>

        <div class="flex items-start">
          <div class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
            3
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-slate-900 dark:text-white mb-1">{{ t.step3Title }}</h4>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ t.step3Desc }}</p>
          </div>
        </div>

        <div class="flex items-start">
          <div class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
            4
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-slate-900 dark:text-white mb-1">{{ t.step4Title }}</h4>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ t.step4Desc }}</p>
          </div>
        </div>

        <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
          <a
            href="/wizard"
            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-semibold"
          >
            {{ t.startCreating }}
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </div>
      </div>
    </Card>
  </AppLayout>
</template>




