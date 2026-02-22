<?php

namespace App\Services;

use App\Jobs\SetupPreviewSession;
use App\Models\Generation;
use App\Models\GenerationFile;
use App\Models\PreviewSession;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * Workspace Service
 *
 * Manages server-side file workspaces for generated templates.
 * Handles workspace creation, dependency installation, dev server lifecycle,
 * and cleanup.
 *
 * Workspace Structure:
 *   storage/app/workspaces/gen-{id}/
 *     ├── package.json
 *     ├── vite.config.ts
 *     ├── src/
 *     │   ├── pages/
 *     │   ├── layouts/
 *     │   └── ...
 *     └── node_modules/ (after install)
 *
 * Port Management:
 *   Each workspace gets a unique port (3100-3999).
 *   Ports are tracked in preview_sessions table.
 */
class WorkspaceService
{
    /** Base directory for all workspaces */
    private string $baseDir;

    /** Port range for dev servers */
    private const PORT_MIN = 3100;

    private const PORT_MAX = 3999;

    /** Timeout for npm install (seconds) */
    private const INSTALL_TIMEOUT = 120;

    /** Timeout for inactivity before auto-stop (minutes) */
    private const INACTIVITY_TIMEOUT = 30;

    /** Resolved npm binary path (cached) */
    private ?string $resolvedNpmBinary = null;

    /** Resolved node binary directory for PATH (cached) */
    private ?string $resolvedNodeDir = null;

    public function __construct()
    {
        $this->baseDir = storage_path('app/workspaces');
    }

