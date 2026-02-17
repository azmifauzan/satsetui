<script setup lang="ts">
/**
 * LivePreview Component
 *
 * Handles both static HTML preview (srcdoc/blob) and
 * server-side framework preview (dev server proxy).
 *
 * For HTML+CSS: renders content directly in iframe via blob URL.
 * For frameworks (React/Vue/Svelte/Angular): sets up a server-side
 * workspace with npm install + vite dev server, then proxies via backend.
 */
import { ref, computed, watch, onUnmounted } from 'vue';
import axios from 'axios';
import { useI18n } from '@/lib/i18n';

interface Props {
  generationId: number;
  outputFormat: string;
  pageContent: string;
  isCompleted: boolean;
  isGenerating: boolean;
}

const props = defineProps<Props>();
const { currentLang } = useI18n();

// Device modes for responsive preview
type DeviceMode = 'desktop' | 'tablet' | 'mobile';
const deviceMode = ref<DeviceMode>('desktop');

const deviceWidths: Record<DeviceMode, string> = {
  desktop: '100%',
  tablet: '768px',
  mobile: '375px',
};

// Preview state for framework output
type PreviewStatus = 'idle' | 'creating' | 'installing' | 'running' | 'stopped' | 'error';
const previewStatus = ref<PreviewStatus>('idle');
const previewUrl = ref<string | null>(null);
const previewError = ref<string | null>(null);
const isSettingUp = ref(false);
const statusPollingInterval = ref<ReturnType<typeof setInterval> | null>(null);

// Whether this is a framework output (needs server-side preview)
const isFramework = computed(() => {
  return ['react', 'vue', 'angular', 'svelte'].includes(props.outputFormat);
});

// Static preview blob URL
const staticIframeSrc = ref('');

// Update static preview when content changes (HTML+CSS mode)
watch(() => props.pageContent, (content) => {
  if (!isFramework.value && content) {
    const blob = new Blob([content], { type: 'text/html' });
    staticIframeSrc.value = URL.createObjectURL(blob);
  }
}, { immediate: true });

// Framework preview actions
async function setupPreview() {
  if (isSettingUp.value) return;
  isSettingUp.value = true;
  previewError.value = null;
  previewStatus.value = 'creating';

  try {
    const response = await axios.post(`/generation/${props.generationId}/preview/setup`);
    if (response.data.success) {
      previewUrl.value = response.data.preview_url || null;
      previewStatus.value = (response.data.status as PreviewStatus) || 'creating';

      // If not yet running, poll for status
      if (previewStatus.value !== 'running') {
        startStatusPolling();
      }
    } else {
      previewError.value = response.data.error || 'Setup failed';
      previewStatus.value = 'error';
    }
  } catch (error: any) {
    previewError.value = error.response?.data?.error || error.message || 'Setup failed';
    previewStatus.value = 'error';
  } finally {
    isSettingUp.value = false;
  }
}

async function stopPreview() {
  try {
    await axios.post(`/generation/${props.generationId}/preview/stop`);
    previewStatus.value = 'stopped';
    previewUrl.value = null;
    stopStatusPolling();
  } catch (error: any) {
    console.error('Stop preview error:', error);
  }
}

function startStatusPolling() {
  stopStatusPolling();
  statusPollingInterval.value = setInterval(async () => {
    try {
      const response = await axios.get(`/generation/${props.generationId}/preview/status`);
      const status = response.data.status as PreviewStatus;
      previewStatus.value = status;

      if (status === 'running') {
        previewUrl.value = response.data.preview_url || `/generation/${props.generationId}/preview/proxy`;
        stopStatusPolling();
      } else if (status === 'error' || status === 'stopped') {
        previewError.value = response.data.error || null;
        stopStatusPolling();
      }
    } catch {
      // Ignore polling errors
    }
  }, 3000);
}

function stopStatusPolling() {
  if (statusPollingInterval.value) {
    clearInterval(statusPollingInterval.value);
    statusPollingInterval.value = null;
  }
}

// Check initial status on mount for framework output
async function checkInitialStatus() {
  if (!isFramework.value || !props.isCompleted) return;
  try {
    const response = await axios.get(`/generation/${props.generationId}/preview/status`);
    if (response.data.status === 'running') {
      previewStatus.value = 'running';
      previewUrl.value = response.data.preview_url || `/generation/${props.generationId}/preview/proxy`;
    } else if (response.data.status === 'installing' || response.data.status === 'creating') {
      previewStatus.value = response.data.status as PreviewStatus;
      startStatusPolling();
    }
  } catch {
    // No active session
  }
}

// Auto-check status on mount
if (isFramework.value && props.isCompleted) {
  checkInitialStatus();
}

