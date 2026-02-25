<?php

namespace App\Http\Controllers;

use App\Models\Generation;
use App\Models\PreviewSession;
use App\Services\WorkspaceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Preview Controller
 *
 * Manages live preview sessions for generated templates.
 *
 * - For HTML+CSS output: serves files directly (no dev server needed)
 * - For framework output: manages workspace setup, npm install, dev server, and proxy
 */
class PreviewController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private WorkspaceService $workspaceService
    ) {}

    /**
     * Setup a preview session (create workspace, install deps, start server).
     */
    public function setup(Generation $generation): JsonResponse
    {
        $this->authorize('view', $generation);

        $previewableStatuses = ['completed', 'generating', 'processing'];
        if (! in_array($generation->status, $previewableStatuses, true)) {
            return response()->json([
                'success' => false,
                'error' => 'Generation must be started before preview can be set up',
            ], 422);
        }

        // For in-progress generations, require at least one completed page
        if ($generation->status !== 'completed') {
            $hasCompleted = collect($generation->progress_data ?? [])
                ->contains(fn ($p) => ($p['status'] ?? '') === 'completed');

            if (! $hasCompleted) {
                return response()->json([
                    'success' => false,
                    'error' => 'Wait for the first page to complete before starting preview',
                ], 422);
            }
        }

        $result = $this->workspaceService->setupPreview($generation);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Get preview session status.
     */
    public function status(Generation $generation): JsonResponse
    {
        $this->authorize('view', $generation);

        $session = $generation->previewSessions()
            ->latest()
            ->first();

        if ($session && $session->status === PreviewSession::STATUS_RUNNING && $session->preview_type === PreviewSession::TYPE_SERVER) {
            $port = (int) ($session->preview_port ?? 0);

            if ($port <= 0 || ! $this->workspaceService->isDevServerReachable($port)) {
                $session->update([
                    'status' => PreviewSession::STATUS_STOPPED,
                    'error_message' => 'Preview server is unreachable. Starting a new session is required.',
                    'stopped_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'none',
                    'message' => 'No active preview session',
                ]);
            }
        }

        if (! $session) {
            return response()->json([
                'success' => true,
                'status' => 'none',
                'message' => 'No preview session exists',
            ]);
        }

        $previewUrl = $session->preview_port
            ? "/generation/{$generation->id}/preview/proxy"
            : null;

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'status' => $session->status,
            'preview_type' => $session->preview_type,
            'port' => $session->preview_port,
            'url' => $previewUrl,
            'preview_url' => $previewUrl,
            'error' => $session->error_message,
            'started_at' => $session->started_at?->toISOString(),
            'last_activity' => $session->last_activity_at?->toISOString(),
        ]);
    }

    /**
     * Get lightweight terminal logs for preview progress.
     */
    public function logs(Generation $generation, Request $request): JsonResponse
    {
        $this->authorize('view', $generation);

        $maxLines = max(20, min(200, (int) $request->integer('lines', 80)));
        $lines = [];

        $session = $generation->previewSessions()
            ->latest()
            ->first();

        $lines[] = sprintf(
            'Generation status: %s (%d/%d)',
            $generation->current_status ?? $generation->status,
            (int) $generation->current_page_index,
            (int) $generation->total_pages
        );

        if ($session) {
            $lines[] = sprintf(
                'Preview session: %s%s',
                $session->status,
                $session->preview_port ? " on :{$session->preview_port}" : ''
            );

            if ($session->error_message) {
                $lines[] = sprintf('ERROR: %s', $session->error_message);
            }
        }

        $prewarmLog = storage_path("logs/preview-prewarm-{$generation->id}.log");
        if (File::exists($prewarmLog)) {
            $lines = array_merge($lines, $this->tailLines($prewarmLog, (int) floor($maxLines / 2)));
        }

        if ($session?->preview_port && in_array($session->status, [
            PreviewSession::STATUS_CREATING,
            PreviewSession::STATUS_INSTALLING,
            PreviewSession::STATUS_BOOTING,
            PreviewSession::STATUS_RUNNING,
        ], true)) {
            $runtimeLog = "/tmp/preview-{$session->preview_port}.log";
            if (File::exists($runtimeLog)) {
                $runtimeLines = $this->tailLines($runtimeLog, (int) floor($maxLines / 2));
                foreach ($runtimeLines as $runtimeLine) {
                    $lines[] = '[vite] '.$runtimeLine;
                }
            }
        }

        $lines = array_values(array_filter(array_map(static fn ($line) => trim($line), $lines), static fn ($line) => $line !== ''));
        $lines = array_slice($lines, -$maxLines);
        $progress = $this->resolveProgress($session?->status ?? 'none', $lines);

        return response()->json([
            'success' => true,
            'status' => $session?->status ?? 'none',
            'lines' => $lines,
            'phase' => $progress['phase'],
            'progress_percentage' => $progress['percentage'],
            'progress_detail' => $progress['detail'],
        ]);
    }

    /**
     * Read the last N lines of a file.
     *
     * @return array<int, string>
     */
    private function tailLines(string $filePath, int $maxLines): array
    {
        $content = File::get($filePath);
        $all = preg_split('/\r\n|\r|\n/', $content) ?: [];

        return array_slice(array_values(array_filter(array_map(static fn ($line) => Str::limit(trim($line), 500, '...'), $all), static fn ($line) => $line !== '')), -$maxLines);
    }

    /**
     * Estimate setup progress for terminal UI from session status + log milestones.
     *
     * @param  array<int, string>  $lines
     * @return array{phase: string, percentage: int, detail: string}
     */
    private function resolveProgress(string $status, array $lines): array
    {
        if ($status === 'stopped') {
            return [
                'phase' => 'stopped',
                'percentage' => 100,
                'detail' => 'Preview stopped',
            ];
        }

        if ($status === 'error') {
            return [
                'phase' => 'error',
                'percentage' => 0,
                'detail' => 'Failed to start preview',
            ];
        }

        if ($status === 'none') {
            return [
                'phase' => 'none',
                'percentage' => 0,
                'detail' => 'Waiting',
            ];
        }

        $phase = $status;
        $detail = 'Waiting';

        $percentage = match ($status) {
            'creating' => 15,
            'installing' => 45,
            'booting' => 85,
            'running' => 100,
            'stopped' => 100,
            'error' => 0,
            default => 5,
        };

        $joined = strtolower(implode("\n", $lines));

        if (str_contains($joined, 'starting parallel dependency prewarm')) {
            $percentage = max($percentage, 20);
            $phase = 'prewarming';
            $detail = 'Starting dependency prewarm';
        }
        if (str_contains($joined, 'workspace ready')) {
            $percentage = max($percentage, 35);
            $phase = 'workspace';
            $detail = 'Workspace prepared';
        }
        if (str_contains($joined, 'install dependencies') || str_contains($joined, 'installing dependencies')) {
            $percentage = max($percentage, 60);
            $phase = 'installing';
            $detail = 'Installing npm dependencies';
        }
        if (str_contains($joined, '[install] starting npm install')) {
            $percentage = max($percentage, 62);
            $phase = 'installing';
            $detail = 'npm install started';
        }
        if (str_contains($joined, 'added ') && str_contains($joined, 'packages in')) {
            $percentage = max($percentage, 74);
            $phase = 'installing';
            $detail = 'Packages added';
        }
        if (str_contains($joined, 'up to date in')) {
            $percentage = max($percentage, 80);
            $phase = 'installing';
            $detail = 'Dependencies already up to date';
        }
        if (str_contains($joined, 'audited ') && str_contains($joined, 'packages')) {
            $percentage = max($percentage, 83);
            $phase = 'installing';
            $detail = 'Dependency audit done';
        }
        if (str_contains($joined, 'dependencies installed successfully')) {
            $percentage = max($percentage, 85);
            $phase = 'booting';
            $detail = 'Dependencies installed';
        }
        if (str_contains($joined, '[install] npm install completed')) {
            $percentage = max($percentage, 88);
            $phase = 'booting';
            $detail = 'npm install completed';
        }
        if (str_contains($joined, '[install] skipping npm install') || str_contains($joined, 'already installed (skipped)')) {
            $percentage = max($percentage, 88);
            $phase = 'booting';
            $detail = 'Dependencies ready (cached)';
        }
        if (str_contains($joined, '[setup] starting dev server')) {
            $percentage = max($percentage, 90);
            $phase = 'booting';
            $detail = 'Starting dev server';
        }
        if (str_contains($joined, 'dev server started') || str_contains($joined, 'ready in') || str_contains($joined, 'local:')) {
            $percentage = max($percentage, 95);
            $phase = 'booting';
            $detail = 'Dev server is booting';
        }
        if ($status === 'running') {
            $percentage = 100;
            $phase = 'running';
            $detail = 'Preview ready';
        }
        if ($status === 'error') {
            $detail = 'Failed to start preview';
        }

        return [
            'phase' => $phase,
            'percentage' => max(0, min(100, $percentage)),
            'detail' => $detail,
        ];
    }

    /**
     * Proxy requests to the dev server.
     *
     * This avoids CORS issues when the preview is loaded in an iframe.
     */
    public function proxy(Generation $generation, Request $request, string $path = ''): Response|JsonResponse
    {
        $this->authorize('view', $generation);

        if (trim($path, '/') === '@vite/client') {
            return response($this->viteClientStubModule(), 200)
                ->header('Content-Type', 'text/javascript; charset=utf-8')
                ->header('X-Preview-Port', 'stub')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('X-Content-Type-Options', 'nosniff');
        }

        $session = $generation->previewSessions()
            ->where('status', 'running')
            ->latest()
            ->first();

        if (! $session) {
            return response()->json([
                'error' => 'No active preview session',
            ], 404);
        }

        // Touch session to track activity
        $session->touch();

        $port = $session->preview_port;

        if (! $port) {
            return response()->json([
                'error' => 'No dev server port assigned',
            ], 500);
        }

        // Vite is configured with base = /generation/{id}/preview/proxy/
        // so we forward the full path including that prefix to the Vite server.
        $base = "/generation/{$generation->id}/preview/proxy";
        $host = $this->workspaceService->resolveReachableDevServerHost((int) $port);
        if (! $host) {
            $session->update([
                'status' => PreviewSession::STATUS_STOPPED,
                'error_message' => 'Preview server is unreachable. Please start preview again.',
                'stopped_at' => now(),
            ]);

            return response()->json([
                'error' => 'Preview server is unreachable. Please restart preview.',
            ], 502);
        }

        $queryString = $this->getProxyForwardQueryString($request);
        $targetUrl = $this->buildProxyTargetUrl($host, (int) $port, $base, $path, $queryString);

        Log::debug('Preview proxy', ['path' => $path, 'targetUrl' => $targetUrl]);

        try {
            /** @var \Illuminate\Http\Client\Response $proxyResponse */
            $proxyResponse = Http::timeout(10)
                ->withHeaders([
                    'Accept' => $request->header('Accept', '*/*'),
                    'Accept-Encoding' => 'identity', // Don't compress for proxy
                ])
                ->get($targetUrl);

            if ($this->shouldRetryProxyWithoutBase($proxyResponse, $path)) {
                $fallbackUrl = $this->buildProxyTargetUrl($host, (int) $port, '', $path, $queryString);
                Log::debug('Preview proxy fallback without base', ['path' => $path, 'fallbackUrl' => $fallbackUrl]);

                $proxyResponse = Http::timeout(10)
                    ->withHeaders([
                        'Accept' => $request->header('Accept', '*/*'),
                        'Accept-Encoding' => 'identity',
                    ])
                    ->get($fallbackUrl);
            }

            $contentType = $proxyResponse->header('Content-Type') ?? '';
            $body = $proxyResponse->body();

            // Vite dev server may return incorrect or empty Content-Type for
            // transformed files (.vue → JS, .css → JS with HMR wrapper, etc.).
            // Browsers enforce strict MIME checking for ES module scripts and
            // will reject any module not served as a JavaScript MIME type.
            $contentType = $this->fixProxyContentType($contentType, $body, $path);

            if (str_contains($contentType, 'text/html')) {
                $body = $this->normalizeProxyHtmlEntrypoint($body, (string) $session->workspace_path);
                $body = $this->removeViteClientScript($body);
            }

            // Some generated templates do not configure Vite base for dev mode,
            // causing absolute asset URLs like /@vite/client and /src/main.ts
            // that bypass our proxy route and fail in the app shell.
            // Rebase known absolute URLs back to this proxy prefix.
            $body = $this->rewriteProxyAssetUrls($body, $contentType, $base);

            // No URL rewriting needed — Vite's base config handles all path prefixing
            // natively, so every import/src/href already points to the proxy route.

            return response($body, $proxyResponse->status())
                ->header('Content-Type', $contentType)
                ->header('X-Preview-Port', $port)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('X-Content-Type-Options', 'nosniff');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to proxy to dev server: '.$e->getMessage(),
            ], 502);
        }
    }

    /**
     * Stop the preview session.
     */
    public function stop(Generation $generation): JsonResponse
    {
        $this->authorize('view', $generation);

        $session = $generation->previewSessions()
            ->whereIn('status', ['running', 'creating', 'installing', 'booting', 'error'])
            ->latest()
            ->first();

        if (! $session) {
            return response()->json([
                'success' => true,
                'message' => 'No active preview to stop',
            ]);
        }

        if ($session->status === 'running') {
            $this->workspaceService->stopDevServer($session);
        } else {
            $session->update([
                'status' => PreviewSession::STATUS_STOPPED,
                'stopped_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Preview stopped',
        ]);
    }

    /**
     * Serve a static HTML file directly from workspace (for HTML+CSS output).
     */
    public function serveStatic(Generation $generation, string $path = 'index.html'): Response|JsonResponse
    {
        $this->authorize('view', $generation);

        // For HTML+CSS, serve from progress_data directly
        $progressData = $generation->progress_data;

        if (empty($progressData)) {
            return response()->json(['error' => 'No generated content'], 404);
        }

        // If path matches a page name, serve that page's content
        $pageName = pathinfo($path, PATHINFO_FILENAME);
        $pageName = str_replace('_', '-', strtolower($pageName));

        foreach ($progressData as $page => $data) {
            $normalizedPage = strtolower(str_replace(['custom:', 'component:'], '', $page));
            if ($normalizedPage === $pageName && isset($data['content'])) {
                return response($data['content'], 200)
                    ->header('Content-Type', 'text/html; charset=utf-8');
            }
        }

        // Default: serve first completed page
        foreach ($progressData as $page => $data) {
            if (isset($data['content']) && $data['status'] === 'completed') {
                return response($data['content'], 200)
                    ->header('Content-Type', 'text/html; charset=utf-8');
            }
        }

        return response()->json(['error' => 'Page not found'], 404);
    }

    /**
     * Get file tree for multi-file projects.
     */
    public function fileTree(Generation $generation): JsonResponse
    {
        $this->authorize('view', $generation);

        $files = $generation->generationFiles()
            ->select(['id', 'file_path', 'file_type', 'is_scaffold'])
            ->orderBy('is_scaffold', 'desc')
            ->orderBy('file_path')
            ->get()
            ->map(fn ($file) => [
                'id' => $file->id,
                'path' => $file->file_path,
                'type' => $file->file_type,
                'is_scaffold' => $file->is_scaffold,
                'name' => basename($file->file_path),
                'directory' => dirname($file->file_path),
            ]);

        return response()->json([
            'success' => true,
            'files' => $files,
            'total' => $files->count(),
            'scaffold_count' => $files->where('is_scaffold', true)->count(),
            'component_count' => $files->where('is_scaffold', false)->count(),
        ]);
    }

    /**
     * Get content of a specific file by ID.
     */
    public function fileContent(Generation $generation, int $fileId): JsonResponse
    {
        $this->authorize('view', $generation);

        $file = $generation->generationFiles()->findOrFail($fileId);

        return response()->json([
            'success' => true,
            'file' => [
                'id' => $file->id,
                'path' => $file->file_path,
                'type' => $file->file_type,
                'content' => $file->file_content,
                'is_scaffold' => $file->is_scaffold,
            ],
        ]);
    }

    /**
     * Fix Content-Type for proxied Vite dev server responses.
     *
     * Vite transforms various file types (.vue, .css, .ts, .svelte, etc.)
     * into JavaScript modules during dev mode but may return incorrect or
     * empty Content-Type headers. Browsers enforce strict MIME checking for
     * ES module scripts and reject any module not served as text/javascript.
     */
    private function fixProxyContentType(string $contentType, string $body, string $path): string
    {
        $trimmedBody = ltrim($body);
        $isJavaScriptContent = str_starts_with($trimmedBody, 'import ')
            || str_starts_with($trimmedBody, 'import{')
            || str_starts_with($trimmedBody, 'import.meta')
            || str_starts_with($trimmedBody, 'export ')
            || str_starts_with($trimmedBody, 'export{')
            || str_starts_with($trimmedBody, 'const ')
            || str_starts_with($trimmedBody, 'var ')
            || str_starts_with($trimmedBody, 'let ')
            || str_starts_with($trimmedBody, '//');

        // Empty or missing Content-Type → detect from content
        if (empty(trim($contentType))) {
            if ($isJavaScriptContent) {
                return 'text/javascript';
            }

            if (str_starts_with($trimmedBody, '<!DOCTYPE') || str_starts_with($trimmedBody, '<html')) {
                return 'text/html; charset=utf-8';
            }

            return 'application/octet-stream';
        }

        // Vite returns CSS-imported-in-JS as text/css but body is JavaScript
        if (str_contains($contentType, 'text/css') && $isJavaScriptContent) {
            return 'text/javascript';
        }

        return $contentType;
    }

    /**
     * Rewrite absolute Vite asset URLs so requests stay under preview proxy.
     */
    private function rewriteProxyAssetUrls(string $body, string $contentType, string $base): string
    {
        $isHtml = str_contains($contentType, 'text/html');

        if (! $isHtml) {
            return $body;
        }

        $normalizedBase = rtrim($base, '/');
        $prefixMap = [
            '/@vite/' => $normalizedBase.'/@vite/',
            '/src/' => $normalizedBase.'/src/',
            '/node_modules/' => $normalizedBase.'/node_modules/',
            '/@id/' => $normalizedBase.'/@id/',
            '/__x00__' => $normalizedBase.'/__x00__',
            '/@fs/' => $normalizedBase.'/@fs/',
        ];

        $rewritten = $body;

        foreach ($prefixMap as $from => $to) {
            $rewritten = str_replace('"'.$from, '"'.$to, $rewritten);
            $rewritten = str_replace("'".$from, "'".$to, $rewritten);
            $rewritten = str_replace('('.$from, '('.$to, $rewritten);
            $rewritten = str_replace('='.$from, '='.$to, $rewritten);
            $rewritten = str_replace(' '.$from, ' '.$to, $rewritten);
            $rewritten = str_replace('`'.$from, '`'.$to, $rewritten);
        }

        return $rewritten;
    }

    /**
     * Remove Vite HMR client script in proxied iframe preview.
     */
    private function removeViteClientScript(string $html): string
    {
        return preg_replace(
            '/\s*<script[^>]*type=["\']module["\'][^>]*src=["\'][^"\']*\/(@vite\/client|generation\/\d+\/preview\/proxy\/@vite\/client)[^"\']*["\'][^>]*><\/script>\s*/i',
            PHP_EOL,
            $html
        ) ?? $html;
    }

    /**
     * Build target URL for proxy requests.
     */
    private function buildProxyTargetUrl(string $host, int $port, string $base, string $path, ?string $queryString): string
    {
        $normalizedBase = trim($base) === '' ? '' : '/'.trim($base, '/');
        $normalizedPath = trim($path) === '' ? '/' : '/'.ltrim($path, '/');

        $url = "http://{$host}:{$port}{$normalizedBase}{$normalizedPath}";

        if ($queryString) {
            $url .= '?'.$queryString;
        }

        return $url;
    }

    /**
     * Get query string for proxy forwarding, preserving Vite flag parameters.
     */
    private function getProxyForwardQueryString(Request $request): ?string
    {
        $rawQuery = (string) $request->server->get('QUERY_STRING', '');
        if ($rawQuery === '') {
            $rawQuery = (string) ($request->getQueryString() ?? '');
        }

        if ($rawQuery === '') {
            return null;
        }

        return $this->normalizeViteFlagQueryString($rawQuery);
    }

    /**
     * Keep Vite query flags in "bare" form (without trailing '=') so transforms are correct.
     */
    private function normalizeViteFlagQueryString(string $queryString): string
    {
        $normalized = preg_replace('/(^|&)vue=(?=&|$)/', '$1vue', $queryString) ?? $queryString;
        $normalized = preg_replace('/(^|&)lang\.(css|scss|sass|less|stylus|styl)=(?=&|$)/', '$1lang.$2', $normalized) ?? $normalized;

        return $normalized;
    }

    /**
     * Detect when proxy should retry request without configured base path.
     */
    private function shouldRetryProxyWithoutBase(\Illuminate\Http\Client\Response $response, string $path): bool
    {
        if (trim($path) === '') {
            return false;
        }

        $assetLikePath = preg_match('/\.(ts|tsx|js|jsx|css|vue|mjs)(\?.*)?$/i', $path) === 1
            || str_starts_with($path, '@vite/')
            || str_starts_with($path, 'src/')
            || str_starts_with($path, '@id/')
            || str_starts_with($path, '__x00__')
            || str_starts_with($path, 'node_modules/');

        if ($response->status() === 404) {
            return true;
        }

        if ($response->status() >= 500 && $assetLikePath) {
            return true;
        }

        $contentType = (string) ($response->header('Content-Type') ?? '');

        // Module/style endpoints returning HTML typically indicate incorrect base path.
        if (str_contains($contentType, 'text/html')) {
            return $assetLikePath;
        }

        return false;
    }

    /**
     * Normalize proxied HTML entry script and mount container to match actual workspace files.
     */
    private function normalizeProxyHtmlEntrypoint(string $html, string $workspacePath): string
    {
        if (trim($workspacePath) === '' || ! File::isDirectory($workspacePath)) {
            return $html;
        }

        $entryCandidates = ['/src/main.tsx', '/src/main.ts', '/src/main.jsx', '/src/main.js'];
        $existingEntry = null;

        foreach ($entryCandidates as $entry) {
            $entryPath = $workspacePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, ltrim($entry, '/'));
            if (File::exists($entryPath)) {
                $existingEntry = $entry;
                break;
            }
        }

        $normalizedHtml = $html;

        if ($existingEntry) {
            $replacedEntryScript = false;

            $normalizedHtml = preg_replace_callback(
                '/<script[^>]*type=["\']module["\'][^>]*src=["\']([^"\']+)["\'][^>]*><\/script>/i',
                function (array $matches) use ($entryCandidates, $existingEntry, &$replacedEntryScript): string {
                    $fullScriptTag = $matches[0];
                    $src = $matches[1];

                    $srcPath = parse_url($src, PHP_URL_PATH) ?? $src;
                    $srcPath = '/'.ltrim($srcPath, '/');

                    if (! in_array($srcPath, $entryCandidates, true)) {
                        return $fullScriptTag;
                    }

                    if ($srcPath === $existingEntry) {
                        $replacedEntryScript = true;

                        return $fullScriptTag;
                    }

                    $replacedEntryScript = true;

                    return str_replace($src, $existingEntry, $fullScriptTag);
                },
                $normalizedHtml
            ) ?? $normalizedHtml;

            if (! $replacedEntryScript) {
                if (str_contains($normalizedHtml, '</body>')) {
                    $normalizedHtml = str_replace(
                        '</body>',
                        '    <script type="module" src="'.$existingEntry.'"></script>'.PHP_EOL.'  </body>',
                        $normalizedHtml
                    );
                }
            }
        }

        $expectedMountId = $this->detectWorkspaceMountId($workspacePath);
        if ($expectedMountId !== null && preg_match('/<div[^>]*id=["\']([^"\']+)["\'][^>]*><\/div>/i', $normalizedHtml, $divMatch) === 1) {
            $currentId = $divMatch[1];
            if ($currentId !== $expectedMountId) {
                $normalizedHtml = preg_replace(
                    '/(<div[^>]*id=["\'])[^"\']+(["\'][^>]*><\/div>)/i',
                    '$1'.$expectedMountId.'$2',
                    $normalizedHtml,
                    1
                ) ?? $normalizedHtml;
            }
        }

        return $normalizedHtml;
    }

    /**
     * Detect mount id from workspace main entry files.
     */
    private function detectWorkspaceMountId(string $workspacePath): ?string
    {
        foreach (['main.tsx', 'main.ts', 'main.jsx', 'main.js'] as $entryFile) {
            $mainPath = $workspacePath.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.$entryFile;
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
     * No-op Vite client for iframe preview mode (disables HMR websocket).
     */
    private function viteClientStubModule(): string
    {
        return <<<'JS'
// Vite HMR client stub for proxy preview (HMR disabled, but CSS injection must work)
const __sheetsMap = new Map()

export function createHotContext() {
    return {
        accept() {},
        acceptExports() {},
        dispose() {},
        prune() {},
        invalidate() {},
        on() {},
        off() {},
        send() {},
        data: {},
    }
}

export function injectQuery(url) {
    return url
}

// updateStyle / removeStyle must work — Vite CSS modules call these to inject styles
export function updateStyle(id, css) {
    let style = __sheetsMap.get(id)
    if (!style) {
        style = document.createElement('style')
        style.setAttribute('type', 'text/css')
        style.setAttribute('data-vite-dev-id', id)
        document.head.appendChild(style)
        __sheetsMap.set(id, style)
    }
    style.textContent = css
}

export function removeStyle(id) {
    const style = __sheetsMap.get(id)
    if (style) {
        document.head.removeChild(style)
        __sheetsMap.delete(id)
    }
}

export const ErrorOverlay = class {
    constructor() {}
}
export default {}
JS;
    }
}
