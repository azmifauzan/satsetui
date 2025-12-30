<?php

namespace App\Services;

use App\Models\LlmModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAI-Compatible API Service
 * 
 * Supports multiple LLM providers through OpenAI-compatible API format
 */
class OpenAICompatibleService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.llm.api_key');
        $this->baseUrl = config('services.llm.base_url');
    }

    /**
     * Generate template code using specified model
     * 
     * @param string $prompt The MCP prompt to send
     * @param string $modelName The model identifier (e.g., 'claude-haiku-4-5')
     * @return array Response with success status, content, and token usage
     */
    public function generateTemplate(string $prompt, string $modelName): array
    {
        try {
            $response = Http::timeout(180)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$this->apiKey}",
                ])
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $modelName,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => 60000,
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                Log::error('LLM API Error', [
                    'model' => $modelName,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Failed to generate template: ' . $response->body(),
                ];
            }

            $data = $response->json();
            
            if (!isset($data['choices'][0]['message']['content'])) {
                Log::error('Invalid LLM API Response', [
                    'model' => $modelName,
                    'response' => $data,
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Invalid response from LLM API',
                ];
            }

            $content = $data['choices'][0]['message']['content'];
            $usage = $data['usage'] ?? null;

            return [
                'success' => true,
                'content' => $content,
                'model' => $modelName,
                'usage' => [
                    'input_tokens' => $usage['prompt_tokens'] ?? 0,
                    'output_tokens' => $usage['completion_tokens'] ?? 0,
                    'total_tokens' => $usage['total_tokens'] ?? 0,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('LLM Service Exception', [
                'model' => $modelName,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Exception occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get available models for user
     * 
     * @param bool $isPremium Whether user has premium access (credits > 0)
     * @return array List of available models
     */
    public function getAvailableModels(bool $isPremium): array
    {
        $query = LlmModel::active()->ordered();
        
        if (!$isPremium) {
            $query->free();
        }
        
        return $query->get()
            ->map(fn($model) => [
                'id' => $model->name,
                'name' => $model->display_name,
                'description' => $model->description,
                'credits_required' => $model->estimated_credits_per_generation,
                'is_free' => $model->is_free,
            ])
            ->toArray();
    }

    /**
     * Get model by name
     */
    public function getModel(string $modelName): ?LlmModel
    {
        return LlmModel::where('name', $modelName)
            ->active()
            ->first();
    }

    /**
     * Calculate actual credits used based on token usage
     * 
     * This provides more accurate billing after generation completes
     */
    public function calculateActualCredits(
        string $modelName,
        int $inputTokens,
        int $outputTokens
    ): float {
        $model = $this->getModel($modelName);
        
        if (!$model) {
            return 0;
        }

        // Constants
        $usdToIdr = 18000;
        $margin = 0.05; // 5%
        $creditValue = 1000; // IDR

        // Calculate USD cost
        $inputCost = ($inputTokens / 1_000_000) * $model->input_price_per_million;
        $outputCost = ($outputTokens / 1_000_000) * $model->output_price_per_million;
        $totalUsd = $inputCost + $outputCost;

        // Convert to IDR with margin
        $totalIdr = $totalUsd * $usdToIdr * (1 + $margin);

        // Convert to credits
        return ceil($totalIdr / $creditValue);
    }
}
