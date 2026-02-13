<?php

namespace App\Http\Controllers;

use App\Models\LlmModel;
use App\Services\CreditEstimationService;
use Illuminate\Http\Request;

/**
 * LLM Model API Controller
 * 
 * Provides the 2 model types (Satset & Expert) to the wizard
 */
class LlmModelController extends Controller
{
    public function __construct(
        private CreditEstimationService $creditEstimation
    ) {}

    /**
     * Get available models for authenticated user with learned credit estimates
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get the 2 active models (Satset & Expert) ordered by type
        $models = LlmModel::active()->ordered()->get();
        
        // Get wizard context for better estimates (optional parameters from query)
        $category = $request->query('category');
        $framework = $request->query('framework');
        $pageCount = (int) ($request->query('page_count', 1));
        $componentCount = (int) ($request->query('component_count', 0));

        // Enhance models with learned credit estimates
        $enhancedModels = $models->map(function ($model) use ($category, $framework, $pageCount, $componentCount) {
            $estimation = $this->creditEstimation->estimateCredits(
                $model->model_type,
                $category,
                $framework,
                $pageCount,
                $componentCount
            );
            
            return [
                'id' => $model->model_type,
                'name' => $model->display_name,
                'description' => $model->description,
                'credits_required' => $estimation['base_credits'],
                'is_free' => false, // All models cost credits now
                'estimation_confidence' => $estimation['confidence'],
                'estimation_sample_count' => $estimation['sample_count'],
                'estimation_source' => $estimation['source'],
                // Keep original for comparison
                'original_estimate' => $model->base_credits,
            ];
        })->toArray();

        return response()->json([
            'models' => $enhancedModels,
            'user_credits' => $user->credits,
        ]);
    }
}

