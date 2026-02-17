<?php

use App\Services\WorkspaceService;

it('resolves npm binary to a valid path', function () {
    $service = app(WorkspaceService::class);

    $reflection = new ReflectionMethod($service, 'getNpmBinary');

    $npmBinary = $reflection->invoke($service);

    expect($npmBinary)->not->toBeEmpty();

    if (PHP_OS_FAMILY === 'Windows') {
        expect($npmBinary)->toEndWith('npm.cmd');
    } else {
        expect($npmBinary)->toContain('npm');
    }
});

it('detects npm full path when node is installed', function () {
    $service = app(WorkspaceService::class);

    $reflection = new ReflectionMethod($service, 'detectNpmFullPath');

    $fullPath = $reflection->invoke($service);

    if ($fullPath === null) {
        $this->markTestSkipped('Node.js not installed on this machine');
    }

    expect($fullPath)->toBeString();
    expect(file_exists($fullPath))->toBeTrue();
});

it('derives npx binary from npm binary path', function () {
    $service = app(WorkspaceService::class);

    $reflection = new ReflectionMethod($service, 'getNpxBinary');

    if (PHP_OS_FAMILY === 'Windows') {
        $npmPath = 'C:\\Program Files\\nodejs\\npm.cmd';
        $npx = $reflection->invoke($service, $npmPath);

        if (file_exists('C:\\Program Files\\nodejs\\npx.cmd')) {
            expect($npx)->toBe('C:\\Program Files\\nodejs\\npx.cmd');
        } else {
            expect($npx)->toEndWith('npx.cmd');
        }
    } else {
        $npmPath = '/usr/local/bin/npm';
        $npx = $reflection->invoke($service, $npmPath);
        expect($npx)->toContain('npx');
    }
});

it('uses configured npm path from env config', function () {
    $npmBinary = PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm';
    $result = Illuminate\Support\Facades\Process::run(
        PHP_OS_FAMILY === 'Windows' ? "where {$npmBinary} 2>nul" : "which npm 2>/dev/null"
    );

    if (! $result->successful()) {
        $this->markTestSkipped('npm not installed');
    }

    $knownPath = trim(explode("\n", $result->output())[0]);

    config(['services.node.npm_path' => $knownPath]);

    $service = new WorkspaceService;
    $reflection = new ReflectionMethod($service, 'getNpmBinary');
    $resolved = $reflection->invoke($service);

    expect($resolved)->toBe($knownPath);
});

it('builds safe command with SYSTEMROOT for Windows', function () {
    $service = app(WorkspaceService::class);

    // Trigger npm resolution
    $getNpm = new ReflectionMethod($service, 'getNpmBinary');
    $npm = $getNpm->invoke($service);

    $buildCmd = new ReflectionMethod($service, 'buildSafeCommand');
    $cmd = $buildCmd->invoke($service, "\"{$npm}\" --version");

    expect($cmd)->toBeString()->not->toBeEmpty();

    if (PHP_OS_FAMILY === 'Windows') {
        // Must include SYSTEMROOT
        expect($cmd)->toContain('SYSTEMROOT');
    }

    // The command should actually work
    $result = Illuminate\Support\Facades\Process::path(sys_get_temp_dir())->run($cmd);
    expect($result->successful())->toBeTrue();
    expect(trim($result->output()))->toMatch('/^\d+\.\d+\.\d+$/');
});
