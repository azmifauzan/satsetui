<?php

namespace App\Http\Controllers;

use App\Models\Generation;
use App\Services\WorkspaceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

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

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'status' => $session->status,
            'preview_type' => $session->preview_type,
            'port' => $session->preview_port,
            'url' => $session->preview_port
                ? "http://localhost:{$session->preview_port}"
                : null,
            'error' => $session->error_message,
            'started_at' => $session->started_at?->toISOString(),
            'last_activity' => $session->last_activity_at?->toISOString(),
        ]);
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
            ->where('status', 'running')
            ->latest()
            ->first();

        if (! $session) {
            return response()->json([
                'success' => true,
                'message' => 'No active preview to stop',
            ]);
        }

        $this->workspaceService->stopDevServer($session);

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
