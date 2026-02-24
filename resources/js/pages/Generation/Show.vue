<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';
import JSZip from 'jszip';
import { useI18n } from '@/lib/i18n';
import { useTheme } from '@/lib/theme';
import LivePreview from '@/components/generation/LivePreview.vue';
import FileTree from '@/components/generation/FileTree.vue';

interface PageProgress {
  status: 'pending' | 'generating' | 'completed' | 'failed';
  content: string | null;
  error: string | null;
  processing_time: number;
}

interface Generation {
  id: number;
  user_id: number;
  project_id: number;
  model_used: string;
  credits_used: number;
  status: string;
  current_status: string;
  current_page_index: number;
  total_pages: number;
  progress_data: Record<string, PageProgress>;
  blueprint_json: any;
  mcp_prompt: string;
  generated_content: string | null;
  error_message: string | null;
  processing_time: number;
  started_at: string;
  completed_at: string | null;
  created_at: string;
  updated_at: string;
  project: {
    id: number;
    name: string;
    blueprint: any;
    status: string;
    generated_at: string;
  };
  refinement_messages?: Array<{
    id: number;
    role: 'system' | 'user' | 'assistant';
    content: string;
    type: string | null;
    page_name: string | null;
    created_at: string;
  }>;
}

interface ChatMessage {
  id: number;
  role: 'system' | 'user' | 'assistant';
  content: string;
  timestamp: Date;
  type?: 'status' | 'page_complete' | 'error' | 'refine';
  pageName?: string;
}

interface Props {
  generation: Generation;
  userCredits: number;
}

const props = defineProps<Props>();
const { currentLang } = useI18n();
const { isDark, toggleTheme } = useTheme();

// State
const generationData = ref<Generation>(props.generation);
const activeView = ref<'preview' | 'code'>('preview');
const selectedPage = ref<string | null>(null);
const chatMessages = ref<ChatMessage[]>([]);
const commandInput = ref('');
const isRefining = ref(false);
const isGenerating = ref(false);
const chatContainer = ref<HTMLElement | null>(null);
const iframeSrc = ref('');
const isEditingTitle = ref(false);
const editTitleValue = ref('');
const titleInput = ref<HTMLInputElement | null>(null);
const selectedRefineModel = ref('satset');
const userCredits = ref(props.userCredits);
const isRetrying = ref(false);
let msgId = 0;

// Framework output state
const outputFormat = computed(() => blueprint.value?.outputFormat || 'html-css');
const isFrameworkOutput = computed(() => ['react', 'vue', 'angular', 'svelte'].includes(outputFormat.value));
const showFileTree = ref(false);
const selectedFileContent = ref<string | null>(null);
const selectedFileName = ref<string | null>(null);
const fileTreeRef = ref<InstanceType<typeof FileTree> | null>(null);
const livePreviewRef = ref<InstanceType<typeof LivePreview> | null>(null);

// Blueprint info for wizard summary
const blueprint = computed(() => generationData.value.blueprint_json || generationData.value.project?.blueprint || {});
const categoryLabel = computed(() => {
  const c = blueprint.value?.category;
  const labels: Record<string, string> = {
    'admin-dashboard': 'Admin Dashboard',
    'company-profile': 'Company Profile',
    'landing-page': 'Landing Page',
    'saas-application': 'SaaS Application',
    'blog-content-site': 'Blog / Content Site',
    'e-commerce': 'E-Commerce',
    'dashboard': 'Dashboard',
    'mobile-apps': 'Mobile App UI',
  };
  return labels[c] || c || '-';
});

const colorSchemeLabel = computed(() => {
  const c = blueprint.value?.colorScheme;
  const labels: Record<string, string> = {
    blue: 'Ocean Blue', green: 'Forest Green', purple: 'Royal Purple',
    red: 'Ruby Red', amber: 'Warm Amber', slate: 'Slate Gray',
  };
  return labels[c] || c || '-';
});

const styleLabel = computed(() => blueprint.value?.stylePreset || '-');
const fontLabel = computed(() => blueprint.value?.fontFamily || '-');
const navStyleLabel = computed(() => {
  const n = blueprint.value?.navStyle;
  if (n === 'top') return 'Top Nav';
  if (n === 'sidebar') return 'Sidebar Nav';
  if (n === 'both') return 'Top + Sidebar';
  return n || '-';
});

// Computed
const pagesList = computed(() => Object.entries(generationData.value.progress_data || {}));

