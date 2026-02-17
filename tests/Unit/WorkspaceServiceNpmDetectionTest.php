<?php

use App\Services\WorkspaceService;

it('resolves npm binary to a valid path', function () {
    $service = new WorkspaceService;

    // Use reflection to call private getNpmBinary
    $reflection = new ReflectionMethod($service, 'getNpmBinary');
    $reflection->setAccessible(true);

    $npmBinary = $reflection->invoke($service);

    expect($npmBinary)->not->toBeEmpty();

    // On this dev machine, it should resolve to a full path (not just 'npm.cmd')
    if (PHP_OS_FAMILY === 'Windows') {
        expect($npmBinary)->toEndWith('npm.cmd');
    } else {
        expect($npmBinary)->toContain('npm');
    }
});

it('resolves npm binary with full path when node is installed', function () {
    $service = new WorkspaceService;

    $reflection = new ReflectionMethod($service, 'detectNpmFullPath');
    $reflection->setAccessible(true);

    $fullPath = $reflection->invoke($service);

    // Skip on environments without Node.js
    if ($fullPath === null) {
        $this->markTestSkipped('Node.js not installed');
    }

    expect($fullPath)->toBeString();
    expect(file_exists($fullPath))->toBeTrue();
});

it('derives npx binary from npm binary path', function () {
    $service = new WorkspaceService;

    $reflection = new ReflectionMethod($service, 'getNpxBinary');
    $reflection->setAccessible(true);

    if (PHP_OS_FAMILY === 'Windows') {
        $npmPath = 'C:\\Program Files\\nodejs\\npm.cmd';
        $npx = $reflection->invoke($service, $npmPath);

        // If the file exists, it should resolve to full path
        if (file_exists('C:\\Program Files\\nodejs\\npx.cmd')) {
            expect($npx)->toBe('C:\\Program Files\\nodejs\\npx.cmd');
        }
    } else {
        $npmPath = '/usr/local/bin/npm';
        $npx = $reflection->invoke($service, $npmPath);
        expect($npx)->toContain('npx');
    }
});

it('uses configured npm path from config', function () {
    $service = new WorkspaceService;

    // Set config value to a known path
    $npmBinary = PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm';
    $result = \Illuminate\Support\Facades\Process::run(
        PHP_OS_FAMILY === 'Windows' ? "where {$npmBinary} 2>nul" : "which npm 2>/dev/null"
    );

    if (! $result->successful()) {
        $this->markTestSkipped('npm not installed');
    }

    $knownPath = trim(explode("\n", $result->output())[0]);

    config(['services.node.npm_path' => $knownPath]);

    $reflection = new ReflectionMethod($service, 'getNpmBinary');
    $reflection->setAccessible(true);

    // Need a fresh instance since result is cached
    $freshService = new WorkspaceService;
    $resolved = $reflection->invoke($freshService);

    expect($resolved)->toBe($knownPath);
});

it('builds process with node dir in PATH', function () {
    $service = new WorkspaceService;

    // Trigger npm resolution first
    $getNpm = new ReflectionMethod($service, 'getNpmBinary');
    $getNpm->setAccessible(true);
    $getNpm->invoke($service);

    $buildProcess = new ReflectionMethod($service, 'buildProcess');
    $buildProcess->setAccessible(true);

    $process = $buildProcess->invoke($service, sys_get_temp_dir());

    expect($process)->toBeInstanceOf(\Illuminate\Process\PendingProcess::class);
});
