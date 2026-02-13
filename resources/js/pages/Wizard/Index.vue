<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import WizardLayout from '@/wizard/WizardLayout.vue';
import WizardNavigation from '@/wizard/WizardNavigation.vue';
import { wizardState, blueprintJSON } from '@/wizard/wizardState';
import { useI18n } from '@/lib/i18n';
import { useTheme } from '@/lib/theme';
import LandingNavbar from '@/components/landing/Navbar.vue';

interface LlmModel {
  id: string;
  name: string;
  description: string;
  credits_required: number;
}

interface Props {
  models: LlmModel[];
  userCredits: number;
}

const props = defineProps<Props>();

const page = usePage();
const auth = computed(() => page.props.auth as { user?: { name: string; email: string } } | undefined);
const isAuthenticated = computed(() => !!auth.value?.user);

const { t, currentLang, setLang } = useI18n();
const { isDark, toggleTheme } = useTheme();

const isGenerating = ref(false);
const showLoginPrompt = ref(false);
const showBlueprintPreview = ref(false);
const generationError = ref<string | null>(null);

// Restore wizard state from sessionStorage (after login/register redirect)
onMounted(() => {
  const saved = sessionStorage.getItem('satsetui-wizard-state');
  if (saved) {
    try {
      const parsed = JSON.parse(saved);
      Object.keys(parsed).forEach((key) => {
        if (key in wizardState && key !== 'logoFile') {
          (wizardState as any)[key] = parsed[key];
        }
      });
      sessionStorage.removeItem('satsetui-wizard-state');
    } catch (e) {
      // Ignore parse errors
    }
  }
});

async function handleGenerate() {
  // Check if user is authenticated
  if (!isAuthenticated.value) {
    showLoginPrompt.value = true;
    return;
  }

  isGenerating.value = true;
  generationError.value = null;
  
  try {
    const blueprint = blueprintJSON.value;
    const projectName = `Satset ${wizardState.category} ${new Date().toLocaleString()}`;
    
    // Just create the generation record, actual generation happens via SSE on the preview page
    const response = await axios.post('/generation/generate', {
      blueprint,
      project_name: projectName,
      model_name: wizardState.llmModel,
    }, { timeout: 30000 });

    const result = response.data;
    if (!result.success) throw new Error(result.error || 'Generation failed');

    // Immediately redirect to preview page - SSE streaming will handle the rest
    router.visit(`/generation/${result.generation_id}`);
  } catch (error) {
    let errorMessage = 'An unexpected error occurred.';
    if (axios.isAxiosError(error)) {
      if (error.response?.status === 401 || error.response?.status === 419) {
        showLoginPrompt.value = true;
        isGenerating.value = false;
        return;
      }
      errorMessage = error.response?.data?.error || `Server error: ${error.response?.status}`;
    } else if (error instanceof Error) {
      errorMessage = error.message;
    }
    generationError.value = errorMessage;
    isGenerating.value = false;
  }
}

function goToLogin() {
  // Save wizard state to sessionStorage so it persists across login
  sessionStorage.setItem('satsetui-wizard-state', JSON.stringify(wizardState));
  router.visit('/login?redirect=/wizard');
}

function goToRegister() {
  sessionStorage.setItem('satsetui-wizard-state', JSON.stringify(wizardState));
  router.visit('/register?redirect=/wizard');
}

const scrollToSection = (sectionId: string) => {
  const el = document.getElementById(sectionId);
  if (el) el.scrollIntoView({ behavior: 'smooth' });
};

