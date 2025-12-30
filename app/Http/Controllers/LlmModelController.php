<?php

namespace App\Http\Controllers;

use App\Services\OpenAICompatibleService;
use Illuminate\Http\Request;

class LlmModelController extends Controller
{
    public function __construct(
        private OpenAICompatibleService $llmService
    ) {}

    /**
     * Get available models for authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $models = $this->llmService->getAvailableModels($user->hasPremiumAccess());

        return response()->json([
            'models' => $models,
            'user_credits' => $user->credits,
        ]);
    }
}
