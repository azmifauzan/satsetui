<script setup lang="ts">
interface Props {
  title: string;
  value: string | number;
  icon?: string;
  trend?: {
    value: number;
    isPositive: boolean;
  };
  color?: 'blue' | 'green' | 'purple' | 'orange';
}

const props = withDefaults(defineProps<Props>(), {
  color: 'blue'
});

const colorClasses = {
  blue: {
    bg: 'bg-blue-500',
    text: 'text-blue-600',
    bgLight: 'bg-blue-100 dark:bg-blue-900/20'
  },
  green: {
    bg: 'bg-green-500',
    text: 'text-green-600',
    bgLight: 'bg-green-100 dark:bg-green-900/20'
  },
  purple: {
    bg: 'bg-purple-500',
    text: 'text-purple-600',
    bgLight: 'bg-purple-100 dark:bg-purple-900/20'
  },
  orange: {
    bg: 'bg-orange-500',
    text: 'text-orange-600',
    bgLight: 'bg-orange-100 dark:bg-orange-900/20'
  }
};

const classes = colorClasses[props.color];
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">{{ title }}</p>
        <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ value }}</p>
        
        <div v-if="trend" class="flex items-center mt-2">
          <svg
            :class="['w-4 h-4 mr-1', trend.isPositive ? 'text-green-500' : 'text-red-500']"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              v-if="trend.isPositive"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 10l7-7m0 0l7 7m-7-7v18"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 14l-7 7m0 0l-7-7m7 7V3"
            />
          </svg>
          <span :class="['text-sm font-medium', trend.isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
            {{ Math.abs(trend.value) }}%
          </span>
          <span class="text-sm text-slate-500 dark:text-slate-400 ml-1">vs last month</span>
        </div>
      </div>

      <div v-if="icon" :class="['w-12 h-12 rounded-lg flex items-center justify-center', classes.bgLight]">
        <svg :class="['w-6 h-6', classes.text]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
        </svg>
      </div>
    </div>
  </div>
</template>