const toggleLang = () => {
  setLang(currentLang.value === 'id' ? 'en' : 'id');
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900">
    <Head :title="currentLang === 'en' ? 'SatsetUI Wizard' : 'Wizard SatsetUI'" />

    <!-- Use landing navbar for non-authenticated, simplified for authenticated -->
    <LandingNavbar
      v-if="!isAuthenticated"
      :auth="auth"
      :isDark="isDark"
      @toggle-theme="toggleTheme"
      @toggle-lang="toggleLang"
      @scroll-to-section="scrollToSection"
    />

    <!-- Simple top bar for authenticated users -->
    <nav v-else class="sticky top-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-b border-slate-200 dark:border-slate-700">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <a href="/" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
              </svg>
            </div>
            <span class="text-xl font-bold text-slate-900 dark:text-white">Satset<span class="text-blue-600">UI</span></span>
          </a>
          <div class="flex items-center gap-3">
            <a href="/dashboard" class="text-sm text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400">Dashboard</a>
            <button @click="toggleTheme" class="p-2 text-slate-500 dark:text-slate-400 hover:text-blue-600 rounded-lg">
              <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
              </svg>
            </button>
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
              {{ auth?.user?.name?.charAt(0).toUpperCase() }}
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Wizard Content -->
    <div :class="[!isAuthenticated ? 'pt-16' : '']">
      <!-- Blueprint Preview Toggle -->
      <button
        @click="showBlueprintPreview = !showBlueprintPreview"
        class="fixed top-20 right-6 z-30 px-3 py-1.5 bg-slate-900 dark:bg-slate-700 text-white rounded-lg shadow-lg hover:bg-slate-800 dark:hover:bg-slate-600 transition-colors text-xs font-medium opacity-50 hover:opacity-100"
      >
        {{ showBlueprintPreview ? 'Hide JSON' : 'Show JSON' }}
      </button>

      <!-- Blueprint Preview Sidebar -->
      <div v-if="showBlueprintPreview" class="fixed right-0 top-16 bottom-0 w-96 bg-slate-900 dark:bg-slate-950 border-l border-slate-700 z-20 overflow-auto shadow-2xl">
        <div class="p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">Blueprint JSON</h3>
            <button @click="showBlueprintPreview = false" class="p-1 hover:bg-slate-800 rounded transition-colors">
              <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>
          <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap break-words bg-slate-950 dark:bg-black p-4 rounded-lg overflow-auto">{{ JSON.stringify(blueprintJSON, null, 2) }}</pre>
        </div>
      </div>

      <!-- Main Content -->
      <div :class="['transition-all duration-300', showBlueprintPreview ? 'mr-96' : 'mr-0']">
        <WizardLayout @submit="handleGenerate" />
        <WizardNavigation
          @submit="handleGenerate"
          :models="props.models"
          :user-credits="props.userCredits"
        />
      </div>
    </div>

    <!-- Login Prompt Modal -->
    <div v-if="showLoginPrompt" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-2xl max-w-md w-full">
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
            {{ currentLang === 'en' ? 'Sign in to Generate' : 'Masuk untuk Generate' }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 mb-6 text-sm">
            {{ currentLang === 'en' ? 'You need an account to generate your project. Your wizard configuration will be saved.' : 'Kamu perlu akun untuk generate proyek. Konfigurasi wizard akan disimpan.' }}
          </p>
          <div class="space-y-3">
            <button @click="goToLogin" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg font-semibold transition-all shadow-lg">
              {{ currentLang === 'en' ? 'Sign In' : 'Masuk' }}
            </button>
            <button @click="goToRegister" class="w-full px-6 py-3 border-2 border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
              {{ currentLang === 'en' ? 'Create Account' : 'Buat Akun' }}
            </button>
            <button @click="showLoginPrompt = false" class="w-full px-6 py-2 text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
              {{ currentLang === 'en' ? 'Cancel' : 'Batal' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay - Brief redirect -->
    <div v-if="isGenerating" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-8 shadow-2xl max-w-sm w-full mx-4">
        <div class="flex flex-col items-center">
          <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-white">
            {{ currentLang === 'en' ? 'Preparing workspace...' : 'Menyiapkan workspace...' }}
          </h3>
        </div>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="generationError" class="fixed bottom-6 right-6 z-50 max-w-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 shadow-lg">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <div class="flex-1">
          <h4 class="font-medium text-red-900 dark:text-red-200">{{ currentLang === 'en' ? 'Generation Failed' : 'Generasi Gagal' }}</h4>
          <p class="text-sm text-red-700 dark:text-red-300 mt-1">{{ generationError }}</p>
        </div>
        <button @click="generationError = null" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>
    </div>
  </div>
</template>
