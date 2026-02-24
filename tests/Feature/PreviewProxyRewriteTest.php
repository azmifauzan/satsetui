<?php

use App\Http\Controllers\PreviewController;
use App\Services\WorkspaceService;

it('rewrites absolute vite asset urls to proxy base', function () {
    $controller = new PreviewController(app(WorkspaceService::class));

    $reflection = new ReflectionMethod($controller, 'rewriteProxyAssetUrls');
    $reflection->setAccessible(true);

    $base = '/generation/6/preview/proxy';
    $html = <<<'HTML'
<!doctype html>
<html>
  <head>
    <script type="module" src="/@vite/client"></script>
  </head>
  <body>
    <script type="module" src="/src/main.ts"></script>
  </body>
</html>
HTML;

    $rewritten = $reflection->invoke($controller, $html, 'text/html; charset=utf-8', $base);

    expect($rewritten)->toContain('/generation/6/preview/proxy/@vite/client');
    expect($rewritten)->toContain('/generation/6/preview/proxy/src/main.ts');
    expect($rewritten)->not->toContain('src="/@vite/client"');
    expect($rewritten)->not->toContain('src="/src/main.ts"');
});

it('does not rewrite javascript module internals for @id helper imports', function () {
    $controller = new PreviewController(app(WorkspaceService::class));
    $reflection = new ReflectionMethod($controller, 'rewriteProxyAssetUrls');
    $reflection->setAccessible(true);

    $base = '/generation/6/preview/proxy';
    $js = 'import helper from "/@id/__x00__plugin-vue:export-helper";';

    $rewritten = $reflection->invoke($controller, $js, 'text/javascript', $base);

    expect($rewritten)->toContain('"/@id/__x00__plugin-vue:export-helper"');
    expect($rewritten)->not->toContain('/generation/6/preview/proxy/@id/__x00__plugin-vue:export-helper');
});

it('does not rewrite javascript module internals for bare __x00__ helper imports', function () {
    $controller = new PreviewController(app(WorkspaceService::class));
    $reflection = new ReflectionMethod($controller, 'rewriteProxyAssetUrls');
    $reflection->setAccessible(true);

    $base = '/generation/6/preview/proxy';
    $js = 'import helper from "/__x00__plugin-vue:export-helper";';

    $rewritten = $reflection->invoke($controller, $js, 'text/javascript', $base);

    expect($rewritten)->toContain('"/__x00__plugin-vue:export-helper"');
    expect($rewritten)->not->toContain('/generation/6/preview/proxy/__x00__plugin-vue:export-helper');
});

it('removes vite client script from proxied html', function () {
    $controller = new PreviewController(app(WorkspaceService::class));
    $reflection = new ReflectionMethod($controller, 'removeViteClientScript');
    $reflection->setAccessible(true);

    $html = <<<'HTML'
<!doctype html>
<html>
  <head>
    <script type="module" src="/@vite/client"></script>
  </head>
  <body>
    <script type="module" src="/src/main.ts"></script>
  </body>
</html>
HTML;

    $result = $reflection->invoke($controller, $html);

    expect($result)->not->toContain('/@vite/client');
    expect($result)->toContain('/src/main.ts');
});
