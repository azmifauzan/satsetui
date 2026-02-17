<script setup lang="ts">
/**
 * FileTree Component
 *
 * Displays the file tree for multi-file framework generation output.
 * Groups files by scaffold (boilerplate) vs component (LLM-generated pages).
 * Allows selecting files to view their content.
 */
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useI18n } from '@/lib/i18n';

interface FileItem {
  id: number;
  file_path: string;
  file_type: string;
  is_scaffold: boolean;
}

interface Props {
  generationId: number;
  isCompleted: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'selectFile', file: FileItem): void;
}>();

const { currentLang } = useI18n();

const files = ref<FileItem[]>([]);
const selectedFileId = ref<number | null>(null);
const loading = ref(false);
const expandedGroups = ref<Record<string, boolean>>({ scaffold: false, components: true });

const scaffoldFiles = computed(() =>
  files.value.filter(f => f.is_scaffold).sort((a, b) => a.file_path.localeCompare(b.file_path))
);

const componentFiles = computed(() =>
  files.value.filter(f => !f.is_scaffold).sort((a, b) => a.file_path.localeCompare(b.file_path))
);

async function loadFiles() {
  if (!props.isCompleted) return;
  loading.value = true;
  try {
    const response = await axios.get(`/generation/${props.generationId}/files`);
    files.value = response.data.files || [];
  } catch {
    // Ignore errors — might not have files (HTML+CSS mode)
  } finally {
    loading.value = false;
  }
}

function selectFile(file: FileItem) {
  selectedFileId.value = file.id;
  emit('selectFile', file);
}

function toggleGroup(group: string) {
  expandedGroups.value[group] = !expandedGroups.value[group];
}

function getFileIcon(filePath: string): string {
  const ext = filePath.split('.').pop()?.toLowerCase() || '';
  const iconMap: Record<string, string> = {
    tsx: '⚛️', jsx: '⚛️',
    vue: '💚', svelte: '🧡',
    ts: '🔷', js: '🟨',
    css: '🎨', html: '🌐',
    json: '📋', md: '📝',
  };
  return iconMap[ext] || '📄';
}

function getFileName(filePath: string): string {
  return filePath.split('/').pop() || filePath;
}

function getFileDir(filePath: string): string {
  const parts = filePath.split('/');
  parts.pop();
  return parts.length > 0 ? parts.join('/') + '/' : '';
}

onMounted(() => {
  if (props.isCompleted) {
    loadFiles();
  }
});

defineExpose({ loadFiles });
</script>

<template>
  <div class="h-full flex flex-col text-xs">
    <!-- Header -->
    <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900/80 flex-shrink-0">
      <div class="flex items-center justify-between">
        <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-widest">
          {{ currentLang === 'en' ? 'Files' : 'File' }}
        </span>
        <span class="text-[10px] text-slate-400 dark:text-slate-600">
          {{ files.length }} {{ currentLang === 'en' ? 'files' : 'file' }}
        </span>
      </div>
    </div>

    <!-- File tree -->
    <div class="flex-1 overflow-y-auto">
      <div v-if="loading" class="p-4 text-center text-slate-500">
        <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
        {{ currentLang === 'en' ? 'Loading...' : 'Memuat...' }}
      </div>

      <div v-else-if="files.length === 0" class="p-4 text-center text-slate-500 dark:text-slate-500">
        {{ currentLang === 'en' ? 'No files available' : 'Tidak ada file' }}
      </div>

      <div v-else>
        <!-- Components (LLM-generated pages) -->
        <div v-if="componentFiles.length > 0">
          <button
            @click="toggleGroup('components')"
            class="w-full px-3 py-1.5 flex items-center gap-1.5 text-left hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors"
          >
            <svg
              class="w-3 h-3 text-slate-400 transition-transform"
              :class="{ 'rotate-90': expandedGroups.components }"
              fill="currentColor" viewBox="0 0 20 20"
            >
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            <span class="font-semibold text-blue-600 dark:text-blue-400">
              {{ currentLang === 'en' ? 'Pages' : 'Halaman' }}
            </span>
            <span class="text-slate-400 dark:text-slate-600">({{ componentFiles.length }})</span>
          </button>
          <div v-if="expandedGroups.components" class="pb-1">
            <button
              v-for="file in componentFiles"
              :key="file.id"
              @click="selectFile(file)"
              :class="[
                'w-full px-3 py-1 pl-7 flex items-center gap-1.5 text-left hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors',
                selectedFileId === file.id
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                  : 'text-slate-700 dark:text-slate-300'
              ]"
            >
              <span class="flex-shrink-0">{{ getFileIcon(file.file_path) }}</span>
              <span class="truncate">
                <span class="text-slate-400 dark:text-slate-600">{{ getFileDir(file.file_path) }}</span>{{ getFileName(file.file_path) }}
              </span>
            </button>
          </div>
        </div>

        <!-- Scaffold (boilerplate) -->
        <div v-if="scaffoldFiles.length > 0">
          <button
            @click="toggleGroup('scaffold')"
            class="w-full px-3 py-1.5 flex items-center gap-1.5 text-left hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors"
          >
            <svg
              class="w-3 h-3 text-slate-400 transition-transform"
              :class="{ 'rotate-90': expandedGroups.scaffold }"
              fill="currentColor" viewBox="0 0 20 20"
            >
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            <span class="font-semibold text-slate-600 dark:text-slate-400">
              {{ currentLang === 'en' ? 'Scaffold' : 'Kerangka' }}
            </span>
            <span class="text-slate-400 dark:text-slate-600">({{ scaffoldFiles.length }})</span>
          </button>
          <div v-if="expandedGroups.scaffold" class="pb-1">
            <button
              v-for="file in scaffoldFiles"
              :key="file.id"
              @click="selectFile(file)"
              :class="[
                'w-full px-3 py-1 pl-7 flex items-center gap-1.5 text-left hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors',
                selectedFileId === file.id
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                  : 'text-slate-700 dark:text-slate-300'
              ]"
            >
              <span class="flex-shrink-0">{{ getFileIcon(file.file_path) }}</span>
              <span class="truncate">
                <span class="text-slate-400 dark:text-slate-600">{{ getFileDir(file.file_path) }}</span>{{ getFileName(file.file_path) }}
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