const pageNames = computed(() => Object.keys(generationData.value.progress_data || {}));

const isCompleted = computed(() => generationData.value.status === 'completed');
const isFailed = computed(() => generationData.value.status === 'failed');
const hasFailedPages = computed(() =>
  Object.values(generationData.value.progress_data || {}).some((p) => p.status === 'failed'),
);
const failedPageNames = computed(() =>
  Object.entries(generationData.value.progress_data || {})
    .filter(([, p]) => p.status === 'failed')
    .map(([name]) => name),
);

const progressPercentage = computed(() => {
  if (!generationData.value.total_pages) return 0;
  return Math.round((generationData.value.current_page_index / generationData.value.total_pages) * 100);
});

const modelLabel = computed(() => {
  const m = generationData.value.model_used;
  if (m === 'satset' || m?.includes('flash')) return 'Satset';
  if (m === 'expert' || m?.includes('pro')) return 'Expert';
  return m;
});

const currentPageContent = computed(() => {
  if (!selectedPage.value) {
    const firstCompleted = pagesList.value.find(([, d]) => d.status === 'completed');
    if (firstCompleted) return firstCompleted[1].content || '';
    return '';
  }
  const pd = generationData.value.progress_data?.[selectedPage.value];
  return pd?.content || '';
});

const allPagesContent = computed(() => {
  return pagesList.value
    .filter(([, d]) => d.status === 'completed' && d.content)
    .map(([, d]) => d.content)
    .join('\n');
});

// Watch selected page for preview update
watch([selectedPage, activeView], () => {
  updatePreview();
});

function updatePreview() {
  const content = selectedPage.value ? currentPageContent.value : allPagesContent.value;
  if (content) {
    const blob = new Blob([content], { type: 'text/html' });
    iframeSrc.value = URL.createObjectURL(blob);
  }
}

// Title editing
function startEditTitle() {
  editTitleValue.value = generationData.value.project.name;
  isEditingTitle.value = true;
  nextTick(() => titleInput.value?.focus());
}

async function saveTitle() {
  const newName = editTitleValue.value.trim();
  if (!newName) { isEditingTitle.value = false; return; }
  try {
    await axios.patch(`/generation/${generationData.value.id}/name`, { name: newName });
    generationData.value.project.name = newName;
  } catch { /* ignore */ }
  isEditingTitle.value = false;
}

function cancelEditTitle() {
  isEditingTitle.value = false;
}

// Chat helpers
function addMessage(role: ChatMessage['role'], content: string, type?: ChatMessage['type'], pageName?: string) {
  chatMessages.value.push({
    id: ++msgId,
    role,
    content,
    timestamp: new Date(),
    type,
    pageName,
  });
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
  });
}

