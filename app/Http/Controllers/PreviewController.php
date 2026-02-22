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

        if ($generation->status !== 'completed') {
            return response()->json([
                'success' => false,
                'error' => 'Generation must be completed before preview',
            ], 422);
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
            '[%s] Generation status: %s (%d/%d)',
            now()->format('H:i:s'),
            $generation->current_status ?? $generation->status,
            (int) $generation->current_page_index,
            (int) $generation->total_pages
        );

        if ($session) {
            $lines[] = sprintf(
                '[%s] Preview session: %s%s',
                now()->format('H:i:s'),
                $session->status,
                $session->preview_port ? " on :{$session->preview_port}" : ''
            );

            if ($session->error_message) {
                $lines[] = sprintf('[%s] ERROR: %s', now()->format('H:i:s'), $session->error_message);
            }
        }

        $prewarmLog = storage_path("logs/preview-prewarm-{$generation->id}.log");
        if (File::exists($prewarmLog)) {
            $lines = array_merge($lines, $this->tailLines($prewarmLog, (int) floor($maxLines / 2)));
        }

        if ($session?->preview_port) {
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
        $phase = $status;
        $detail = 'Waiting';

        $percentage = match ($status) {
            'creating' => 15,
            'installing' => 45,
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

        $targetUrl = "http://127.0.0.1:{$port}/{$path}";
        $queryString = $request->getQueryString();
        if ($queryString) {
            $targetUrl .= '?'.$queryString;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => $request->header('Accept', '*/*'),
                    'Accept-Encoding' => 'identity', // Don't compress for proxy
                ])
                ->get($targetUrl);

            $contentType = $response->header('Content-Type') ?? 'text/html';

            return response($response->body(), $response->status())
                ->header('Content-Type', $contentType)
                ->header('X-Preview-Port', $port);
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
            ->whereIn('status', ['running', 'creating', 'installing', 'error'])
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
}
