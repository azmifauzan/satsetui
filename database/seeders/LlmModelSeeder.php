<?php

namespace Database\Seeders;

use App\Models\LlmModel;
use Illuminate\Database\Seeder;

class LlmModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Credit calculation formula:
     * credits = ((input_price * estimated_input_tokens) + (output_price * estimated_output_tokens)) 
     *           * usd_to_idr_rate * (1 + margin) / credit_value
     * 
     * Assumptions:
     * - Estimated input: 10,000 tokens (10K)
     * - Estimated output: 50,000 tokens (50K)
     * - USD to IDR rate: 18,000
     * - System margin: 5%
     * - 1 credit = 1,000 IDR
     */
    public function run(): void
    {
        $models = [
            [
                'name' => 'gemini-2.5-flash',
                'display_name' => 'Gemini 2.5 Flash',
                'description' => 'Cepat dan efisien untuk generasi template dasar',
                'input_price_per_million' => 0.3000000,
                'output_price_per_million' => 2.5000000,
                'estimated_credits_per_generation' => 0, // Free tier model
                'context_length' => 1000000, // 1M tokens
                'is_free' => true, // Free users default
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'gpt-5.1-codex-mini',
                'display_name' => 'GPT-5.1 Codex Mini',
                'description' => 'Model ringan untuk generasi kode cepat',
                'input_price_per_million' => 0.2500000,
                'output_price_per_million' => 2.0000000,
                'estimated_credits_per_generation' => 2, // 1.94 rounded up
                'context_length' => 16000, // 16k tokens
                'is_free' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'claude-haiku-4-5',
                'display_name' => 'Claude Haiku 4.5',
                'description' => 'Cepat dan efisien dengan kualitas Claude',
                'input_price_per_million' => 1.0000000,
                'output_price_per_million' => 5.0000000,
                'estimated_credits_per_generation' => 6, // 5.67 rounded up
                'context_length' => 200000, // 200k tokens
                'is_free' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'gpt-5.1-codex',
                'display_name' => 'GPT-5.1 Codex',
                'description' => 'Model premium untuk generasi kode berkualitas tinggi',
                'input_price_per_million' => 1.2500000,
                'output_price_per_million' => 10.0000000,
                'estimated_credits_per_generation' => 10, // 9.69 rounded up
                'context_length' => 128000, // 128k tokens
                'is_free' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'gemini-3-pro-preview',
                'display_name' => 'Gemini 3 Pro Preview',
                'description' => 'Model premium Google dengan kemampuan lanjutan',
                'input_price_per_million' => 2.0000000,
                'output_price_per_million' => 12.0000000,
                'estimated_credits_per_generation' => 12, // 11.72 rounded up
                'context_length' => 2000000, // 2M tokens
                'is_free' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'claude-sonnet-4-5',
                'display_name' => 'Claude Sonnet 4.5',
                'description' => 'Model terbaik untuk template kompleks dan berkualitas tinggi',
                'input_price_per_million' => 3.0000000,
                'output_price_per_million' => 15.0000000,
                'estimated_credits_per_generation' => 15, // 14.74 rounded up
                'context_length' => 200000, // 200k tokens
                'is_free' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($models as $model) {
            LlmModel::updateOrCreate(
                ['name' => $model['name']],
                $model
            );
        }
    }
}
