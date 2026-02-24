<?php

use App\Http\Controllers\PreviewController;
use App\Services\WorkspaceService;

it('returns a no-op vite client stub module', function () {
    $controller = new PreviewController(app(WorkspaceService::class));

    $method = new ReflectionMethod($controller, 'viteClientStubModule');
    $method->setAccessible(true);

    $stub = $method->invoke($controller);

    expect($stub)->toContain('export function createHotContext()');
    expect($stub)->toContain('export function updateStyle() {}');
    expect($stub)->toContain('export function removeStyle() {}');
    expect($stub)->toContain('export const ErrorOverlay');
});