    /**
     * Create a workspace from generation files.
     *
     * Writes all GenerationFile records to the workspace directory.
     *
     * @return string Workspace directory path
     */
    public function createWorkspace(Generation $generation): string
    {
        $workspaceDir = $this->getWorkspaceDir($generation);

        // If node_modules exists (from prewarm), keep it — only clear source files
        $nodeModulesDir = $workspaceDir.DIRECTORY_SEPARATOR.'node_modules';
        $hasPrewarmedDeps = File::isDirectory($nodeModulesDir);

        if (File::isDirectory($workspaceDir)) {
            if ($hasPrewarmedDeps) {
                // Preserve node_modules, remove everything else
                $items = File::glob($workspaceDir.DIRECTORY_SEPARATOR.'*');
                foreach ($items as $item) {
                    if (basename($item) === 'node_modules') {
                        continue;
                    }
                    if (is_dir($item)) {
                        File::deleteDirectory($item);
                    } else {
                        File::delete($item);
                    }
                }
            } else {
                File::deleteDirectory($workspaceDir);
                File::makeDirectory($workspaceDir, 0755, true);
            }
        } else {
            File::makeDirectory($workspaceDir, 0755, true);
        }

        // Write all generation files to workspace
        $files = $generation->generationFiles()->get();

        foreach ($files as $file) {
            $filePath = $workspaceDir.DIRECTORY_SEPARATOR.$file->file_path;
            $directory = dirname($filePath);

            if (! File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::put($filePath, $file->file_content);
        }

        // Tailwind v4 cleanup: remove v3-era config files that crash Vite
        $this->cleanupTailwindV3Artifacts($workspaceDir);

        Log::info("Workspace created for generation {$generation->id}", [
            'path' => $workspaceDir,
            'file_count' => $files->count(),
        ]);

        return $workspaceDir;
    }

    /**
     * Install dependencies (npm install) in workspace.
     */
    public function installDependencies(string $workspaceDir, ?string $progressLogPath = null): array
    {
        if (! File::exists($workspaceDir.DIRECTORY_SEPARATOR.'package.json')) {
            return [
                'success' => false,
                'error' => 'No package.json found in workspace',
            ];
        }

        // Skip npm install if node_modules already exists (from prewarm)
        $nodeModulesDir = $workspaceDir.DIRECTORY_SEPARATOR.'node_modules';
        if (File::isDirectory($nodeModulesDir) && File::exists($nodeModulesDir.DIRECTORY_SEPARATOR.'.package-lock.json')
            || (File::isDirectory($nodeModulesDir) && count(File::directories($nodeModulesDir)) > 5)) {
            if ($progressLogPath) {
                File::append($progressLogPath, '['.now()->format('H:i:s')."] [install] Skipping npm install — node_modules already exists (from prewarm)\n");
            }

            Log::info("Skipping npm install for {$workspaceDir} — node_modules exists from prewarm");

            return ['success' => true, 'skipped' => true];
        }

        $npmBinary = $this->getNpmBinary();

        $command = $this->buildSafeCommand("\"{$npmBinary}\" install --no-audit --no-fund --cache /tmp/.npm");

        if ($progressLogPath) {
            File::append($progressLogPath, '['.now()->format('H:i:s')."] [install] Starting npm install\n");
        }

        // Linux/container: stream npm output to prewarm log in real-time (for terminal indicator)
        if ($progressLogPath && PHP_OS_FAMILY !== 'Windows') {
            $logPath = escapeshellarg($progressLogPath);
            $command = 'bash -lc '.escapeshellarg($command." 2>&1 | tee -a {$logPath}");
        }

        $result = Process::path($workspaceDir)
            ->timeout(self::INSTALL_TIMEOUT)
            ->run($command);

        if ($result->successful()) {
            if ($progressLogPath) {
                File::append($progressLogPath, '['.now()->format('H:i:s')."] [install] npm install completed\n");
            }

            Log::info("Dependencies installed for workspace {$workspaceDir}");

            return ['success' => true];
        }

        if ($progressLogPath) {
            File::append($progressLogPath, '['.now()->format('H:i:s').'] [install] ERROR: npm install failed'."\n");
        }

        Log::error("npm install failed in {$workspaceDir}", [
            'output' => $result->output(),
            'error' => $result->errorOutput(),
            'exitCode' => $result->exitCode(),
        ]);

        return [
            'success' => false,
            'error' => 'npm install failed: '.$result->output(),
        ];
    }

    /**
     * Start a dev server in the workspace.
     *
     * @return array{success: bool, port?: int, pid?: int, error?: string}
     */
    public function startDevServer(string $workspaceDir, Generation $generation): array
    {
        $port = $this->findAvailablePort();
        if (! $port) {
            return [
                'success' => false,
                'error' => 'No available port for dev server',
            ];
        }

        $npmBinary = $this->getNpmBinary();

        // Start vite/dev server as a truly detached background process.
        // Using nohup + disown so the process survives after the PHP/Apache request ends.
        $pid = $this->spawnDaemonDevServer($workspaceDir, $port, $npmBinary);

        if (! $pid) {
            return [
                'success' => false,
                'error' => 'Dev server failed to start (could not spawn process)',
            ];
        }

        // Give Vite time to boot and start listening
        $ready = false;
        for ($i = 0; $i < 45; $i++) {
            sleep(1);
            if ($this->isPortInUse($port)) {
                $ready = true;
                Log::info("Dev server ready after {$i}s on port {$port}");
                break;
            }

            // Check if process is still alive every 5 seconds
            if ($i > 0 && $i % 5 === 0) {
                if (! $this->isProcessRunning($pid)) {
                    Log::error("Dev server process {$pid} died before port {$port} was ready");
                    break;
                }
            }
        }

        if (! $ready) {
            $logFile = "/tmp/preview-{$port}.log";
            $logContent = file_exists($logFile) ? file_get_contents($logFile) : 'no log';
            Log::error("Dev server port {$port} not listening after 45s", ['log' => $logContent, 'pid' => $pid]);

            return [
                'success' => false,
                'error' => 'Dev server did not start in time. Check logs.',
            ];
        }

        Log::info("Dev server started for generation {$generation->id}", [
            'port' => $port,
            'pid' => $pid,
        ]);

        return [
            'success' => true,
            'port' => $port,
            'pid' => $pid,
        ];
    }

    /**
     * Stop a dev server by killing its process.
     */
    public function stopDevServer(PreviewSession $session): bool
    {
        if (! $session->isRunning()) {
            return true;
        }

        $workspaceDir = $session->workspace_path;
        $port = $session->preview_port;

        // Kill process on the port
        $this->killProcessOnPort($port);

        $session->update([
            'status' => PreviewSession::STATUS_STOPPED,
            'stopped_at' => now(),
        ]);

        Log::info("Dev server stopped for session {$session->id}", [
            'port' => $port,
        ]);

        return true;
    }

    /**
     * Destroy workspace directory entirely.
     */
    public function destroyWorkspace(Generation $generation): bool
    {
        $workspaceDir = $this->getWorkspaceDir($generation);

        // Stop any running dev servers first
        $activeSessions = $generation->previewSessions()
            ->whereIn('status', [PreviewSession::STATUS_RUNNING, PreviewSession::STATUS_INSTALLING])
            ->get();

        foreach ($activeSessions as $session) {
            $this->stopDevServer($session);
        }

        if (File::isDirectory($workspaceDir)) {
            File::deleteDirectory($workspaceDir);

            Log::info("Workspace destroyed for generation {$generation->id}");

            return true;
        }

        return false;
    }

    /**
     * Set up a complete preview session: create session and dispatch async setup job.
     *
     * Returns immediately with 'creating' status. The frontend polls /preview/status.
     */
    public function setupPreview(Generation $generation): array
    {
        /** @var \App\Models\User $user */
        $user = $generation->user;

        // Check if there's already a running session
        $existing = $generation->previewSessions()
            ->where('status', PreviewSession::STATUS_RUNNING)
            ->first();

        if ($existing && ! $existing->hasTimedOut()) {
            $existing->touch();

            return [
                'success' => true,
                'session_id' => $existing->id,
                'port' => $existing->preview_port,
                'url' => $this->getPreviewUrl($existing->preview_port),
                'status' => 'already_running',
            ];
        }

        // Check if there's already a session being set up
        $inProgress = $generation->previewSessions()
            ->whereIn('status', [PreviewSession::STATUS_CREATING, PreviewSession::STATUS_INSTALLING])
            ->first();

        if ($inProgress) {
            return [
                'success' => true,
                'session_id' => $inProgress->id,
                'status' => $inProgress->status,
            ];
        }

        // Stop existing timed-out session
        if ($existing) {
            $this->stopDevServer($existing);
        }

        // Determine preview type
        $blueprint = $generation->project->blueprint;
        $outputFormat = $blueprint['outputFormat'] ?? 'html-css';
        $previewType = in_array($outputFormat, ['react', 'vue', 'svelte', 'angular'])
            ? PreviewSession::TYPE_SERVER
            : PreviewSession::TYPE_STATIC;

        // For static preview, set up synchronously (fast)
        if ($previewType === PreviewSession::TYPE_STATIC) {
            $session = PreviewSession::create([
                'generation_id' => $generation->id,
                'user_id' => $user->id,
                'workspace_path' => $this->getWorkspaceDir($generation),
                'preview_port' => 0,
                'preview_type' => $previewType,
                'status' => PreviewSession::STATUS_CREATING,
                'started_at' => now(),
                'last_activity_at' => now(),
            ]);

            $workspaceDir = $this->createWorkspace($generation);
            $session->update([
                'workspace_path' => $workspaceDir,
                'status' => PreviewSession::STATUS_RUNNING,
                'preview_port' => 0,
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'port' => 0,
                'url' => null,
                'status' => 'static',
                'preview_type' => 'static',
            ];
        }

        // For framework preview, create session and dispatch async job
        $session = PreviewSession::create([
            'generation_id' => $generation->id,
            'user_id' => $user->id,
            'workspace_path' => $this->getWorkspaceDir($generation),
            'preview_port' => null,
            'preview_type' => $previewType,
            'status' => PreviewSession::STATUS_CREATING,
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        // Dispatch async setup job
        SetupPreviewSession::dispatch($session->id, $generation->id);

        return [
            'success' => true,
            'session_id' => $session->id,
            'status' => 'creating',
        ];
    }

    /**
     * Execute the actual preview setup (called from the async job).
     *
     * Handles workspace creation, dependency installation, and dev server boot.
     */
    public function runPreviewSetup(PreviewSession $session, Generation $generation): void
    {
        try {
            // Step 1: Create workspace (preserves node_modules from prewarm)
            $workspaceDir = $this->createWorkspace($generation);
            $session->update(['workspace_path' => $workspaceDir]);

            // Step 2: Install dependencies (skips if already prewarmed)
            $session->update(['status' => PreviewSession::STATUS_INSTALLING]);
            $installResult = $this->installDependencies($workspaceDir);

            if (! $installResult['success']) {
                $session->update([
                    'status' => PreviewSession::STATUS_ERROR,
                    'error_message' => $installResult['error'],
                ]);

                return;
            }

            // Step 3: Start dev server
            $serverResult = $this->startDevServer($workspaceDir, $generation);

            if (! $serverResult['success']) {
                $session->update([
                    'status' => PreviewSession::STATUS_ERROR,
                    'error_message' => $serverResult['error'],
                ]);

                return;
            }

            $session->update([
                'status' => PreviewSession::STATUS_RUNNING,
                'preview_port' => $serverResult['port'],
            ]);

            Log::info("Preview session {$session->id} is running on port {$serverResult['port']}");
        } catch (\Exception $e) {
            $session->update([
                'status' => PreviewSession::STATUS_ERROR,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Preview setup failed for generation {$generation->id}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update a single file in the workspace (after refinement or re-generation).
     */
    public function updateWorkspaceFile(Generation $generation, string $filePath, string $content): bool
    {
        $workspaceDir = $this->getWorkspaceDir($generation);
        $fullPath = $workspaceDir.DIRECTORY_SEPARATOR.$filePath;
        $directory = dirname($fullPath);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($fullPath, $content);

        return true;
    }

    /**
     * Get all files in a workspace as an array.
     *
     * @return array<string, string> file path => content
     */
    public function getWorkspaceFiles(Generation $generation): array
    {
        $workspaceDir = $this->getWorkspaceDir($generation);
        $files = [];

        if (! File::isDirectory($workspaceDir)) {
            return $files;
        }

        $allFiles = File::allFiles($workspaceDir);
        foreach ($allFiles as $file) {
            $relativePath = str_replace($workspaceDir.DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);

            // Skip node_modules and build output
            if (str_starts_with($relativePath, 'node_modules/') || str_starts_with($relativePath, 'dist/')) {
                continue;
            }

            $files[$relativePath] = $file->getContents();
        }

        return $files;
    }

    /**
     * Clean up stale/inactive workspaces.
     */
    public function cleanupStaleWorkspaces(): int
    {
        $cleaned = 0;

        $staleSessions = PreviewSession::where('status', PreviewSession::STATUS_RUNNING)
            ->where('last_activity_at', '<', now()->subMinutes(self::INACTIVITY_TIMEOUT))
            ->get();

        foreach ($staleSessions as $session) {
            $this->stopDevServer($session);
            $cleaned++;
        }

        if ($cleaned > 0) {
            Log::info("Cleaned up {$cleaned} stale preview sessions");
        }

        return $cleaned;
    }

    // ========================================================================
    // Private Helpers
    // ========================================================================

    /**
     * Remove Tailwind CSS v3-era config files (postcss.config.js, tailwind.config.*)
     * when the project uses @tailwindcss/vite (Tailwind v4).
     *
     * Also patches vite.config.ts to include the @tailwindcss/vite plugin if missing.
     */
    private function cleanupTailwindV3Artifacts(string $workspaceDir): void
    {
        $packageJsonPath = $workspaceDir.DIRECTORY_SEPARATOR.'package.json';
        if (! File::exists($packageJsonPath)) {
            return;
        }

        $packageJson = json_decode(File::get($packageJsonPath), true);
        $devDeps = $packageJson['devDependencies'] ?? [];

        // Only clean up if using @tailwindcss/vite (Tailwind v4)
        if (! isset($devDeps['@tailwindcss/vite'])) {
            return;
        }

        // Remove postcss.config.js — not needed with @tailwindcss/vite
        $postcssConfig = $workspaceDir.DIRECTORY_SEPARATOR.'postcss.config.js';
        if (File::exists($postcssConfig)) {
            File::delete($postcssConfig);
            Log::info("Removed stale postcss.config.js from workspace {$workspaceDir}");
        }

        // Remove tailwind.config.* — Tailwind v4 uses CSS-first config
        foreach (['tailwind.config.ts', 'tailwind.config.js'] as $configFile) {
            $configPath = $workspaceDir.DIRECTORY_SEPARATOR.$configFile;
            if (File::exists($configPath)) {
                File::delete($configPath);
                Log::info("Removed stale {$configFile} from workspace {$workspaceDir}");
            }
        }

        // Ensure vite.config includes @tailwindcss/vite plugin
        foreach (['vite.config.ts', 'vite.config.js'] as $viteFile) {
            $vitePath = $workspaceDir.DIRECTORY_SEPARATOR.$viteFile;
            if (! File::exists($vitePath)) {
                continue;
            }

            $viteContent = File::get($vitePath);
            if (! str_contains($viteContent, '@tailwindcss/vite')) {
                // Inject tailwindcss import and plugin
                $viteContent = "import tailwindcss from '@tailwindcss/vite'\n".$viteContent;
                $viteContent = preg_replace(
                    '/plugins:\s*\[/',
                    'plugins: [tailwindcss(), ',
                    $viteContent,
                    1
                );
                File::put($vitePath, $viteContent);
                Log::info("Patched {$viteFile} with @tailwindcss/vite plugin in {$workspaceDir}");
            }
        }
    }

    private function getWorkspaceDir(Generation $generation): string
    {
        return $this->baseDir.DIRECTORY_SEPARATOR.'gen-'.$generation->id;
    }

    private function findAvailablePort(): ?int
    {
        $usedPorts = PreviewSession::where('status', PreviewSession::STATUS_RUNNING)
            ->whereNotNull('preview_port')
            ->pluck('preview_port')
            ->toArray();

        for ($port = self::PORT_MIN; $port <= self::PORT_MAX; $port++) {
            if (! in_array($port, $usedPorts) && ! $this->isPortInUse($port)) {
                return $port;
            }
        }

        return null;
    }

    private function isPortInUse(int $port): bool
    {
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
        if ($connection) {
            fclose($connection);

            return true;
        }

        return false;
    }

    private function isProcessRunning(int $pid): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $result = Process::run("tasklist /FI \"PID eq {$pid}\" /FO CSV /NH");

            return str_contains($result->output(), (string) $pid);
        }

        return file_exists("/proc/{$pid}");
    }

    private function killProcessOnPort(int $port): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Find PID using the port
            Process::run("FOR /F \"tokens=5\" %P IN ('netstat -ano ^| findstr :{$port}') DO taskkill /PID %P /F 2>nul");
        } else {
            Process::run("fuser -k {$port}/tcp 2>/dev/null");
        }
    }

    private function buildDevServerCommand(string $workspaceDir, int $port, string $npmBinary): string
    {
        // Check if there's a package.json with scripts
        $packageJson = json_decode(File::get($workspaceDir.DIRECTORY_SEPARATOR.'package.json'), true);
        $scripts = $packageJson['scripts'] ?? [];

        if (isset($scripts['dev'])) {
            // Inject port; bind to 127.0.0.1 — PHP proxy runs in same container
            return "\"{$npmBinary}\" run dev -- --port {$port} --host 127.0.0.1";
        }

        // Fallback to npx vite — derive npx path from npm path
        $npxBinary = $this->getNpxBinary($npmBinary);

        return "\"{$npxBinary}\" vite --port {$port} --host 127.0.0.1";
    }

    /**
     * Spawn a dev server as a truly detached daemon process.
     *
     * Uses nohup + bash disown so the Vite process outlives the PHP request.
     * Returns the PID of the spawned process, or null on failure.
     */
    private function spawnDaemonDevServer(string $workspaceDir, int $port, string $npmBinary): ?int
    {
        $logFile = "/tmp/preview-{$port}.log";
        $innerCmd = $this->buildDevServerCommand($workspaceDir, $port, $npmBinary);

        if (PHP_OS_FAMILY === 'Windows') {
            // Windows: use START /B to detach
            $command = $this->buildSafeCommand($innerCmd);
            $process = Process::path($workspaceDir)->timeout(0)->start($command);

            return $process->id() ?: null;
        }

        // Linux/Mac: use bash -c with nohup and disown to fully detach
        // Prepend NODE path if needed
        $envPrefix = '';
        if ($this->resolvedNodeDir) {
            $nodeDir = $this->resolvedNodeDir;
            $envPrefix = "PATH=\"${nodeDir}:\$PATH\" ";
        }

        $escapedDir = escapeshellarg($workspaceDir);
        $shellScript = "cd {$escapedDir} && {$envPrefix}nohup {$innerCmd} > ".escapeshellarg($logFile).' 2>&1 & echo $!';

        $rawPid = shell_exec('bash -c '.escapeshellarg($shellScript));
        $pid = (int) trim((string) $rawPid);

        Log::info('Spawned dev server daemon', ['port' => $port, 'pid' => $pid, 'log' => $logFile]);

        return $pid > 0 ? $pid : null;
    }

    private function getPreviewUrl(int $port): string
    {
        return "http://localhost:{$port}";
    }

    /**
     * Derive the npx binary path from the npm binary path.
     */
    private function getNpxBinary(string $npmBinary): string
    {
        $dir = dirname($npmBinary);

        if ($dir !== '.' && $dir !== '') {
            $npx = PHP_OS_FAMILY === 'Windows'
                ? $dir.DIRECTORY_SEPARATOR.'npx.cmd'
                : $dir.DIRECTORY_SEPARATOR.'npx';

            if (file_exists($npx)) {
                return $npx;
            }
        }

        return PHP_OS_FAMILY === 'Windows' ? 'npx.cmd' : 'npx';
    }

    private function getNpmBinary(): string
    {
        if ($this->resolvedNpmBinary !== null) {
            return $this->resolvedNpmBinary;
        }

        // 1. Check config/env
        $configured = config('services.node.npm_path');
        if ($configured && file_exists($configured)) {
            $this->resolvedNpmBinary = $configured;
            $this->resolvedNodeDir = dirname($configured);

            return $this->resolvedNpmBinary;
        }

        // 2. Auto-detect full path
        $detected = $this->detectNpmFullPath();
        if ($detected) {
            $this->resolvedNpmBinary = $detected;
            $this->resolvedNodeDir = dirname($detected);

            return $this->resolvedNpmBinary;
        }

        // 3. Fallback to bare command (may fail if not in web server PATH)
        $fallback = PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm';
        Log::warning("Could not resolve npm full path, falling back to '{$fallback}'. Set NPM_BINARY_PATH in .env if preview fails.");
        $this->resolvedNpmBinary = $fallback;

        return $this->resolvedNpmBinary;
    }

    /**
     * Detect the full path to npm by checking common locations and `where`/`which`.
     */
    private function detectNpmFullPath(): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Check common Windows locations
            $commonPaths = [
                'C:\\Program Files\\nodejs\\npm.cmd',
                'C:\\Program Files (x86)\\nodejs\\npm.cmd',
                getenv('APPDATA').'\\npm\\npm.cmd',
                getenv('ProgramFiles').'\\nodejs\\npm.cmd',
            ];

            foreach ($commonPaths as $path) {
                if ($path && file_exists($path)) {
                    return $path;
                }
            }

            // Try `where npm.cmd`
            $result = Process::run('where npm.cmd 2>nul');
            if ($result->successful()) {
                $lines = array_filter(explode("\n", trim($result->output())));
                if (! empty($lines)) {
                    $path = trim($lines[0]);
                    if (file_exists($path)) {
                        return $path;
                    }
                }
            }
        } else {
            // Unix: try `which npm`
            $result = Process::run('which npm 2>/dev/null');
            if ($result->successful()) {
                $path = trim($result->output());
                if ($path && file_exists($path)) {
                    return $path;
                }
            }
        }

        return null;
    }

    /**
     * Build a command string that ensures Node.js works on Windows.
     *
     * The PHP built-in web server (php artisan serve) often runs with a stripped
     * environment missing critical Windows variables like SYSTEMROOT. Without it,
     * Node.js v24+ crashes: ncrypto::CSPRNG assertion failure (can't find crypto DLLs).
     *
     * Solution: wrap the command in a batch that explicitly sets SYSTEMROOT and PATH
     * before running npm/npx, so it works regardless of the parent process environment.
     */
    private function buildSafeCommand(string $command): string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            // Unix: just prepend node dir to PATH if needed
            if ($this->resolvedNodeDir) {
                $currentPath = getenv('PATH') ?: '';
                if (! str_contains($currentPath, $this->resolvedNodeDir)) {
                    return "PATH=\"{$this->resolvedNodeDir}:\$PATH\" {$command}";
                }
            }

            return $command;
        }

        // Windows: build a cmd /c command that sets critical env vars
        $parts = [];

        // 1. Ensure SYSTEMROOT is set (required for Node.js crypto)
        $systemRoot = getenv('SYSTEMROOT') ?: getenv('SystemRoot') ?: 'C:\\WINDOWS';
        $parts[] = "set \"SYSTEMROOT={$systemRoot}\"";

        // 2. Ensure PATH includes Node.js directory
        if ($this->resolvedNodeDir) {
            $nodeDir = str_replace('"', '', $this->resolvedNodeDir);
            $parts[] = "set \"PATH={$nodeDir};%PATH%\"";
        }

        // 3. Build the final command
        $setup = implode(' && ', $parts);

        return "cmd /c \"{$setup} && {$command}\"";
    }
}
