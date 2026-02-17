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
    protected ?string $apiKey;
    protected ?string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.llm.api_key');
        $this->baseUrl = config('services.llm.base_url', 'https://ai.sumopod.com/v1');
    }

    /**
     * Generate template code using specified model type
     * 
     * @param string $prompt The MCP prompt to send
     * @param string $modelType The model type identifier ('satset', 'expert')
     * @return array Response with success status, content, and token usage
     */
    public function generateTemplate(string $prompt, string $modelType): array
    {
        try {
            $model = $this->getModelByType($modelType);
            
            if (!$model) {
                return [
                    'success' => false,
                    'error' => "Model type '{$modelType}' not found or inactive",
                ];
            }

            // Route to appropriate provider
            if ($model->provider === 'gemini') {
                return $this->generateWithGemini($prompt, $model);
            } else {
                return $this->generateWithOpenAI($prompt, $model);
            }

        } catch (\Exception $e) {
            Log::error('LLM Service Exception', [
                'model_type' => $modelType,
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
     * Generate with Gemini provider
     */
    private function generateWithGemini(string $prompt, LlmModel $model): array
    {
        $baseUrl = $model->base_url ?: 'https://generativelanguage.googleapis.com/v1beta';
        $apiKey = $model->api_key;
        
        $http = Http::timeout(300);
        
        // Disable SSL verification for development environment only
        if (config('app.env') !== 'production') {
            $http = $http->withOptions(['verify' => false]);
        }
        
        $response = $http->post("{$baseUrl}/models/{$model->model_name}:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 1.0,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 60000,
            ],
        ]);

        if ($response->failed()) {
            Log::error('Gemini API Error', [
                'model' => $model->model_name,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to generate template: ' . $response->body(),
            ];
        }

        $data = $response->json();
        
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            Log::error('Invalid Gemini API Response', [
                'model' => $model->model_name,
                'response' => $data,
            ]);
            
            return [
                'success' => false,
                'error' => 'Invalid response from Gemini API',
            ];
        }

        $content = $data['candidates'][0]['content']['parts'][0]['text'];
        $usage = $data['usageMetadata'] ?? null;

        return [
            'success' => true,
            'content' => $content,
            'model' => $model->model_name,
            'usage' => [
                'input_tokens' => $usage['promptTokenCount'] ?? 0,
                'output_tokens' => $usage['candidatesTokenCount'] ?? 0,
                'total_tokens' => $usage['totalTokenCount'] ?? 0,
            ],
            'raw_response' => $response->body(),
        ];
    }

    /**
     * Generate with OpenAI-compatible provider
     */
    private function generateWithOpenAI(string $prompt, LlmModel $model): array
    {
        $baseUrl = $model->base_url ?: 'https://api.openai.com/v1';
        $apiKey = $model->api_key;
        
        $http = Http::timeout(300)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$apiKey}",
            ]);
        
        // Disable SSL verification for development environment only
        if (config('app.env') !== 'production') {
            $http = $http->withOptions(['verify' => false]);
        }
        
        $response = $http->post("{$baseUrl}/chat/completions", [
            'model' => $model->model_name,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => 60000,
            'temperature' => 1.0,
        ]);

        if ($response->failed()) {
            Log::error('OpenAI API Error', [
                'model' => $model->model_name,
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
            Log::error('Invalid OpenAI API Response', [
                'model' => $model->model_name,
                'response' => $data,
            ]);
            
            return [
                'success' => false,
                'error' => 'Invalid response from OpenAI API',
            ];
        }

        $content = $data['choices'][0]['message']['content'];
        $usage = $data['usage'] ?? null;

        return [
            'success' => true,
            'content' => $content,
            'model' => $model->model_name,
            'usage' => [
                'input_tokens' => $usage['prompt_tokens'] ?? 0,
                'output_tokens' => $usage['completion_tokens'] ?? 0,
                'total_tokens' => $usage['total_tokens'] ?? 0,
            ],
            'raw_response' => $response->body(),
        ];
    }

    /**
     * Get model by type (fast, professional, expert)
     */
    public function getModelByType(string $modelType): ?LlmModel
    {
        return LlmModel::where('model_type', $modelType)
            ->active()
            ->first();
    }

    /**
     * Calculate actual credits used based on model's base credits
     * 
     * This provides billing after generation completes
     */
    public function calculateActualCredits(
        string $modelType,
        int $inputTokens,
        int $outputTokens
    ): float {
        $model = $this->getModelByType($modelType);
        
        if (!$model) {
            return 0;
        }

        // Use base credits as configured by admin
        return $model->base_credits;
    }
}
