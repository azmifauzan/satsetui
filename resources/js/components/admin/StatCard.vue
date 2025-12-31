<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  title: string;
  value: string | number;
  icon?: string;
  subtitle?: string;
  trend?: {
    value: number;
    label: string;
  };
  color?: 'blue' | 'green' | 'red' | 'yellow' | 'purple' | 'indigo';
}

const props = withDefaults(defineProps<Props>(), {
  color: 'blue',
});

const colorClasses = computed(() => {
  const colors = {
    blue: 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
    green: 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
    red: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
    yellow: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400',
    purple: 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400',
    indigo: 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400',
  };
  return colors[props.color];
});

const trendColor = computed(() => {
  if (!props.trend) return '';
  return props.trend.value >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
});
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">{{ title }}</p>
        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ value }}</p>
        <p v-if="subtitle" class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ subtitle }}</p>
        
        <div v-if="trend" class="mt-2 flex items-center text-sm" :class="trendColor">
          <svg v-if="trend.value >= 0" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
          </svg>
          <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
          <span>{{ Math.abs(trend.value) }}% {{ trend.label }}</span>
        </div>
      </div>
      
      <div v-if="icon" class="flex-shrink-0 ml-4">
        <div :class="['w-12 h-12 rounded-lg flex items-center justify-center', colorClasses]">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>
