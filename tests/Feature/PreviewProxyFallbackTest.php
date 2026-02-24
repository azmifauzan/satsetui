<?php

use App\Http\Controllers\PreviewController;
use App\Services\WorkspaceService;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;

it('retries without base for 404 asset responses', function () {
    $controller = new PreviewController(app(WorkspaceService::class));
    $method = new ReflectionMethod($controller, 'shouldRetryProxyWithoutBase');
    $method->setAccessible(true);

    $response = new Response(new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'text/plain'], 'Not Found'));

    $shouldRetry = $method->invoke($controller, $response, 'src/main.ts');

    expect($shouldRetry)->toBeTrue();
});

it('retries without base for html returned on module endpoint', function () {
    $controller = new PreviewController(app(WorkspaceService::class));
    $method = new ReflectionMethod($controller, 'shouldRetryProxyWithoutBase');
    $method->setAccessible(true);

    $response = new Response(new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'text/html; charset=UTF-8'], '<html>Login</html>'));

    $shouldRetry = $method->invoke($controller, $response, '@vite/client');

    expect($shouldRetry)->toBeTrue();
});

it('retries without base for html returned on __x00__ helper endpoint', function () {
    $controller = new PreviewController(app(WorkspaceService::class));
    $method = new ReflectionMethod($controller, 'shouldRetryProxyWithoutBase');
    $method->setAccessible(true);

    $response = new Response(new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'text/html; charset=UTF-8'], '<html>fallback</html>'));

    $shouldRetry = $method->invoke($controller, $response, '__x00__plugin-vue:export-helper');

    expect($shouldRetry)->toBeTrue();
});

it('retries without base for server error on vue module path', function () {
    $controller = new PreviewController(app(WorkspaceService::class));
    $method = new ReflectionMethod($controller, 'shouldRetryProxyWithoutBase');
    $method->setAccessible(true);

    $response = new Response(new \GuzzleHttp\Psr7\Response(500, ['Content-Type' => 'text/plain; charset=UTF-8'], 'ENOENT: no such file or directory'));

    $shouldRetry = $method->invoke($controller, $response, 'src/pages/Home.vue');

    expect($shouldRetry)->toBeTrue();
});

it('does not retry without base for root html request', function () {
    $controller = new PreviewController(app(WorkspaceService::class));
    $method = new ReflectionMethod($controller, 'shouldRetryProxyWithoutBase');
    $method->setAccessible(true);

    $response = new Response(new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'text/html; charset=UTF-8'], '<html></html>'));

    $shouldRetry = $method->invoke($controller, $response, '');

    expect($shouldRetry)->toBeFalse();
});

it('normalizes vite flag query parameters before forwarding', function () {
    $controller = new PreviewController(app(WorkspaceService::class));
    $method = new ReflectionMethod($controller, 'normalizeViteFlagQueryString');
    $method->setAccessible(true);

    $normalized = $method->invoke($controller, 'vue=&type=style&index=0&scoped=abc&lang.css=');

    expect($normalized)->toBe('vue&type=style&index=0&scoped=abc&lang.css');
});

it('prefers raw server query string for proxy forwarding', function () {
    $controller = new PreviewController(app(WorkspaceService::class));
    $method = new ReflectionMethod($controller, 'getProxyForwardQueryString');
    $method->setAccessible(true);

    $request = Request::create('/demo?vue=&lang.css=&type=style', 'GET');
    $forwarded = $method->invoke($controller, $request);

    expect($forwarded)->toBe('vue&lang.css&type=style');
});
