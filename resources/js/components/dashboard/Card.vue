<script setup lang="ts">
interface Props {
  title?: string;
  subtitle?: string;
  noPadding?: boolean;
}

withDefaults(defineProps<Props>(), {
  noPadding: false
});
</script>

<template>
  <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
    <!-- Header -->
    <div v-if="title || subtitle" class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
      <h3 v-if="title" class="text-lg font-semibold text-slate-900 dark:text-white">{{ title }}</h3>
      <p v-if="subtitle" class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ subtitle }}</p>
    </div>

    <!-- Content -->
    <div :class="noPadding ? '' : 'p-6'">
      <slot />
    </div>
  </div>
</template>
