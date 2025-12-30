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

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to start generation'
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
                'error' => 'An error occurred: ' . $e->getMessage()
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
                'error' => 'LLM request failed: ' . ($e->response?->body() ?? $e->getMessage()),
            ], 500);
            
        } catch (\Exception $e) {
            \Log::error('Generation exception', [
                'generation_id' => $generation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'An error occurred during generation: ' . $e->getMessage(),
            ], 500);
        }
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
                'error' => 'Failed to queue background generation: ' . $e->getMessage(),
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
        ]);

        try {
            $result = $this->generationService->refineGeneration(
                $generation,
                $validated['prompt']
            );

            if (!$result['success']) {
                return response()->json($result, 400);
            }

            return response()->json([
                'success' => true,
                'content' => $result['content'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show generation result
     */
    public function show(Generation $generation)
    {
        $this->authorize('view', $generation);

        $generation->load('project');

        return Inertia::render('Generation/Show', [
            'generation' => $generation,
        ]);
    }
}
