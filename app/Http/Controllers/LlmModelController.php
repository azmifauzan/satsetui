<?php

namespace App\Http\Controllers;

use App\Services\CreditEstimationService;
use App\Services\OpenAICompatibleService;
use Illuminate\Http\Request;

class LlmModelController extends Controller
{
    public function __construct(
        private OpenAICompatibleService $llmService,
        private CreditEstimationService $creditEstimation
    ) {}

    /**
     * Get available models for authenticated user with learned credit estimates
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $models = $this->llmService->getAvailableModels($user->hasPremiumAccess());
        
        // Get wizard context for better estimates (optional parameters from query)
        $category = $request->query('category');
        $framework = $request->query('framework');
        $pageCount = (int) ($request->query('page_count', 1));
        $componentCount = (int) ($request->query('component_count', 0));

        // Enhance models with learned credit estimates
        $enhancedModels = array_map(function ($model) use ($category, $framework, $pageCount, $componentCount) {
            $estimation = $this->creditEstimation->estimateCredits(
                $model['id'],
                $category,
                $framework,
                $pageCount,
                $componentCount
            );
            
            return array_merge($model, [
                'credits_required' => $estimation['base_credits'],
                'estimation_confidence' => $estimation['confidence'],
                'estimation_sample_count' => $estimation['sample_count'],
                'estimation_source' => $estimation['source'],
                // Keep original for comparison
                'original_estimate' => $model['credits_required'],
            ]);
        }, $models);

        return response()->json([
            'models' => $enhancedModels,
            'user_credits' => $user->credits,
        ]);
    }
}