// Stream generation with SSE
async function startStreamGeneration() {
  if (isGenerating.value || isCompleted.value) return;
  isGenerating.value = true;

  addMessage('system', currentLang.value === 'en'
    ? `Starting generation with ${modelLabel.value} model...`
    : `Memulai generasi dengan model ${modelLabel.value}...`, 'status');

  try {
    const evtSource = new EventSource(`/generation/${generationData.value.id}/stream`);

    evtSource.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);

        if (data.type === 'status') {
          addMessage('system', currentLang.value === 'en'
            ? `Generating page: **${data.page}** (${data.index + 1}/${data.total})`
            : `Membuat halaman: **${data.page}** (${data.index + 1}/${data.total})`, 'status', data.page);

          // Update local progress
          if (generationData.value.progress_data[data.page]) {
            generationData.value.progress_data[data.page].status = 'generating';
          }
          generationData.value.current_page_index = data.index;
        }

        if (data.type === 'page_complete') {
          // Update local state
          if (generationData.value.progress_data[data.page]) {
            generationData.value.progress_data[data.page].status = 'completed';
            generationData.value.progress_data[data.page].content = data.content;
          }
          generationData.value.current_page_index = data.index;

          addMessage('assistant', currentLang.value === 'en'
            ? `✅ Page **${data.page}** generated successfully!`
            : `✅ Halaman **${data.page}** berhasil dibuat!`, 'page_complete', data.page);

          // Auto-select first completed page and update preview
          if (!selectedPage.value) {
            selectedPage.value = data.page;
          }
          updatePreview();
        }

        if (data.type === 'page_error') {
          if (generationData.value.progress_data[data.page]) {
            generationData.value.progress_data[data.page].status = 'failed';
          }
          addMessage('system', `❌ Error on ${data.page}: ${data.error}`, 'error', data.page);
        }

        if (data.type === 'complete') {
          generationData.value.status = data.status;
          generationData.value.current_page_index = data.total_pages;

          const currentFailed = Object.values(generationData.value.progress_data || {}).filter(
            (p) => p.status === 'failed',
          );

          if (data.status === 'completed' && currentFailed.length === 0) {
            addMessage('system', currentLang.value === 'en'
              ? '🎉 Generation complete! You can now refine your pages using commands below.'
              : '🎉 Generasi selesai! Kamu bisa merevisi halaman menggunakan perintah di bawah.', 'status');
          } else if (currentFailed.length > 0) {
            const failedNames = Object.entries(generationData.value.progress_data || {})
              .filter(([, p]) => p.status === 'failed').map(([n]) => n);
            addMessage('system', currentLang.value === 'en'
              ? `⚠️ Generation finished with ${currentFailed.length} failed page(s): ${failedNames.join(', ')}. Use the Retry button to retry.`
              : `⚠️ Generasi selesai dengan ${currentFailed.length} halaman gagal: ${failedNames.join(', ')}. Gunakan tombol Retry untuk mencoba ulang.`, 'error');
          } else {
            addMessage('system', currentLang.value === 'en'
              ? '❌ Generation failed. Use the Retry button to try again.'
              : '❌ Generasi gagal. Gunakan tombol Retry untuk mencoba lagi.', 'error');
          }

          isGenerating.value = false;
          evtSource.close();
          // Reload to get full data
          router.reload({ only: ['generation'] });
        }

        if (data.type === 'error') {
          addMessage('system', `❌ ${data.message}`, 'error');
          isGenerating.value = false;
          evtSource.close();
        }
      } catch (e) {
        console.error('SSE parse error:', e);
      }
    };

    evtSource.onerror = () => {
      evtSource.close();
      isGenerating.value = false;
      addMessage('system', currentLang.value === 'en'
        ? '⚠️ Connection lost. Checking progress...'
        : '⚠️ Koneksi terputus. Memeriksa progres...', 'error');
      // Fallback: reload page
      setTimeout(() => router.reload(), 2000);
    };
  } catch (error) {
    isGenerating.value = false;
    addMessage('system', `Error: ${error}`, 'error');
  }
}

// Retry failed pages
async function retryFailedPages() {
  if (isRetrying.value || isGenerating.value) return;
  isRetrying.value = true;

  const pages = failedPageNames.value;
  addMessage('system', currentLang.value === 'en'
    ? `🔄 Retrying failed pages: ${pages.join(', ')}...`
    : `🔄 Mengulang halaman yang gagal: ${pages.join(', ')}...`, 'status');

  try {
    const response = await axios.post(`/generation/${generationData.value.id}/retry-failed`);

    if (response.data.success) {
      // Reset local state for failed pages
      for (const pageName of pages) {
        if (generationData.value.progress_data[pageName]) {
          generationData.value.progress_data[pageName].status = 'pending';
          generationData.value.progress_data[pageName].error = null;
        }
      }
      generationData.value.status = 'generating';
      generationData.value.current_page_index = response.data.retry_from_index;

      // Restart SSE stream
      startStreamGeneration();
    } else {
      addMessage('system', `❌ ${response.data.error}`, 'error');
    }
  } catch (error: any) {
    addMessage('system', `❌ ${error.response?.data?.error || error.message}`, 'error');
  } finally {
    isRetrying.value = false;
  }
}

// Refine via chat command
async function handleCommand() {
  const cmd = commandInput.value.trim();
  if (!cmd || isRefining.value) return;

  const targetPage = selectedPage.value || pageNames.value[0];

  // Add user message immediately for instant feedback
  addMessage('user', cmd);
  commandInput.value = '';
  isRefining.value = true;

  try {
    // Add status message
    addMessage('system', currentLang.value === 'en'
      ? `Refining ${targetPage}...`
      : `Merevisi ${targetPage}...`, 'status', targetPage);

    const response = await axios.post(`/generation/${generationData.value.id}/refine`, {
      prompt: cmd,
      page_name: targetPage,
      model: selectedRefineModel.value,
    });

    if (response.data.success) {
      const refinedPage = response.data.page_name || targetPage;
      
      // Update local state
      if (generationData.value.progress_data[refinedPage]) {
        generationData.value.progress_data[refinedPage].content = response.data.content;
      }

      // Add success message
      addMessage('assistant', currentLang.value === 'en'
        ? `Refinement applied to **${refinedPage}**. Preview updated.`
        : `Revisi diterapkan ke **${refinedPage}**. Preview diperbarui.`, 'refine', refinedPage);

      updatePreview();
    } else {
      addMessage('system', `${response.data.error || 'Refinement failed'}`, 'error');
    }
  } catch (error: any) {
    addMessage('system', `${error.response?.data?.error || error.message}`, 'error');
  } finally {
    isRefining.value = false;
  }
}