// Computed iframe source for framework preview
const frameworkIframeSrc = computed(() => {
  if (previewStatus.value === 'running' && previewUrl.value) {
    return previewUrl.value;
  }
  return '';
});

// Effective iframe source
const effectiveIframeSrc = computed(() => {
  if (isFramework.value) {
    return frameworkIframeSrc.value;
  }
  return staticIframeSrc.value;
});

// Has content to show
const hasPreview = computed(() => {
  if (isFramework.value) {
    return previewStatus.value === 'running' && !!previewUrl.value;
  }
  return !!props.pageContent;
});

// Status labels
const statusLabel = computed(() => {
  const isEn = currentLang.value === 'en';
  switch (previewStatus.value) {
    case 'creating': return isEn ? 'Creating workspace...' : 'Membuat workspace...';
    case 'installing': return isEn ? 'Installing dependencies...' : 'Menginstall dependensi...';
    case 'running': return isEn ? 'Preview running' : 'Preview berjalan';
    case 'stopped': return isEn ? 'Preview stopped' : 'Preview dihentikan';
    case 'error': return previewError.value || (isEn ? 'Preview error' : 'Error preview');
    default: return isEn ? 'Ready to preview' : 'Siap preview';
  }
});

const statusColor = computed(() => {
  switch (previewStatus.value) {
    case 'creating':
    case 'installing': return 'text-amber-600 dark:text-amber-400';
    case 'running': return 'text-green-600 dark:text-green-400';
    case 'error': return 'text-red-600 dark:text-red-400';
    default: return 'text-slate-500 dark:text-slate-400';
  }
});

// Cleanup on unmount
onUnmounted(() => {
  stopStatusPolling();
});

defineExpose({ setupPreview, stopPreview, checkInitialStatus });
</script>

