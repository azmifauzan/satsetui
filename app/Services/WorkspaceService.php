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

        // Migrate Tailwind v4 workspaces to v3 — v4 generates CSS lazily via HMR
        // which is disabled in the preview proxy, causing empty utility classes.
        $this->cleanupTailwindV3Artifacts($workspaceDir);

        // Normalize entry files so preview keeps working when generated scaffold
        // mixes framework conventions (e.g. Vue main.ts but index references main.tsx).
        $this->normalizePreviewEntrypointArtifacts($workspaceDir);

        // Ensure Vite emits URLs under preview proxy base path.
        $this->normalizeVitePreviewBase($workspaceDir);

        // Ensure Vue Router has a default child redirect so the root path
        // renders the first page instead of an empty <RouterView />.
        $this->normalizeVueRouterDefaultChild($workspaceDir);

        // Normalize Tailwind utility classes that are invalid in Tailwind v4
        // and can fail dev-server style transforms.
        $this->normalizeTailwindPreviewUtilities($workspaceDir);

        // Scan all generated source files for missing npm dependencies and inject
        // them into package.json so npm install picks them up. This handles any
        // package the LLM decides to import that wasn't in the scaffold.
        $this->patchMissingDependencies($workspaceDir);

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
        $pid = $this->spawnDaemonDevServer($workspaceDir, $port, $npmBinary, $generation->id);

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
            ->whereIn('status', [PreviewSession::STATUS_RUNNING, PreviewSession::STATUS_INSTALLING, PreviewSession::STATUS_BOOTING])
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
            if ($existing->preview_type === PreviewSession::TYPE_STATIC) {
                $existing->touch();

                return [
                    'success' => true,
                    'session_id' => $existing->id,
                    'port' => $existing->preview_port,
                    'url' => $this->getPreviewUrl($existing->preview_port),
                    'status' => 'already_running',
                ];
            }

            if ($existing->preview_port && $this->isDevServerReachable($existing->preview_port)) {
                $existing->touch();

                return [
                    'success' => true,
                    'session_id' => $existing->id,
                    'port' => $existing->preview_port,
                    'url' => $this->getPreviewUrl($existing->preview_port),
                    'status' => 'already_running',
                ];
            }

            // Session is marked running but server is unreachable: recycle it.
            $this->stopDevServer($existing);
            $existing->update([
                'error_message' => 'Preview server became unreachable and was restarted.',
            ]);
        }

        if ($existing && $existing->hasTimedOut()) {
            $this->stopDevServer($existing);
        }

        // Check if there's already a session being set up
        $inProgress = $generation->previewSessions()
            ->whereIn('status', [PreviewSession::STATUS_CREATING, PreviewSession::STATUS_INSTALLING, PreviewSession::STATUS_BOOTING])
            ->first();

        if ($inProgress) {
            return [
                'success' => true,
                'session_id' => $inProgress->id,
                'status' => $inProgress->status,
            ];
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
        $progressLog = storage_path("logs/preview-prewarm-{$generation->id}.log");

        try {
            // Step 1: Create workspace (preserves node_modules from prewarm)
            file_put_contents($progressLog, '['.now()->format('H:i:s')."] [setup] Creating workspace...\n", FILE_APPEND);
            $workspaceDir = $this->createWorkspace($generation);
            $session->update(['workspace_path' => $workspaceDir]);
            file_put_contents($progressLog, '['.now()->format('H:i:s')."] [setup] Workspace ready: {$workspaceDir}\n", FILE_APPEND);

            // Step 2: Install dependencies (skips if already prewarmed)
            $session->update(['status' => PreviewSession::STATUS_INSTALLING]);
            file_put_contents($progressLog, '['.now()->format('H:i:s')."] [setup] Checking dependencies...\n", FILE_APPEND);
            $installResult = $this->installDependencies($workspaceDir, $progressLog);

            if (! $installResult['success']) {
                file_put_contents($progressLog, '['.now()->format('H:i:s').'] [setup] ERROR: '.($installResult['error'] ?? 'install failed')."\n", FILE_APPEND);
                $session->update([
                    'status' => PreviewSession::STATUS_ERROR,
                    'error_message' => $installResult['error'],
                ]);

                return;
            }

            $skipped = $installResult['skipped'] ?? false;
            file_put_contents($progressLog, '['.now()->format('H:i:s').'] [setup] Dependencies '.($skipped ? 'already installed (skipped)' : 'installed')."\n", FILE_APPEND);

            // Step 3: Start dev server
            $session->update(['status' => PreviewSession::STATUS_BOOTING]);
            file_put_contents($progressLog, '['.now()->format('H:i:s')."] [setup] Starting dev server...\n", FILE_APPEND);
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

            file_put_contents($progressLog, '['.now()->format('H:i:s')."] [setup] Dev server started on port {$serverResult['port']}\n", FILE_APPEND);
            Log::info("Preview session {$session->id} is running on port {$serverResult['port']}");
        } catch (\Exception $e) {
            file_put_contents($progressLog, '['.now()->format('H:i:s').'] [setup] ERROR: '.$e->getMessage()."\n", FILE_APPEND);
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
     * Migrate workspaces that were scaffolded with Tailwind v4 (@tailwindcss/vite)
     * to Tailwind v3 (PostCSS-based). Tailwind v4 generates utilities lazily via HMR
     * which is incompatible with the preview proxy (HMR disabled). Tailwind v3 uses
     * PostCSS to scan content files synchronously on every request, so all utility
     * classes are available immediately without HMR.
     */
    private function cleanupTailwindV3Artifacts(string $workspaceDir): void
    {
        $packageJsonPath = $workspaceDir.DIRECTORY_SEPARATOR.'package.json';
        if (! File::exists($packageJsonPath)) {
            return;
        }

        $packageJson = json_decode(File::get($packageJsonPath), true);
        $devDeps = $packageJson['devDependencies'] ?? [];

        // Only migrate workspaces that still use @tailwindcss/vite (Tailwind v4)
        if (! isset($devDeps['@tailwindcss/vite'])) {
            return;
        }

        Log::info("Migrating workspace from Tailwind v4 to v3: {$workspaceDir}");

        // 1. Update package.json devDependencies
        unset($packageJson['devDependencies']['@tailwindcss/vite']);
        $packageJson['devDependencies']['tailwindcss'] = '^3.4.0';
        $packageJson['devDependencies']['postcss'] = '^8.4.0';
        $packageJson['devDependencies']['autoprefixer'] = '^10.4.0';
        File::put($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        Log::info('Updated package.json devDependencies to Tailwind v3');

        // 2. Patch vite.config: remove @tailwindcss/vite import and plugin usage
        foreach (['vite.config.ts', 'vite.config.js'] as $viteFile) {
            $vitePath = $workspaceDir.DIRECTORY_SEPARATOR.$viteFile;
            if (! File::exists($vitePath)) {
                continue;
            }

            $viteContent = File::get($vitePath);

            // Remove import line
            $viteContent = preg_replace("/^import tailwindcss from '@tailwindcss\/vite'\n?/m", '', $viteContent);

            // Remove tailwindcss() from plugins array (handles "tailwindcss(), " or ", tailwindcss()" patterns)
            $viteContent = preg_replace('/,?\s*tailwindcss\(\)\s*,?/', '', $viteContent);

            // Clean up any double commas left behind
            $viteContent = preg_replace('/\[\s*,/', '[', $viteContent);
            $viteContent = preg_replace('/,\s*\]/', ']', $viteContent);

            File::put($vitePath, $viteContent);
            Log::info("Removed @tailwindcss/vite from {$viteFile}");
        }

        // 3. Patch main CSS: replace @import "tailwindcss" with @tailwind directives
        $cssCandidates = [
            'src/assets/main.css',
            'src/styles/globals.css',
            'src/app.css',
            'src/styles.css',
        ];

        foreach ($cssCandidates as $cssFile) {
            $cssPath = $workspaceDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $cssFile);
            if (! File::exists($cssPath)) {
                continue;
            }

            $cssContent = File::get($cssPath);
            if (str_contains($cssContent, '@import "tailwindcss"')) {
                $cssContent = str_replace(
                    '@import "tailwindcss";',
                    "@tailwind base;\n@tailwind components;\n@tailwind utilities;",
                    $cssContent
                );
                $cssContent = str_replace(
                    "@import \"tailwindcss\"\n",
                    "@tailwind base;\n@tailwind components;\n@tailwind utilities;\n",
                    $cssContent
                );
                File::put($cssPath, $cssContent);
                Log::info("Patched {$cssFile} with @tailwind directives");
            }
        }

        // 4. Create tailwind.config.js if missing
        $tailwindConfigPath = $workspaceDir.DIRECTORY_SEPARATOR.'tailwind.config.js';
        if (! File::exists($tailwindConfigPath)) {
            File::put($tailwindConfigPath, <<<'CONFIG'
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx,vue,svelte}",
  ],
  darkMode: 'class',
  theme: {
    extend: {},
  },
  plugins: [],
}
CONFIG);
            Log::info('Created tailwind.config.js for v3');
        }

        // 5. Create postcss.config.js if missing
        $postcssConfigPath = $workspaceDir.DIRECTORY_SEPARATOR.'postcss.config.js';
        if (! File::exists($postcssConfigPath)) {
            File::put($postcssConfigPath, <<<'CONFIG'
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
CONFIG);
            Log::info('Created postcss.config.js for v3');
        }

        // 6. Delete node_modules so npm install fetches tailwindcss v3 packages
        $nodeModulesPath = $workspaceDir.DIRECTORY_SEPARATOR.'node_modules';
        if (File::isDirectory($nodeModulesPath)) {
            File::deleteDirectory($nodeModulesPath);
            Log::info('Removed node_modules for fresh v3 install');
        }
    }

    /**
     * Auto-fix common generated entrypoint mismatches in index.html.
     */
    private function normalizePreviewEntrypointArtifacts(string $workspaceDir): void
    {
        $indexPath = $workspaceDir.DIRECTORY_SEPARATOR.'index.html';
        if (! File::exists($indexPath)) {
            return;
        }

        $indexContent = File::get($indexPath);
        $updatedContent = $indexContent;

        $entryCandidates = [
            '/src/main.tsx',
            '/src/main.ts',
            '/src/main.jsx',
            '/src/main.js',
        ];

        $existingEntries = array_values(array_filter($entryCandidates, function (string $entry) use ($workspaceDir): bool {
            $relativePath = ltrim($entry, '/');

            return File::exists($workspaceDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
        }));

        if (preg_match('/<script[^>]*type=["\']module["\'][^>]*src=["\']([^"\']+)["\'][^>]*><\/script>/i', $updatedContent, $match) === 1) {
            $currentEntry = trim($match[1]);
            $currentRelativePath = ltrim(parse_url($currentEntry, PHP_URL_PATH) ?? $currentEntry, '/');
            $currentEntryExists = File::exists($workspaceDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $currentRelativePath));

            if (! $currentEntryExists && ! empty($existingEntries)) {
                $updatedContent = str_replace($currentEntry, $existingEntries[0], $updatedContent);
            }
        } elseif (! empty($existingEntries)) {
            $injectedScript = '    <script type="module" src="'.$existingEntries[0].'"></script>';
            if (str_contains($updatedContent, '</body>')) {
                $updatedContent = str_replace('</body>', $injectedScript.PHP_EOL.'  </body>', $updatedContent);
            }
        }

        $expectedMountId = $this->detectExpectedMountId($workspaceDir);
        if ($expectedMountId !== null) {
            if (preg_match('/<div[^>]*id=["\']([^"\']+)["\'][^>]*><\/div>/i', $updatedContent, $divMatch) === 1) {
                $currentId = $divMatch[1];
                if ($currentId !== $expectedMountId) {
                    $updatedContent = str_replace('id="'.$currentId.'"', 'id="'.$expectedMountId.'"', $updatedContent);
                    $updatedContent = str_replace("id='".$currentId."'", "id='".$expectedMountId."'", $updatedContent);
                }
            } elseif (str_contains($updatedContent, '</body>')) {
                $container = '    <div id="'.$expectedMountId.'"></div>';
                $updatedContent = str_replace('</body>', $container.PHP_EOL.'  </body>', $updatedContent);
            }
        }

        if ($updatedContent !== $indexContent) {
            File::put($indexPath, $updatedContent);
            Log::info("Normalized preview entry artifacts in {$workspaceDir}");
        }
    }

    /**
     * Detect expected mount container id from available main entry file.
     */
    private function detectExpectedMountId(string $workspaceDir): ?string
    {
        $entryFiles = ['main.tsx', 'main.ts', 'main.jsx', 'main.js'];

        foreach ($entryFiles as $entryFile) {
            $mainPath = $workspaceDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.$entryFile;
            if (! File::exists($mainPath)) {
                continue;
            }

            $content = File::get($mainPath);
            if (preg_match('/mount\(\s*["\']#([^"\']+)["\']\s*\)/', $content, $match) === 1) {
                return $match[1];
            }

            if (preg_match('/getElementById\(\s*["\']([^"\']+)["\']\s*\)/', $content, $match) === 1) {
                return $match[1];
            }
        }

        return null;
    }

    /**
     * Ensure Vue Router has a default child redirect so that visiting the root
     * path renders the first page content instead of an empty <RouterView />.
     *
     * Without this, navigating to "/" matches the MainLayout parent route but
     * no child route, leaving the content area blank.
     */
    private function normalizeVueRouterDefaultChild(string $workspaceDir): void
    {
        $routerCandidates = [
            'src/router/index.ts',
            'src/router/index.js',
            'src/router.ts',
            'src/router.js',
        ];

        foreach ($routerCandidates as $candidate) {
            $routerPath = $workspaceDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $candidate);
            if (! File::exists($routerPath)) {
                continue;
            }

            $content = File::get($routerPath);

            // Only apply to Vue Router files
            if (! str_contains($content, 'createRouter') || ! str_contains($content, 'createWebHistory')) {
                continue;
            }

            // Already has a default empty-path child redirect — nothing to do
            if (preg_match("/path:\s*['\"]['\"]\\s*,\\s*redirect/", $content) === 1) {
                continue;
            }

            // Find the first named child route to use as redirect target.
            // Use .*? (lazy) instead of [^}]* to handle nested braces in
            // route objects (e.g. meta: { layout: 'main' }).
            if (preg_match("/children:\s*\[.*?path:\s*['\"]([^'\"]+)['\"]/s", $content, $match) !== 1) {
                continue;
            }

            $firstChildPath = $match[1];

            // Insert a default redirect just before the closing of the children array
            // Look for the pattern: children: [ ... ] and add { path: '', redirect: 'firstChild' }
            $content = preg_replace(
                '/(children:\s*\[)(.*?)(\s*\],)/s',
                "$1$2\n    { path: '', redirect: '{$firstChildPath}' },$3",
                $content,
                1
            );

            if ($content !== null) {
                File::put($routerPath, $content);
                Log::info("Normalized Vue Router default child redirect in {$routerPath}", [
                    'redirect_to' => $firstChildPath,
                ]);
            }
        }
    }

    /**
     * Ensure generated Vite config sets `base` from VITE_PREVIEW_BASE.
     */
    private function normalizeVitePreviewBase(string $workspaceDir): void
    {
        foreach (['vite.config.ts', 'vite.config.js'] as $viteFile) {
            $vitePath = $workspaceDir.DIRECTORY_SEPARATOR.$viteFile;
            if (! File::exists($vitePath)) {
                continue;
            }

            $content = File::get($vitePath);
            if (preg_match('/\bbase\s*:/', $content) === 1) {
                continue;
            }

            $updated = preg_replace(
                '/defineConfig\s*\(\s*\{/',
                "defineConfig({\n  base: process.env.VITE_PREVIEW_BASE || '/',",
                $content,
                1
            );

            if (is_string($updated) && $updated !== $content) {
                File::put($vitePath, $updated);
                Log::info("Normalized {$viteFile} with preview base in {$workspaceDir}");
            }
        }
    }

    /**
     * Replace Tailwind v4-invalid ring-primary apply usage in generated Vue files.
     */
    private function normalizeTailwindPreviewUtilities(string $workspaceDir): void
    {
        $srcDir = $workspaceDir.DIRECTORY_SEPARATOR.'src';
        if (! File::isDirectory($srcDir)) {
            return;
        }

        foreach (File::allFiles($srcDir) as $file) {
            $path = $file->getPathname();
            if (! str_ends_with($path, '.vue') && ! str_ends_with($path, '.css')) {
                continue;
            }

            $content = File::get($path);
            $updated = str_replace('ring-primary', 'ring-red-500', $content);
            $updated = str_replace('ring-opacity-50', 'ring-red-500/50', $updated);

            if ($updated !== $content) {
                File::put($path, $updated);
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

    /**
     * Resolve a reachable host for the preview dev server port.
     */
    public function resolveReachableDevServerHost(int $port): ?string
    {
        $configuredHosts = config('app.preview_proxy_hosts');
        $hosts = is_array($configuredHosts)
            ? $configuredHosts
            : explode(',', (string) $configuredHosts);

        foreach ($hosts as $host) {
            $candidate = trim((string) $host);
            if ($candidate === '') {
                continue;
            }

            $connection = @fsockopen($candidate, $port, $errno, $errstr, 1);
            if ($connection) {
                fclose($connection);

                return $candidate;
            }
        }

        return null;
    }

    /**
     * Check whether a dev server is reachable on any configured preview host.
     */
    public function isDevServerReachable(int $port): bool
    {
        return $this->resolveReachableDevServerHost($port) !== null;
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
     * Uses proc_open + nohup so the Vite process outlives the PHP request.
     * PID is written to a file to avoid shell_exec pipe-blocking issues.
     * Returns the PID of the spawned process, or null on failure.
     */
    private function spawnDaemonDevServer(string $workspaceDir, int $port, string $npmBinary, int $generationId): ?int
    {
        $logFile = "/tmp/preview-{$port}.log";
        $pidFile = "/tmp/preview-{$port}.pid";
        $innerCmd = $this->buildDevServerCommand($workspaceDir, $port, $npmBinary);

        // Build the Vite base path so the dev server emits all asset URLs
        // under the proxy route prefix — eliminates any JS/HTML rewriting.
        $previewBase = "/generation/{$generationId}/preview/proxy";

        if (PHP_OS_FAMILY === 'Windows') {
            // Windows: use START /B to detach
            $command = $this->buildSafeCommand($innerCmd);
            $process = Process::path($workspaceDir)
                ->env(['VITE_PREVIEW_BASE' => "{$previewBase}/"])
                ->timeout(0)
                ->start($command);

            return $process->id() ?: null;
        }

        // Linux/Mac: use proc_open with all descriptors pointing to files (not pipes)
        // This prevents PHP from blocking on pipe reads that shell_exec suffers from
        // when background processes inherit the stdout pipe.
        $envPrefix = '';
        if ($this->resolvedNodeDir) {
            $nodeDir = $this->resolvedNodeDir;
            $envPrefix = "PATH=\"{$nodeDir}:\$PATH\" ";
        }

        // Pass Vite preview base path as env var
        $envPrefix .= "VITE_PREVIEW_BASE=\"{$previewBase}/\" ";

        $escapedDir = escapeshellarg($workspaceDir);
        $escapedLog = escapeshellarg($logFile);
        $escapedPid = escapeshellarg($pidFile);

        // Clean up stale files
        @unlink($logFile);
        @unlink($pidFile);

        // Shell script: cd to workspace, start nohup in background, write PID to file
        $shellScript = "cd {$escapedDir} && {$envPrefix}nohup {$innerCmd} > {$escapedLog} 2>&1 & echo \$! > {$escapedPid}";

        $descriptors = [
            0 => ['file', '/dev/null', 'r'],
            1 => ['file', '/dev/null', 'w'],
            2 => ['file', '/dev/null', 'w'],
        ];

        $proc = proc_open(['bash', '-c', $shellScript], $descriptors, $pipes);

        if (! is_resource($proc)) {
            Log::error('proc_open failed for dev server spawn', ['port' => $port]);

            return null;
        }

        // Wait for bash to finish — it backgrounds nohup and exits quickly
        proc_close($proc);

        // Small wait for PID file to be written
        usleep(500000); // 500ms

        if (! file_exists($pidFile)) {
            Log::error('Dev server PID file not created', ['pidFile' => $pidFile, 'port' => $port]);

            return null;
        }

        $pid = (int) trim(file_get_contents($pidFile));

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

    /**
     * Scan all generated source files for npm imports not declared in package.json
     * and inject them so npm install picks them up.
     *
     * The LLM sometimes imports packages that weren't in the scaffold despite the
     * MCP constraint. Rather than failing at runtime, we detect and add them here.
     * If any packages were added and node_modules already exists (from prewarm),
     * we remove the prewarm skip-marker so npm install re-runs with the new deps.
     */
    private function patchMissingDependencies(string $workspaceDir): void
    {
        $packageJsonPath = $workspaceDir.DIRECTORY_SEPARATOR.'package.json';
        if (! File::exists($packageJsonPath)) {
            return;
        }

        $packageJson = json_decode(File::get($packageJsonPath), true);
        if (! is_array($packageJson)) {
            return;
        }

        // Collect all declared dependencies (deps + devDeps)
        $declared = array_merge(
            array_keys($packageJson['dependencies'] ?? []),
            array_keys($packageJson['devDependencies'] ?? [])
        );

        // Node.js built-in modules — never need to be installed
        $builtins = [
            'fs', 'path', 'os', 'url', 'http', 'https', 'crypto', 'stream',
            'buffer', 'events', 'util', 'assert', 'child_process', 'cluster',
            'dns', 'net', 'querystring', 'readline', 'string_decoder', 'timers',
            'tls', 'zlib', 'module', 'process', 'v8', 'vm', 'worker_threads',
        ];

        // Scan all JS/TS/Vue/Svelte/JSX source files for ES module imports
        $srcDir = $workspaceDir.DIRECTORY_SEPARATOR.'src';
        $extensions = ['vue', 'ts', 'tsx', 'js', 'jsx', 'svelte'];
        $pattern = $workspaceDir.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR.'*.{'.
            implode(',', $extensions).'}';

        // Build a flat list of all matching files under src/ and root
        $sourceFiles = [];
        foreach ($extensions as $ext) {
            $found = File::glob($srcDir.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR."*.{$ext}");
            if ($found) {
                $sourceFiles = array_merge($sourceFiles, $found);
            }
        }

        // Regex: match ES static imports — import ... from 'pkg' or import('pkg')
        $importRegex = '/(?:^|\n)\s*import\s[\s\S]*?from\s+[\'"]([^\'"]+)[\'"]|import\([\'"]([^\'"]+)[\'"]/m';

        $missing = [];
        foreach ($sourceFiles as $file) {
            $content = File::get($file);
            if (preg_match_all($importRegex, $content, $matches)) {
                $specifiers = array_filter(
                    array_merge($matches[1], $matches[2]),
                    fn ($s) => $s !== ''
                );
                foreach ($specifiers as $specifier) {
                    // Skip relative and absolute path imports
                    if (str_starts_with($specifier, '.') || str_starts_with($specifier, '/')) {
                        continue;
                    }

                    // Skip virtual modules (vite:, \0, etc.)
                    if (str_starts_with($specifier, 'vite:') || str_starts_with($specifier, '\\0')) {
                        continue;
                    }

                    // Skip Vite/webpack path aliases (e.g. @/, ~/, #/) — these are
                    // not npm package names.
                    if (preg_match('/^[@~#]\//', $specifier)) {
                        continue;
                    }

                    // Extract the package name (handles scoped packages like @org/pkg)
                    $parts = explode('/', $specifier);
                    $pkgName = $specifier[0] === '@' && count($parts) >= 2
                        ? $parts[0].'/'.$parts[1]
                        : $parts[0];

                    if (
                        ! in_array($pkgName, $declared, true) &&
                        ! in_array($pkgName, $builtins, true) &&
                        ! isset($missing[$pkgName])
                    ) {
                        $missing[$pkgName] = 'latest';
                    }
                }
            }
        }

        if (empty($missing)) {
            return;
        }

        Log::warning('patchMissingDependencies: injecting undeclared packages into package.json', [
            'workspace' => $workspaceDir,
            'packages' => array_keys($missing),
        ]);

        // Inject into dependencies section
        $packageJson['dependencies'] = array_merge(
            $packageJson['dependencies'] ?? [],
            $missing
        );

        File::put($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // If node_modules already exists (prewarm), force npm install to re-run
        // by removing the inner .package-lock.json marker and trimming dirs below
        // the threshold used by the skip check (> 5 subdirectories).
        // Simplest reliable approach: remove node_modules entirely so npm install
        // rebuilds. The npm cache makes this fast even on subsequent runs.
        $nodeModulesDir = $workspaceDir.DIRECTORY_SEPARATOR.'node_modules';
        if (File::isDirectory($nodeModulesDir)) {
            Log::info('patchMissingDependencies: removing prewarm node_modules to force reinstall', [
                'workspace' => $workspaceDir,
            ]);
            File::deleteDirectory($nodeModulesDir);
        }
    }
}
