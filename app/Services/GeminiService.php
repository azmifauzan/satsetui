<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $freeModel;
    protected string $premiumModel;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->baseUrl = config('services.gemini.base_url');
        $this->freeModel = config('services.gemini.free_model');
        $this->premiumModel = config('services.gemini.premium_model');
    }

    /**
     * Generate template code using Gemini API
     */
    public function generateTemplate(string $prompt, bool $isPremium = false): array
    {
        $model = $isPremium ? $this->premiumModel : $this->freeModel;
        
        try {
            $response = Http::timeout(120)
                ->post("{$this->baseUrl}/models/{$model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 8192,
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Failed to generate template: ' . $response->body(),
                ];
            }

            $data = $response->json();
            
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'success' => false,
                    'error' => 'Invalid response from Gemini API',
                ];
            }

            $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];

            return [
                'success' => true,
                'content' => $generatedText,
                'model' => $model,
            ];

        } catch (\Exception $e) {
            Log::error('Gemini Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Exception occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get available models
     */
    public function getAvailableModels(): array
    {
        return [
            [
                'id' => 'free',
                'name' => 'Gemini Flash (Free)',
                'model' => $this->freeModel,
                'description' => 'Fast and efficient model for quick generations',
                'credits' => 0,
                'isPremium' => false,
            ],
            [
                'id' => 'premium',
                'name' => 'Gemini Pro (Premium)',
                'model' => $this->premiumModel,
                'description' => 'Advanced model with better quality and reasoning',
                'credits' => 1,
                'isPremium' => true,
            ],
        ];
    }
}
