<?php

use App\Services\WorkspaceService;
use Illuminate\Support\Facades\File;

it('normalizes mismatched index entry script and mount id', function () {
    $workspaceDir = storage_path('app/workspaces/test-preview-normalize-'.uniqid());
    File::makeDirectory($workspaceDir.DIRECTORY_SEPARATOR.'src', 0755, true);

    File::put($workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'main.ts', <<<'TS'
import { createApp } from 'vue'
import App from './App.vue'

createApp(App).mount('#app')
TS);

    File::put($workspaceDir.DIRECTORY_SEPARATOR.'index.html', <<<'HTML'
<!DOCTYPE html>
<html>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>
HTML);

    $service = new WorkspaceService;
    $reflection = new ReflectionMethod($service, 'normalizePreviewEntrypointArtifacts');
    $reflection->setAccessible(true);
    $reflection->invoke($service, $workspaceDir);

    $normalizedIndex = File::get($workspaceDir.DIRECTORY_SEPARATOR.'index.html');

    expect($normalizedIndex)->toContain('/src/main.ts');
    expect($normalizedIndex)->toContain('id="app"');
    expect($normalizedIndex)->not->toContain('/src/main.tsx');
    expect($normalizedIndex)->not->toContain('id="root"');

    File::deleteDirectory($workspaceDir);
});

it('injects vite preview base when config has no base property', function () {
    $workspaceDir = storage_path('app/workspaces/test-vite-base-normalize-'.uniqid());
    File::makeDirectory($workspaceDir, 0755, true);

    File::put($workspaceDir.DIRECTORY_SEPARATOR.'vite.config.ts', <<<'TS'
  import { defineConfig } from 'vite'

  export default defineConfig({
    plugins: [],
  })
  TS);

    $service = new WorkspaceService;
    $reflection = new ReflectionMethod($service, 'normalizeVitePreviewBase');
    $reflection->setAccessible(true);
    $reflection->invoke($service, $workspaceDir);

    $viteConfig = File::get($workspaceDir.DIRECTORY_SEPARATOR.'vite.config.ts');

    expect($viteConfig)->toContain("base: process.env.VITE_PREVIEW_BASE || '/'");

    File::deleteDirectory($workspaceDir);
});

it('normalizes tailwind ring-primary utility usages for preview compatibility', function () {
    $workspaceDir = storage_path('app/workspaces/test-tailwind-preview-normalize-'.uniqid());
    File::makeDirectory($workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'pages', 0755, true);

    $vuePath = $workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'Contact.vue';
    File::put($vuePath, <<<'VUE'
  <style scoped>
  .input-focus {
    @apply ring-2 ring-primary ring-opacity-50 outline-none;
  }
  </style>
  VUE);

    $service = new WorkspaceService;
    $reflection = new ReflectionMethod($service, 'normalizeTailwindPreviewUtilities');
    $reflection->setAccessible(true);
    $reflection->invoke($service, $workspaceDir);

    $content = File::get($vuePath);

    expect($content)->toContain('ring-red-500');
    expect($content)->toContain('ring-red-500/50');
    expect($content)->not->toContain('ring-primary');
    expect($content)->not->toContain('ring-opacity-50');

    File::deleteDirectory($workspaceDir);
});

it('normalizes vue router to add default child redirect when missing', function () {
    $workspaceDir = storage_path('app/workspaces/test-vue-router-normalize-'.uniqid());
    File::makeDirectory($workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'router', 0755, true);

    File::put($workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'router'.DIRECTORY_SEPARATOR.'index.ts', <<<'TS'
import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: MainLayout,
      children: [
    {
      path: 'home',
      name: 'home',
      component: () => import('@/pages/Home.vue'),
      meta: { layout: 'main' },
    },
    {
      path: 'blog',
      name: 'blog',
      component: () => import('@/pages/Blog.vue'),
      meta: { layout: 'main' },
    },
      ],
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/home',
    },
  ],
})

export default router
TS);

    $service = new WorkspaceService;
    $reflection = new ReflectionMethod($service, 'normalizeVueRouterDefaultChild');
    $reflection->setAccessible(true);
    $reflection->invoke($service, $workspaceDir);

    $routerContent = File::get($workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'router'.DIRECTORY_SEPARATOR.'index.ts');

    expect($routerContent)->toContain("path: '', redirect: 'home'");

    File::deleteDirectory($workspaceDir);
});

it('does not add duplicate default child redirect if already present', function () {
    $workspaceDir = storage_path('app/workspaces/test-vue-router-no-dup-'.uniqid());
    File::makeDirectory($workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'router', 0755, true);

    $routerContent = <<<'TS'
import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: MainLayout,
      children: [
    {
      path: 'dashboard',
      name: 'dashboard',
      component: () => import('@/pages/Dashboard.vue'),
    },
    { path: '', redirect: 'dashboard' },
      ],
    },
  ],
})

export default router
TS;

    File::put($workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'router'.DIRECTORY_SEPARATOR.'index.ts', $routerContent);

    $service = new WorkspaceService;
    $reflection = new ReflectionMethod($service, 'normalizeVueRouterDefaultChild');
    $reflection->setAccessible(true);
    $reflection->invoke($service, $workspaceDir);

    $result = File::get($workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'router'.DIRECTORY_SEPARATOR.'index.ts');

    // Should remain unchanged — no duplicate redirect
    expect($result)->toBe($routerContent);

    File::deleteDirectory($workspaceDir);
});
