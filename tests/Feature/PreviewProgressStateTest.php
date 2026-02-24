<?php

use App\Http\Controllers\PreviewController;
use App\Services\WorkspaceService;

it('keeps stopped progress state even if stale boot logs exist', function () {
    $controller = new PreviewController(app(WorkspaceService::class));

    $reflection = new ReflectionMethod($controller, 'resolveProgress');
    $reflection->setAccessible(true);

    $result = $reflection->invoke($controller, 'stopped', [
        '[setup] Starting dev server...',
        '[vite] VITE v5.4.21 ready in 11473 ms',
        '[vite] Local: http://127.0.0.1:3100/',
    ]);

    expect($result['phase'])->toBe('stopped');
    expect($result['percentage'])->toBe(100);
    expect($result['detail'])->toBe('Preview stopped');
});