<template>
  <div class="h-full flex flex-col">
    <!-- Framework Preview Toolbar -->
    <div v-if="isFramework" class="flex items-center justify-between px-3 py-1.5 bg-slate-100 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-800 flex-shrink-0">
      <div class="flex items-center gap-2">
        <!-- Status indicator -->
        <div class="flex items-center gap-1.5">
          <div
            :class="[
              'w-2 h-2 rounded-full',
              previewStatus === 'running' ? 'bg-green-500' :
              previewStatus === 'creating' || previewStatus === 'installing' ? 'bg-amber-500 animate-pulse' :
              previewStatus === 'error' ? 'bg-red-500' :
              'bg-slate-400'
            ]"
          ></div>
          <span :class="['text-xs font-medium', statusColor]">{{ statusLabel }}</span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <!-- Device switcher -->
        <div class="flex items-center bg-slate-200 dark:bg-slate-800 rounded-md p-0.5">
          <button
            v-for="mode in (['desktop', 'tablet', 'mobile'] as DeviceMode[])"
            :key="mode"
            @click="deviceMode = mode"
            :class="[
              'p-1.5 rounded transition-colors',
              deviceMode === mode
                ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600 dark:text-blue-400'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'
            ]"
            :title="mode"
          >
            <!-- Desktop icon -->
            <svg v-if="mode === 'desktop'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <!-- Tablet icon -->
            <svg v-else-if="mode === 'tablet'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <!-- Mobile icon -->
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </button>
        </div>

        <!-- Start/Stop buttons -->
        <button
          v-if="previewStatus !== 'running' && isCompleted"
          @click="setupPreview"
          :disabled="isSettingUp || previewStatus === 'creating' || previewStatus === 'installing'"
          class="px-3 py-1 text-xs font-medium bg-green-600 hover:bg-green-700 disabled:bg-slate-400 text-white rounded-md transition-colors flex items-center gap-1.5"
        >
          <svg v-if="isSettingUp || previewStatus === 'creating' || previewStatus === 'installing'" class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ currentLang === 'en' ? 'Start Preview' : 'Mulai Preview' }}
        </button>
        <button
          v-if="previewStatus === 'running'"
          @click="stopPreview"
          class="px-3 py-1 text-xs font-medium bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors flex items-center gap-1.5"
        >
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
            <rect x="6" y="6" width="12" height="12" rx="1" />
          </svg>
          {{ currentLang === 'en' ? 'Stop' : 'Hentikan' }}
        </button>
      </div>
    </div>

    <!-- Device switcher for static HTML (non-framework) -->
    <div v-if="!isFramework && hasPreview" class="flex items-center justify-end px-3 py-1.5 bg-slate-100 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-800 flex-shrink-0">
      <div class="flex items-center bg-slate-200 dark:bg-slate-800 rounded-md p-0.5">
        <button
          v-for="mode in (['desktop', 'tablet', 'mobile'] as DeviceMode[])"
          :key="mode"
          @click="deviceMode = mode"
          :class="[
            'p-1.5 rounded transition-colors',
            deviceMode === mode
              ? 'bg-white dark:bg-slate-700 shadow-sm text-blue-600 dark:text-blue-400'
              : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'
          ]"
          :title="mode"
        >
          <svg v-if="mode === 'desktop'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          <svg v-else-if="mode === 'tablet'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Preview Content -->
    <div class="flex-1 overflow-hidden relative flex items-start justify-center bg-slate-200/50 dark:bg-slate-900/50">
      <!-- Iframe container with device sizing -->
      <div
        v-if="hasPreview"
        class="h-full transition-all duration-300 bg-white"
        :style="{
          width: deviceWidths[deviceMode],
          maxWidth: '100%',
          boxShadow: deviceMode !== 'desktop' ? '0 0 40px rgba(0,0,0,0.1)' : 'none',
          margin: deviceMode !== 'desktop' ? '0 auto' : '0',
        }"
      >
        <iframe
          v-if="effectiveIframeSrc"
          :src="effectiveIframeSrc"
          class="w-full h-full border-0"
          sandbox="allow-scripts allow-same-origin allow-forms allow-popups"
        ></iframe>
      </div>

      <!-- Empty state: generating -->
      <div v-else-if="isGenerating" class="h-full w-full flex items-center justify-center">
        <div class="text-center">
          <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-slate-500 dark:text-slate-500 text-sm">
            {{ currentLang === 'en' ? 'Generating preview...' : 'Membuat preview...' }}
          </p>
        </div>
      </div>

      <!-- Empty state: framework not started -->
      <div v-else-if="isFramework && isCompleted && previewStatus === 'idle'" class="h-full w-full flex items-center justify-center">
        <div class="text-center max-w-sm">
          <div class="w-16 h-16 mx-auto mb-4 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center">
            <svg class="w-8 h-8 text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-base font-semibold text-slate-700 dark:text-slate-300 mb-1">
            {{ currentLang === 'en' ? 'Live Preview Ready' : 'Live Preview Siap' }}
          </h3>
          <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
            {{ currentLang === 'en'
              ? 'Click "Start Preview" to set up the development server and see your app running.'
              : 'Klik "Mulai Preview" untuk menyiapkan server pengembangan dan melihat aplikasi Anda berjalan.'
            }}
          </p>
          <button
            @click="setupPreview"
            :disabled="isSettingUp"
            class="px-5 py-2.5 bg-green-600 hover:bg-green-700 disabled:bg-slate-400 text-white rounded-lg transition-colors text-sm font-medium flex items-center gap-2 mx-auto"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ currentLang === 'en' ? 'Start Preview' : 'Mulai Preview' }}
          </button>
        </div>
      </div>

      <!-- Empty state: framework setting up -->
      <div v-else-if="isFramework && (previewStatus === 'creating' || previewStatus === 'installing')" class="h-full w-full flex items-center justify-center">
        <div class="text-center max-w-sm">
          <div class="w-12 h-12 border-4 border-amber-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <h3 class="text-base font-semibold text-slate-700 dark:text-slate-300 mb-1">
            {{ previewStatus === 'creating'
              ? (currentLang === 'en' ? 'Creating workspace...' : 'Membuat workspace...')
              : (currentLang === 'en' ? 'Installing dependencies...' : 'Menginstall dependensi...')
            }}
          </h3>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ currentLang === 'en'
              ? 'This may take a minute. Setting up your project environment.'
              : 'Ini mungkin memakan waktu satu menit. Menyiapkan lingkungan proyek Anda.'
            }}
          </p>
        </div>
      </div>

      <!-- Empty state: error -->
      <div v-else-if="previewStatus === 'error'" class="h-full w-full flex items-center justify-center">
        <div class="text-center max-w-sm">
          <div class="w-16 h-16 mx-auto mb-4 bg-red-100 dark:bg-red-900/20 rounded-2xl flex items-center justify-center">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <h3 class="text-base font-semibold text-red-700 dark:text-red-400 mb-1">
            {{ currentLang === 'en' ? 'Preview Error' : 'Error Preview' }}
          </h3>
          <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ previewError }}</p>
          <button
            @click="setupPreview"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium"
          >
            {{ currentLang === 'en' ? 'Retry' : 'Coba Lagi' }}
          </button>
        </div>
      </div>

      <!-- Empty state: no content yet -->
      <div v-else class="h-full w-full flex items-center justify-center">
        <div class="text-center">
          <svg class="w-12 h-12 text-slate-400 dark:text-slate-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <p class="text-slate-500 dark:text-slate-500 text-sm">
            {{ currentLang === 'en' ? 'Preview will appear here' : 'Preview akan muncul di sini' }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
