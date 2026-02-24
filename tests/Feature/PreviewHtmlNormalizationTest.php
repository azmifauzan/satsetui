<?php

use App\Http\Controllers\PreviewController;
use App\Services\WorkspaceService;
use Illuminate\Support\Facades\File;

it('normalizes proxied html entry script and mount id from workspace files', function () {
    $workspaceDir = storage_path('app/workspaces/test-proxy-html-normalize-'.uniqid());
    File::makeDirectory($workspaceDir.DIRECTORY_SEPARATOR.'src', 0755, true);

    File::put($workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'main.ts', <<<'TS'
import { createApp } from 'vue'
import App from './App.vue'

createApp(App).mount('#app')
TS);

    $html = <<<'HTML'
<!doctype html>
<html>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>
HTML;

    $controller = new PreviewController(app(WorkspaceService::class));
    $reflection = new ReflectionMethod($controller, 'normalizeProxyHtmlEntrypoint');
    $reflection->setAccessible(true);

    $normalized = $reflection->invoke($controller, $html, $workspaceDir);

    expect($normalized)->toContain('id="app"');
    expect($normalized)->toContain('src="/src/main.ts"');
    expect($normalized)->not->toContain('id="root"');
    expect($normalized)->not->toContain('src="/src/main.tsx"');

    File::deleteDirectory($workspaceDir);
});

it('normalization and rewrite keep module entry under proxy path', function () {
    $workspaceDir = storage_path('app/workspaces/test-proxy-html-normalize-'.uniqid());
    File::makeDirectory($workspaceDir.DIRECTORY_SEPARATOR.'src', 0755, true);

    File::put($workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'main.ts', <<<'TS'
import { createApp } from 'vue'
import App from './App.vue'

createApp(App).mount('#app')
TS);

    $html = <<<'HTML'
<!doctype html>
<html>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>
HTML;

    $controller = new PreviewController(app(WorkspaceService::class));

    $normalize = new ReflectionMethod($controller, 'normalizeProxyHtmlEntrypoint');
    $normalize->setAccessible(true);
    $normalized = $normalize->invoke($controller, $html, $workspaceDir);

    $rewrite = new ReflectionMethod($controller, 'rewriteProxyAssetUrls');
    $rewrite->setAccessible(true);
    $rewritten = $rewrite->invoke($controller, $normalized, 'text/html; charset=utf-8', '/generation/6/preview/proxy');

    expect($rewritten)->toContain('src="/generation/6/preview/proxy/src/main.ts"');
    expect($rewritten)->toContain('id="app"');
    expect($rewritten)->not->toContain('src="/src/main.ts"');

    File::deleteDirectory($workspaceDir);
});

it('preserves vite client script while normalizing app entry script', function () {
    $workspaceDir = storage_path('app/workspaces/test-proxy-html-normalize-'.uniqid());
    File::makeDirectory($workspaceDir.DIRECTORY_SEPARATOR.'src', 0755, true);

    File::put($workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'main.ts', <<<'TS'
import { createApp } from 'vue'
import App from './App.vue'

createApp(App).mount('#app')
TS);

    $html = <<<'HTML'
<!doctype html>
<html>
  <head>
    <script type="module" src="/@vite/client"></script>
  </head>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>
HTML;

    $controller = new PreviewController(app(WorkspaceService::class));

    $normalize = new ReflectionMethod($controller, 'normalizeProxyHtmlEntrypoint');
    $normalize->setAccessible(true);
    $normalized = $normalize->invoke($controller, $html, $workspaceDir);

    expect($normalized)->toContain('src="/@vite/client"');
    expect($normalized)->toContain('src="/src/main.ts"');
    expect($normalized)->not->toContain('src="/src/main.tsx"');

    File::deleteDirectory($workspaceDir);
});
