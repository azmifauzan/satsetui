<?php

namespace App\Http\Controllers;

use App\Models\Generation;
use App\Models\Project;
use App\Services\GenerationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GenerationController extends Controller
{
    use AuthorizesRequests;

    protected GenerationService $generationService;

    public function __construct(GenerationService $generationService)
    {
        $this->generationService = $generationService;
    }

    /**
     * Generate template from wizard blueprint
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'blueprint' => 'required|array',
            'project_name' => 'nullable|string|max:255',
            'model_name' => 'nullable|string|max:100', // Optional model selection
        ]);

        $user = Auth::user();

        try {
            $result = $this->generationService->startGeneration(
                $validated['blueprint'],
                $user,
                $validated['model_name'] ?? null,
                $validated['project_name'] ?? null
            );

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to start generation',
                ], 400);
            }

            // Return JSON for AJAX request
            return response()->json([
                'success' => true,
                'generation_id' => $result['generation_id'],
                'total_pages' => $result['total_pages'],
                'model' => $result['model'],
                'credits_charged' => $result['credits_charged'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate next page (called via AJAX during progressive generation)
     */
    public function generateNext(Generation $generation)
    {
        $this->authorize('view', $generation);

        try {
            $result = $this->generationService->generateNextPage($generation);

            return response()->json($result);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('Generation connection error', [
                'generation_id' => $generation->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unable to connect to LLM service. Please check your API configuration.',
            ], 503);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            \Log::error('Generation request error', [
                'generation_id' => $generation->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'LLM request failed: '.($e->response?->body() ?? $e->getMessage()),
            ], 500);

        } catch (\Exception $e) {
            \Log::error('Generation exception', [
                'generation_id' => $generation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred during generation: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retry failed pages — resets failed pages to pending and restarts from first failure
     */
    public function retryFailedPages(Generation $generation)
    {
        $this->authorize('view', $generation);

        $progressData = $generation->progress_data;
        $pages = array_keys($progressData);
        $firstFailedIndex = null;
        $failedPageNames = [];

        foreach ($pages as $index => $pageName) {
            if (($progressData[$pageName]['status'] ?? '') === 'failed') {
                if ($firstFailedIndex === null) {
                    $firstFailedIndex = $index;
                }
                $failedPageNames[] = $pageName;
                $progressData[$pageName]['status'] = 'pending';
                $progressData[$pageName]['error'] = null;
            }
        }

        if ($firstFailedIndex === null) {
            return response()->json([
                'success' => false,
                'error' => 'No failed pages found to retry.',
            ], 400);
        }

        \Log::info("Retrying {$generation->id} failed pages", [
            'failed_pages' => $failedPageNames,
            'restarting_from_index' => $firstFailedIndex,
        ]);

        $generation->update([
            'progress_data' => $progressData,
            'status' => 'generating',
            'current_status' => 'Retrying failed pages...',
            'current_page_index' => $firstFailedIndex,
            'error_message' => null,
            'completed_at' => null,
        ]);

        $generation->project->update(['status' => 'generating']);

        return response()->json([
            'success' => true,
            'failed_pages' => $failedPageNames,
            'retry_from_index' => $firstFailedIndex,
        ]);
    }

    /**
     * Continue generation in background
     */
    public function continueInBackground(Generation $generation)
    {
        $this->authorize('view', $generation);

        try {
            // Dispatch job to queue
            \App\Jobs\ProcessTemplateGeneration::dispatch($generation);

            return response()->json([
                'success' => true,
                'message' => 'Generation will continue in background. You will be notified when completed.',
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to queue background generation', [
                'generation_id' => $generation->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to queue background generation: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get generation progress
     */
    public function progress(Generation $generation)
    {
        $this->authorize('view', $generation);

        return response()->json([
            'status' => $generation->status,
            'current_status' => $generation->current_status,
            'current_page_index' => $generation->current_page_index,
            'total_pages' => $generation->total_pages,
            'progress_percentage' => round(($generation->current_page_index / $generation->total_pages) * 100),
            'progress_data' => $generation->progress_data,
            'completed_at' => $generation->completed_at,
        ]);
    }

    /**
     * Refine generation with additional prompt
     */
    public function refine(Request $request, Generation $generation)
    {
        $this->authorize('view', $generation);

        $validated = $request->validate([
            'prompt' => 'required|string|max:2000',
            'page_name' => 'nullable|string|max:100',
            'model' => 'nullable|string',
        ]);

        try {
            $result = $this->generationService->refineGeneration(
                $generation,
                $validated['prompt'],
                $validated['page_name'] ?? null,
                $validated['model'] ?? $generation->model_used
            );

            if (! $result['success']) {
                return response()->json($result, 400);
            }

            return response()->json([
                'success' => true,
                'content' => $result['content'],
                'page_name' => $result['page_name'] ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stream generation - SSE endpoint for live preview
     * Generates pages one by one and streams content back
     */
    public function stream(Generation $generation)
    {
        $this->authorize('view', $generation);

        // SSE streams can take a long time - remove PHP time limit
        set_time_limit(0);
        ini_set('max_execution_time', '0');

        return response()->stream(function () use ($generation) {
            // Disable output buffering
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $progressData = $generation->progress_data;
            $pages = array_keys($progressData);

            while ($generation->current_page_index < $generation->total_pages) {
                $currentIndex = $generation->current_page_index;
                $currentPage = $pages[$currentIndex] ?? null;
                if (! $currentPage) {
                    break;
                }

                // Send status update
                echo 'data: '.json_encode([
                    'type' => 'status',
                    'page' => $currentPage,
                    'index' => $currentIndex,
                    'total' => $generation->total_pages,
                    'message' => "Generating {$currentPage}...",
                ])."\n\n";

                if (connection_aborted()) {
                    break;
                }

                flush();

                try {
                    $result = app(GenerationService::class)->generateNextPage($generation);
                    $generation->refresh();

                    if ($result['success']) {
                        // Get page content from progress_data
                        $pageContent = $generation->progress_data[$currentPage]['content'] ?? '';

                        echo 'data: '.json_encode([
                            'type' => 'page_complete',
                            'page' => $currentPage,
                            'content' => $pageContent,
                            'index' => $currentIndex + 1,
                            'total' => $generation->total_pages,
                            'completed' => $result['completed'] ?? false,
                        ])."\n\n";
                    } else {
                        echo 'data: '.json_encode([
                            'type' => 'page_error',
                            'page' => $currentPage,
                            'error' => $result['error'] ?? 'Unknown error',
                        ])."\n\n";
                        break;
                    }

                    flush();

                    if ($result['completed'] ?? false) {
                        break;
                    }
                } catch (\Exception $e) {
                    echo 'data: '.json_encode([
                        'type' => 'error',
                        'message' => $e->getMessage(),
                    ])."\n\n";
                    flush();
                    break;
                }
            }

            // Send final complete event
            $generation->refresh();
            echo 'data: '.json_encode([
                'type' => 'complete',
                'status' => $generation->status,
                'total_pages' => $generation->total_pages,
            ])."\n\n";
            flush();

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Show generation result
     */
    public function show(Generation $generation)
    {
        $this->authorize('view', $generation);

        $generation->load(['project', 'refinementMessages']);

        return Inertia::render('Generation/Show', [
            'generation' => $generation,
            'userCredits' => Auth::user()->credits ?? 0,
        ]);
    }

    /**
     * Update project name
     */
    public function updateName(Request $request, Generation $generation)
    {
        $this->authorize('view', $generation);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $generation->project->update(['name' => $validated['name']]);

        return response()->json(['success' => true]);
    }
}
