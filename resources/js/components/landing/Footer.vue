<script setup lang="ts">
import { useI18n } from '@/lib/i18n';

const { t } = useI18n();

interface Props {
  onScrollToSection?: (sectionId: string) => void;
}

defineProps<Props>();
</script>

<template>
  <!-- Footer -->
  <footer class="bg-slate-900 dark:bg-slate-950 text-slate-300 py-6 border-t border-slate-800">
   
      

      <!-- Copyright -->
      <div class="border-slate-800 text-center text-sm">
        <p>&copy; {{ new Date().getFullYear() }} TemplateGen. {{ t.landing?.footer?.rights || 'All Rights Reserved.' }}</p>
      </div>
  </footer>
</template>
