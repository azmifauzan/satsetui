<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import Card from '@/components/dashboard/Card.vue';
import Faq from '@/components/Faq.vue';
import { useI18n } from '@/lib/i18n';

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
const { t } = useI18n();

const statCards = computed(() => [
  {
    title: t.value.dashboard.totalTemplates,
    value: props.stats.total_templates,
    icon: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
    color: 'blue' as const,
    trend: { value: 0, isPositive: true }
  },
  {
    title: t.value.dashboard.thisMonth,
    value: props.stats.templates_this_month,
    icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    color: 'green' as const,
    trend: { value: 0, isPositive: true }
  },
  {
    title: t.value.dashboard.credits,
    value: props.stats.credits_remaining,
    icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    color: 'purple' as const
  },
  {
    title: t.value.dashboard.lastGenerated,
    value: props.stats.last_generated || t.value.dashboard.never,
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    color: 'orange' as const
  }
]);

const quickActions = computed(() => [
  {
    title: t.value.dashboard.newTemplate,
    description: t.value.dashboard.newTemplateDesc,
    icon: 'M12 4v16m8-8H4',
    color: 'blue',
    href: '/wizard'
  },
  {
    title: t.value.dashboard.browseTemplates,
    description: t.value.dashboard.browseTemplatesDesc,
    icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    color: 'green',
    href: '/templates'
  },
]);
</script>

<template>
  <AppLayout>
    <Head :title="t.dashboard.title" />

    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">{{ t.dashboard.title }}</h1>
      <p class="text-slate-600 dark:text-slate-400">{{ t.dashboard.welcome }}</p>
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
    <Card :title="t.dashboard.quickActions" :subtitle="t.dashboard.quickActionsDesc" class="mb-8">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a
          v-for="action in quickActions"
          :key="action.title"
          :href="action.href"
          class="flex items-start p-4 rounded-lg border-2 border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 transition-all hover:shadow-md group"
        >
          <div :class="[
            'w-10 h-10 rounded-lg flex items-center justify-center mr-4 flex-shrink-0',
            action.color === 'blue' ? 'bg-blue-100 dark:bg-blue-900/20 group-hover:bg-blue-500' :
            'bg-green-100 dark:bg-green-900/20 group-hover:bg-green-500'
          ]">
            <svg :class="[
              'w-5 h-5 transition-colors',
              action.color === 'blue' ? 'text-blue-600 group-hover:text-white' :
              'text-green-600 group-hover:text-white'
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
    <Card :title="t.dashboard.gettingStarted" :subtitle="t.dashboard.gettingStartedDesc" class="mb-8">
      <div class="space-y-4">
        <div class="flex items-start">
          <div class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
            1
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-slate-900 dark:text-white mb-1">{{ t.dashboard.step1Title }}</h4>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ t.dashboard.step1Desc }}</p>
          </div>
        </div>

        <div class="flex items-start">
          <div class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
            2
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-slate-900 dark:text-white mb-1">{{ t.dashboard.step2Title }}</h4>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ t.dashboard.step2Desc }}</p>
          </div>
        </div>

        <div class="flex items-start">
          <div class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
            3
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-slate-900 dark:text-white mb-1">{{ t.dashboard.step3Title }}</h4>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ t.dashboard.step3Desc }}</p>
          </div>
        </div>

        <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
          <a
            href="/wizard"
            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-semibold"
          >
            {{ t.dashboard.startCreating }}
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </div>
      </div>
    </Card>

    <!-- FAQ Section -->
    <Card :title="t.dashboard.faqTitle" :subtitle="t.dashboard.faqDesc">
      <Faq />
    </Card>
  </AppLayout>
</template>




