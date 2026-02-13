<script setup lang="ts">
import { useI18n } from '@/lib/i18n';

const { currentLang } = useI18n();

const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="bg-slate-900 dark:bg-slate-950 text-slate-400 py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
        <!-- Brand -->
        <div class="md:col-span-2">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">S</span>
            </div>
            <span class="text-xl font-bold text-white">SatsetUI</span>
          </div>
          <p class="text-slate-400 leading-relaxed max-w-md">
            {{ currentLang === 'id' 
              ? 'Wizard-driven UI template generator. Tanpa prompt, tanpa drag-and-drop. Pilih, kustomisasi, generate — sat-set!' 
              : 'Wizard-driven UI template generator. No prompts, no drag-and-drop. Select, customize, generate — sat-set!' }}
          </p>
        </div>

        <!-- Product -->
        <div>
          <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">
            {{ currentLang === 'id' ? 'Produk' : 'Product' }}
          </h3>
          <ul class="space-y-3">
            <li>
              <a href="/wizard" class="hover:text-white transition-colors">Wizard</a>
            </li>
            <li>
              <a href="#features" class="hover:text-white transition-colors">
                {{ currentLang === 'id' ? 'Fitur' : 'Features' }}
              </a>
            </li>
            <li>
              <a href="#faq" class="hover:text-white transition-colors">FAQ</a>
            </li>
          </ul>
        </div>

        <!-- Legal  -->
        <div>
          <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">
            {{ currentLang === 'id' ? 'Legal' : 'Legal' }}
          </h3>
          <ul class="space-y-3">
            <li>
              <a href="#" class="hover:text-white transition-colors">
                {{ currentLang === 'id' ? 'Syarat Layanan' : 'Terms of Service' }}
              </a>
            </li>
            <li>
              <a href="#" class="hover:text-white transition-colors">
                {{ currentLang === 'id' ? 'Kebijakan Privasi' : 'Privacy Policy' }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom bar -->
      <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm text-slate-500">
          &copy; {{ currentYear }} SatsetUI. {{ currentLang === 'id' ? 'Hak cipta dilindungi.' : 'All rights reserved.' }}
        </p>
        <p class="text-sm text-slate-500">
          {{ currentLang === 'id' ? 'Dibuat dengan' : 'Built with' }} 
          <span class="text-red-400">&hearts;</span> 
          {{ currentLang === 'id' ? 'di Indonesia' : 'in Indonesia' }}
        </p>
      </div>
    </div>
  </footer>
</template>