function copyPageCode() {
  const code = currentPageContent.value;
  if (code) navigator.clipboard.writeText(code);
}

function downloadPage() {
  const code = currentPageContent.value;
  const name = selectedPage.value || 'page';
  const blob = new Blob([code], { type: 'text/html' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `${name}.html`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

function selectPage(pageName: string) {
  selectedPage.value = pageName;
  // Clear file selection when switching pages
  selectedFileContent.value = null;
  selectedFileName.value = null;
}

// Handle file selection from FileTree
async function handleFileSelect(file: { id: number; file_path: string; file_type: string }) {
  selectedFileName.value = file.file_path;
  try {
    const response = await axios.get(`/generation/${generationData.value.id}/files/${file.id}`);
    selectedFileContent.value = response.data.content || '';
    // Switch to code view when selecting a file
    activeView.value = 'code';
  } catch {
    selectedFileContent.value = '// Error loading file content';
  }
}

function toggleFileTree() {
  showFileTree.value = !showFileTree.value;
}

// Enhanced download for framework multi-file output
async function downloadAll() {
  const zip = new JSZip();
  const projectName = generationData.value.project.name.replace(/[^a-zA-Z0-9-_]/g, '_');

  if (isFrameworkOutput.value) {
    // Download all files from GenerationFile records
    try {
      const response = await axios.get(`/generation/${generationData.value.id}/files`);
      const files = response.data.files || [];
      for (const file of files) {
        const contentResp = await axios.get(`/generation/${generationData.value.id}/files/${file.id}`);
        zip.file(file.file_path, contentResp.data.content || '');
      }
    } catch {
      // Fallback to progress_data
      for (const [pageName, pageData] of pagesList.value) {
        if (pageData.status === 'completed' && pageData.content) {
          zip.file(`${pageName}.html`, pageData.content);
        }
      }
    }
  } else {
    // HTML+CSS mode: add all completed pages to ZIP
    for (const [pageName, pageData] of pagesList.value) {
      if (pageData.status === 'completed' && pageData.content) {
        zip.file(`${pageName}.html`, pageData.content);
      }
    }
  }

  zip.generateAsync({ type: 'blob' }).then((blob) => {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${projectName}.zip`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  });
}

// Code content for display — uses file selection or page content
const displayedCode = computed(() => {
  if (selectedFileContent.value !== null) {
    return selectedFileContent.value;
  }
  return currentPageContent.value;
});

onMounted(() => {  // Load stored refinement messages first
  if (generationData.value.refinement_messages && generationData.value.refinement_messages.length > 0) {
    generationData.value.refinement_messages.forEach((msg) => {
      chatMessages.value.push({
        id: ++msgId,
        role: msg.role,
        content: msg.content,
        timestamp: new Date(msg.created_at),
        type: msg.type as ChatMessage['type'],
        pageName: msg.page_name || undefined,
      });
    });
  }

  if (!isCompleted.value && !isFailed.value) {
    startStreamGeneration();
  } else {
    // Already completed - init preview and messages
    if (isCompleted.value) {
      // Only add completion message if there are no stored messages
      if (chatMessages.value.length === 0) {
        addMessage('system', currentLang.value === 'en'
          ? '✅ Generation was already completed. You can refine pages using the command input.'
          : '✅ Generasi sudah selesai. Kamu bisa merevisi halaman melalui input perintah.', 'status');
      }
      const firstPage = pageNames.value[0];
      if (firstPage) {
        selectedPage.value = firstPage;
      }
    } else if (isFailed.value || hasFailedPages.value) {
      const failed = failedPageNames.value;
      addMessage('system', currentLang.value === 'en'
        ? `❌ ${failed.length > 0 ? `Page(s) failed: ${failed.join(', ')}. Use the Retry button to try again.` : 'Generation failed. Use the Retry button to try again.'}`
        : `❌ ${failed.length > 0 ? `Halaman gagal: ${failed.join(', ')}. Gunakan tombol Retry untuk mencoba lagi.` : 'Generasi gagal. Gunakan tombol Retry untuk mencoba lagi.'}`, 'error');
    }
    updatePreview();
  }
});
</script>

<template>
  <div class="h-screen flex flex-col bg-white dark:bg-slate-950 text-slate-900 dark:text-white overflow-hidden" :class="isDark ? 'dark' : ''">
    <Head :title="generationData.project.name" />

    <!-- Top Bar - Simplified: editable title + credits -->
    <header class="h-11 bg-slate-100 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 flex-shrink-0">
      <div class="flex items-center gap-3 min-w-0 flex-1">
        <a href="/" class="flex items-center gap-2 flex-shrink-0">
          <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
          </div>
          <span class="text-xl font-bold text-slate-900 dark:text-white">Satset<span class="text-blue-600">UI</span></span>
        </a>
        <span class="text-slate-300 dark:text-slate-700">|</span>
        <!-- Editable Title -->
        <div v-if="isEditingTitle" class="flex items-center gap-1.5 min-w-0 flex-1">
          <input
            ref="titleInput"
            v-model="editTitleValue"
            @keyup.enter="saveTitle"
            @keyup.escape="cancelEditTitle"
            @blur="saveTitle"
            class="flex-1 min-w-0 px-2 py-0.5 text-sm bg-white dark:bg-slate-800 border border-blue-500 rounded text-slate-900 dark:text-white focus:outline-none"
          />
        </div>
        <button v-else @click="startEditTitle" class="flex items-center gap-1.5 min-w-0 group" :title="currentLang === 'en' ? 'Click to edit' : 'Klik untuk edit'">
          <span class="text-sm text-slate-600 dark:text-slate-300 truncate max-w-80">{{ generationData.project.name }}</span>
          <svg class="w-3 h-3 text-slate-400 dark:text-slate-600 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
          </svg>
        </button>
      </div>
      <div class="flex items-center gap-3 flex-shrink-0">
        <!-- Theme Toggle -->
        <button @click="toggleTheme" class="p-1.5 text-slate-500 hover:text-slate-900 dark:hover:text-white rounded transition-colors" :title="currentLang === 'en' ? 'Toggle theme' : 'Ganti tema'">
          <svg v-if="isDark" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" /></svg>
          <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" /></svg>
        </button>
        <!-- Credits -->
        <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-200 dark:bg-slate-800 rounded-lg">
          <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M7 3h10l4 4-9 14L3 7l4-4zm5 2L9 7h6l-3-2zm-6 3l3.5 8L5 8zm7.5 8L17 8l-3.5 8z"/></svg>
          <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">{{ userCredits }}</span>
          <span class="text-[10px] text-blue-600 dark:text-blue-400">{{ currentLang === 'en' ? 'credits' : 'kredit' }}</span>
        </div>
        <button @click="downloadAll" v-if="isCompleted"
          class="px-2.5 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors">
          {{ currentLang === 'en' ? 'Download All' : 'Unduh Semua' }}
        </button>
        <a href="/wizard" class="px-2.5 py-1 text-xs bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-900 dark:text-white rounded transition-colors">
          {{ currentLang === 'en' ? 'New Satset' : 'Satset Baru' }}
        </a>
        <a href="/dashboard" class="text-xs text-slate-600 dark:text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors">Dashboard</a>
      </div>
    </header>

    <!-- Main Layout -->
    <div class="flex-1 flex overflow-hidden">

      <!-- Left Sidebar: Wizard Summary + Chat -->
      <aside class="w-[420px] bg-slate-100 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col flex-shrink-0">

        <!-- Wizard Summary Card -->
        <div class="border-b border-slate-200 dark:border-slate-800 p-3">
          <div class="bg-gradient-to-br from-slate-200 dark:from-slate-800 to-slate-100 dark:to-slate-800/50 rounded-lg p-3 border border-slate-300 dark:border-slate-700/50">
            <div class="flex items-center justify-between mb-2">
              <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-widest">
                {{ currentLang === 'en' ? 'Project Summary' : 'Ringkasan Proyek' }}
              </span>
              <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-600/20 text-blue-600 dark:text-blue-300 font-medium">
                {{ modelLabel }}
              </span>
            </div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
              <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-500">{{ currentLang === 'en' ? 'Type' : 'Tipe' }}</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium">{{ categoryLabel }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-500">{{ currentLang === 'en' ? 'Color' : 'Warna' }}</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium">{{ colorSchemeLabel }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-500">{{ currentLang === 'en' ? 'Style' : 'Gaya' }}</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium capitalize">{{ styleLabel }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-500">Font</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium capitalize">{{ fontLabel }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-500">Nav</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium">{{ navStyleLabel }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-500">{{ currentLang === 'en' ? 'Output' : 'Output' }}</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium capitalize">{{ outputFormat }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-500">{{ currentLang === 'en' ? 'Pages' : 'Halaman' }}</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium">{{ generationData.total_pages }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Progress Only (Pages moved to preview area) -->
        <div class="border-b border-slate-200 dark:border-slate-800 px-3 py-2">
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-widest">
              {{ currentLang === 'en' ? 'Progress' : 'Progres' }}
            </span>
            <span class="text-[10px] text-slate-600 dark:text-slate-600">{{ generationData.current_page_index }}/{{ generationData.total_pages }}</span>
          </div>
          <!-- Progress bar -->
          <div class="w-full bg-slate-300 dark:bg-slate-800 rounded-full h-0.5 mb-2">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-0.5 rounded-full transition-all duration-700"
              :style="{ width: `${progressPercentage}%` }"></div>
          </div>
        </div>

        <!-- Chat Messages -->
        <div ref="chatContainer" class="flex-1 overflow-y-auto p-3 space-y-2.5">
          <div v-for="msg in chatMessages" :key="msg.id"
            :class="[
              'text-sm rounded-lg px-3 py-2 max-w-full',
              msg.role === 'user'
                ? 'bg-blue-100 dark:bg-blue-600/20 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-600/30 ml-8'
                : msg.role === 'assistant'
                  ? 'bg-green-100 dark:bg-green-600/10 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-600/20'
                  : msg.type === 'error'
                    ? 'bg-red-100 dark:bg-red-600/10 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-600/20'
                    : 'bg-slate-200 dark:bg-slate-800/60 text-slate-700 dark:text-slate-400 border border-slate-300 dark:border-slate-700/50'
            ]"
          >
            <div class="flex items-start gap-2">
              <span v-if="msg.role === 'user'" class="text-[10px] font-medium text-blue-600 dark:text-blue-400 flex-shrink-0">You</span>
              <span v-else-if="msg.role === 'assistant'" class="text-[10px] font-medium text-green-600 dark:text-green-400 flex-shrink-0">AI</span>
              <span v-else class="text-[10px] font-medium text-slate-500 dark:text-slate-500 flex-shrink-0">System</span>
            </div>
            <p class="mt-0.5 text-xs leading-relaxed whitespace-pre-wrap" v-html="msg.content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')"></p>
            <div class="text-[9px] text-slate-500 dark:text-slate-600 mt-1">
              {{ msg.timestamp.toLocaleTimeString() }}
            </div>
          </div>

          <!-- Generating indicator -->
          <div v-if="isGenerating" class="flex items-center gap-2 text-xs text-blue-700 dark:text-blue-300 px-3 py-2 bg-blue-100 dark:bg-blue-600/10 rounded-lg border border-blue-200 dark:border-blue-600/20">
            <div class="w-3 h-3 border-2 border-blue-600 dark:border-blue-400 border-t-transparent rounded-full animate-spin"></div>
            {{ currentLang === 'en' ? 'Generating...' : 'Membuat...' }}
          </div>

          <!-- Retrying indicator -->
          <div v-if="isRetrying" class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-300 px-3 py-2 bg-amber-100 dark:bg-amber-600/10 rounded-lg border border-amber-200 dark:border-amber-600/20">
            <div class="w-3 h-3 border-2 border-amber-600 dark:border-amber-400 border-t-transparent rounded-full animate-spin"></div>
            {{ currentLang === 'en' ? 'Preparing retry...' : 'Mempersiapkan retry...' }}
          </div>

          <!-- Refining indicator -->
          <div v-if="isRefining" class="flex items-center gap-2 text-xs text-purple-700 dark:text-purple-300 px-3 py-2 bg-purple-100 dark:bg-purple-600/10 rounded-lg border border-purple-200 dark:border-purple-600/20">
            <div class="w-3 h-3 border-2 border-purple-600 dark:border-purple-400 border-t-transparent rounded-full animate-spin"></div>
            {{ currentLang === 'en' ? 'Refining...' : 'Merevisi...' }}
          </div>
        </div>

        <!-- Chat Input Area - Bigger, with model selector -->
        <div class="border-t border-slate-200 dark:border-slate-800 p-3">
          <!-- Target page indicator -->
          <div v-if="selectedPage" class="flex items-center gap-1.5 mb-2">
            <span class="text-[10px] text-slate-500 dark:text-slate-500">{{ currentLang === 'en' ? 'Targeting' : 'Target' }}:</span>
            <span class="text-[10px] px-1.5 py-0.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded font-medium">{{ selectedPage }}</span>
          </div>
          <!-- Retry Failed Pages Banner -->
          <div v-if="hasFailedPages && !isGenerating && !isRetrying" class="mb-2 flex items-center justify-between px-3 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/40 rounded-lg">
            <div class="flex items-center gap-2 min-w-0">
              <svg class="w-3.5 h-3.5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <span class="text-[10px] text-red-700 dark:text-red-300 truncate">
                {{ currentLang === 'en' ? `Failed: ${failedPageNames.join(', ')}` : `Gagal: ${failedPageNames.join(', ')}` }}
              </span>
            </div>
            <button
              @click="retryFailedPages"
              class="ml-2 flex-shrink-0 px-2.5 py-1 text-[10px] font-semibold bg-red-600 hover:bg-red-700 text-white rounded transition-colors flex items-center gap-1"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              {{ currentLang === 'en' ? 'Retry' : 'Retry' }}
            </button>
          </div>

          <!-- Text input - larger -->
          <textarea
            v-model="commandInput"
            :placeholder="currentLang === 'en' ? 'Describe changes... (e.g. &quot;Make navbar fixed&quot;, &quot;Change button color to red&quot;)' : 'Jelaskan perubahan... (contoh: &quot;Buat navbar fixed&quot;, &quot;Ubah warna tombol jadi merah&quot;)'"
            :disabled="isRefining || isGenerating || !isCompleted"
            class="w-full px-3 py-2.5 text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 resize-none"
            rows="3"
            @keydown.enter.exact.prevent="handleCommand"
          ></textarea>
          <!-- Bottom row: model selector + send button -->
          <div class="flex items-center justify-between mt-2">
            <div class="flex items-center gap-2">
              <!-- Model selector -->
              <select
                v-model="selectedRefineModel"
                class="px-2 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500"
              >
                <option value="satset">⚡ Satset</option>
                <option value="expert">🧠 Expert</option>
              </select>
              <span class="text-[10px] text-slate-500 dark:text-slate-600">
                {{ selectedRefineModel === 'expert' ? '(3 credits)' : '(1 credit)' }}
              </span>
            </div>
            <button
              @click="handleCommand"
              :disabled="!commandInput.trim() || isRefining || isGenerating || !isCompleted"
              class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 dark:disabled:bg-slate-700 disabled:text-slate-400 dark:disabled:text-slate-500 text-white rounded-lg transition-colors text-sm font-medium flex items-center gap-1.5"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
              </svg>
              {{ currentLang === 'en' ? 'Send' : 'Kirim' }}
            </button>
          </div>
        </div>
      </aside>

      <!-- Right Panel: Preview / Code -->
      <main class="flex-1 flex flex-col overflow-hidden bg-white dark:bg-slate-950">

        <!-- Pages Navigation -->
        <div class="bg-slate-100 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-800 px-4 py-2 flex-shrink-0">
          <div class="flex items-center gap-2">
            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-widest flex-shrink-0">
              {{ currentLang === 'en' ? 'Pages' : 'Halaman' }}
            </span>
            <div class="flex flex-wrap gap-1.5">
              <button
                v-for="page in pageNames"
                :key="page"
                @click="selectPage(page)"
                :class="[
                  'px-2.5 py-1 text-xs rounded-md font-medium transition-all',
                  selectedPage === page
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-400 hover:bg-slate-300 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-slate-300'
                ]"
              >
                {{ page }}
                <span
                  v-if="generationData.progress_data[page]"
                  :class="[
                    'ml-1.5 inline-block w-1.5 h-1.5 rounded-full',
                    generationData.progress_data[page].status === 'completed' ? 'bg-green-400' :
                    generationData.progress_data[page].status === 'generating' ? 'bg-blue-400 animate-pulse' :
                    generationData.progress_data[page].status === 'failed' ? 'bg-red-400' :
                    'bg-slate-600'
                  ]"
                ></span>
              </button>
            </div>
          </div>
        </div>

        <!-- View Tabs + Actions -->
        <div class="h-10 bg-slate-100 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 flex-shrink-0">
          <div class="flex items-center gap-1">
            <button
              @click="activeView = 'preview'"
              :class="[
                'px-3 py-1.5 text-xs font-medium rounded transition-colors',
                activeView === 'preview'
                  ? 'bg-blue-100 dark:bg-blue-600/20 text-blue-700 dark:text-blue-300'
                  : 'text-slate-600 dark:text-slate-500 hover:text-slate-900 dark:hover:text-slate-300'
              ]"
            >
              <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                Preview
              </span>
            </button>
            <button
              @click="activeView = 'code'"
              :class="[
                'px-3 py-1.5 text-xs font-medium rounded transition-colors',
                activeView === 'code'
                  ? 'bg-blue-100 dark:bg-blue-600/20 text-blue-700 dark:text-blue-300'
                  : 'text-slate-600 dark:text-slate-500 hover:text-slate-900 dark:hover:text-slate-300'
              ]"
            >
              <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                {{ currentLang === 'en' ? 'Code' : 'Kode' }}
              </span>
            </button>
          </div>
          <div class="flex items-center gap-2">
            <!-- File tree toggle (framework output only) -->
            <button
              v-if="isFrameworkOutput && isCompleted"
              @click="toggleFileTree"
              :class="[
                'p-1.5 rounded transition-colors',
                showFileTree
                  ? 'bg-blue-100 dark:bg-blue-600/20 text-blue-600 dark:text-blue-400'
                  : 'text-slate-500 dark:text-slate-500 hover:text-slate-900 dark:hover:text-white'
              ]"
              :title="currentLang === 'en' ? 'Toggle file tree' : 'Tampilkan file'"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
              </svg>
            </button>
            <span v-if="selectedFileName" class="text-xs text-slate-600 dark:text-slate-500 truncate max-w-48">{{ selectedFileName }}</span>
            <span v-else-if="selectedPage" class="text-xs text-slate-600 dark:text-slate-500">{{ selectedPage }}</span>
            <button v-if="displayedCode" @click="copyPageCode"
              class="p-1.5 text-slate-500 dark:text-slate-500 hover:text-slate-900 dark:hover:text-white rounded transition-colors" title="Copy">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
            </button>
            <button v-if="displayedCode" @click="downloadPage"
              class="p-1.5 text-slate-500 dark:text-slate-500 hover:text-slate-900 dark:hover:text-white rounded transition-colors" title="Download">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            </button>
          </div>
        </div>

        <!-- Content Area with optional FileTree sidebar -->
        <div class="flex-1 overflow-hidden relative flex">
          <!-- File Tree Sidebar (framework output) -->
          <div
            v-if="isFrameworkOutput && showFileTree && isCompleted"
            class="w-56 border-r border-slate-200 dark:border-slate-800 flex-shrink-0"
          >
            <FileTree
              ref="fileTreeRef"
              :generation-id="generationData.id"
              :is-completed="isCompleted"
              @select-file="handleFileSelect"
            />
          </div>

          <!-- Preview / Code panels -->
          <div class="flex-1 overflow-hidden">
            <!-- Preview Tab -->
            <div v-show="activeView === 'preview'" class="h-full">
              <LivePreview
                ref="livePreviewRef"
                :generation-id="generationData.id"
                :output-format="outputFormat"
                :page-content="currentPageContent"
                :target-page="selectedPage"
                :is-completed="isCompleted"
                :is-generating="isGenerating"
              />
            </div>

            <!-- Code Tab -->
            <div v-show="activeView === 'code'" class="h-full overflow-auto bg-slate-50 dark:bg-slate-900">
              <pre v-if="displayedCode" class="p-4 text-xs"><code class="text-green-600 dark:text-green-400 font-mono whitespace-pre-wrap">{{ displayedCode }}</code></pre>
              <div v-else class="h-full flex items-center justify-center">
                <p class="text-slate-500 dark:text-slate-500 text-sm">
                  {{ currentLang === 'en' ? 'No code generated yet' : 'Belum ada kode' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>