<?php

namespace Database\Seeders;

use App\Models\LlmModel;
use Illuminate\Database\Seeder;

class LlmModelSeeder extends Seeder
{
    /**
     * Seed the 3 fixed LLM model types
     * 
     * Each model type (fast, professional, expert) can be configured by admin
     * with any provider (Gemini or OpenAI) and model.
     * 
     * Default configuration uses Gemini API key from environment if available.
     */
    public function run(): void
    {
        $defaultApiKey = config('services.gemini.api_key', '');
        $defaultBaseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        
        // If no API key, use a placeholder that admin must replace
        if (empty($defaultApiKey)) {
            $defaultApiKey = 'PLEASE_CONFIGURE_IN_ADMIN_PANEL';
        }
        
        $models = [
            [
                'model_type' => 'fast',
                'provider' => 'gemini',
                'model_name' => 'gemini-2.0-flash-exp',
                'api_key' => $defaultApiKey,
                'base_url' => $defaultBaseUrl,
                'base_credits' => 6,
                'is_active' => true,
            ],
            [
                'model_type' => 'professional',
                'provider' => 'gemini',
                'model_name' => 'gemini-2.5-pro-preview',
                'api_key' => $defaultApiKey,
                'base_url' => $defaultBaseUrl,
                'base_credits' => 13,
                'is_active' => true,
            ],
            [
                'model_type' => 'expert',
                'provider' => 'gemini',
                'model_name' => 'gemini-3-pro-preview',
                'api_key' => $defaultApiKey,
                'base_url' => $defaultBaseUrl,
                'base_credits' => 24,
                'is_active' => true,
            ],
        ];

        foreach ($models as $model) {
            LlmModel::updateOrCreate(
                ['model_type' => $model['model_type']],
                $model
            );
        }
    }
}

