<?php

namespace App\Services;

use App\Models\Generation;
use App\Models\GenerationCost;
use App\Models\PageGeneration;
use App\Models\User;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\DB;

class CostTrackingService
{
    /**
     * Record cost for a page generation
     */
    public function recordCost(
        PageGeneration $pageGeneration,
        Generation $generation,
        User $user,
        string $modelName,
        string $provider,
        int $inputTokens,
        int $outputTokens,
        int $creditsCharged,
        int $processingTimeMs = null,
        string $rawRequest = null,
        string $rawResponse = null
    ): GenerationCost {
        // Get pricing dari model pricing configuration
        $pricing = $this->getModelPricing($provider, $modelName);
        
        // Calculate actual USD costs
        $inputCostUsd = ($inputTokens / 1000000) * $pricing['input_price_per_million'];
        $outputCostUsd = ($outputTokens / 1000000) * $pricing['output_price_per_million'];
        $totalCostUsd = $inputCostUsd + $outputCostUsd;
        
        // Get USD to local currency rate (from admin settings)
        $usdToLocalRate = AdminSetting::where('key', 'usd_to_local_rate')->value('value') ?? 15000; // Default IDR rate
        $totalCostLocal = $totalCostUsd * $usdToLocalRate;
        
        // Calculate profit margin
        // Assume 1 credit = 100 IDR (configurable)
        $creditValue = AdminSetting::where('key', 'credit_value_local')->value('value') ?? 100;
        $revenueLocal = $creditsCharged * $creditValue;
        
        $profitMarginPercent = 0;
        if ($revenueLocal > 0) {
            $profitMarginPercent = (($revenueLocal - $totalCostLocal) / $revenueLocal) * 100;
        }

        return GenerationCost::create([
            'generation_id' => $generation->id,
            'page_generation_id' => $pageGeneration->id,
            'user_id' => $user->id,
            'model_name' => $modelName,
            'provider' => $provider,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $inputTokens + $outputTokens,
            'input_price_per_million' => $pricing['input_price_per_million'],
            'output_price_per_million' => $pricing['output_price_per_million'],
            'input_cost_usd' => $inputCostUsd,
            'output_cost_usd' => $outputCostUsd,
            'total_cost_usd' => $totalCostUsd,
            'credits_charged' => $creditsCharged,
            'profit_margin_percent' => round($profitMarginPercent, 2),
            'processing_time_ms' => $processingTimeMs,
            'usd_to_local_rate' => $usdToLocalRate,
            'total_cost_local' => $totalCostLocal,
            'raw_request' => $rawRequest,
            'raw_response' => $rawResponse,
        ]);
    }

    /**
     * Get model pricing configuration
     */
    private function getModelPricing(string $provider, string $modelName): array
    {
        // Default pricing (harus di-update sesuai provider actual)
        $pricing = [
            'openai' => [
                'gpt-4' => [
                    'input_price_per_million' => 30.00,
                    'output_price_per_million' => 60.00,
                ],
                'gpt-4-turbo' => [
                    'input_price_per_million' => 10.00,
                    'output_price_per_million' => 30.00,
                ],
                'gpt-3.5-turbo' => [
                    'input_price_per_million' => 0.50,
                    'output_price_per_million' => 1.50,
                ],
            ],
            'anthropic' => [
                'claude-3-opus' => [
                    'input_price_per_million' => 15.00,
                    'output_price_per_million' => 75.00,
                ],
                'claude-3-sonnet' => [
                    'input_price_per_million' => 3.00,
                    'output_price_per_million' => 15.00,
                ],
            ],
            'google' => [
                'gemini-2.0-flash-exp' => [
                    'input_price_per_million' => 0.075,
                    'output_price_per_million' => 0.30,
                ],
                'gemini-1.5-pro' => [
                    'input_price_per_million' => 1.25,
                    'output_price_per_million' => 5.00,
                ],
            ],
        ];

        // Try to get from database first (admin can update pricing)
        $dbPricing = AdminSetting::where('key', "pricing_{$provider}_{$modelName}")->value('value');
        if ($dbPricing) {
            return json_decode($dbPricing, true);
        }

        // Fallback to hardcoded pricing
        $providerLower = strtolower($provider);
        $modelLower = strtolower($modelName);
        
        if (isset($pricing[$providerLower][$modelLower])) {
            return $pricing[$providerLower][$modelLower];
        }

        // Default fallback if model not found
        return [
            'input_price_per_million' => 1.00,
            'output_price_per_million' => 2.00,
        ];
    }

    /**
     * Get cost statistics for admin dashboard
     */
    public function getStatistics(int $days = 30): array
    {
        $since = now()->subDays($days);

        $costs = GenerationCost::where('created_at', '>=', $since);

        return [
            'total_cost_usd' => $costs->sum('total_cost_usd'),
            'total_cost_local' => $costs->sum('total_cost_local'),
            'total_credits_charged' => $costs->sum('credits_charged'),
            'total_tokens_used' => $costs->sum('total_tokens'),
            'total_input_tokens' => $costs->sum('input_tokens'),
            'total_output_tokens' => $costs->sum('output_tokens'),
            'average_profit_margin' => $costs->avg('profit_margin_percent'),
            'total_generations' => $costs->count(),
            'cost_by_provider' => $this->getCostByProvider($days),
            'cost_by_model' => $this->getCostByModel($days),
            'profitability' => $this->getProfitability($days),
        ];
    }

    /**
     * Get costs grouped by provider
     */
    private function getCostByProvider(int $days): array
    {
        $since = now()->subDays($days);

        return GenerationCost::where('created_at', '>=', $since)
            ->select('provider', 
                DB::raw('SUM(total_cost_usd) as total_cost_usd'),
                DB::raw('SUM(credits_charged) as credits_charged'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(profit_margin_percent) as avg_profit_margin')
            )
            ->groupBy('provider')
            ->get()
            ->toArray();
    }

    /**
     * Get costs grouped by model
     */
    private function getCostByModel(int $days): array
    {
        $since = now()->subDays($days);

        return GenerationCost::where('created_at', '>=', $since)
            ->select('model_name', 
                DB::raw('SUM(total_cost_usd) as total_cost_usd'),
                DB::raw('SUM(credits_charged) as credits_charged'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(profit_margin_percent) as avg_profit_margin')
            )
            ->groupBy('model_name')
            ->orderBy(DB::raw('SUM(total_cost_usd)'), 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Calculate overall profitability
     */
    private function getProfitability(int $days): array
    {
        $since = now()->subDays($days);

        $costs = GenerationCost::where('created_at', '>=', $since);
        
        $totalCostLocal = $costs->sum('total_cost_local');
        $totalCreditsCharged = $costs->sum('credits_charged');
        
        // 1 credit = 100 IDR (configurable)
        $creditValue = AdminSetting::where('key', 'credit_value_local')->value('value') ?? 100;
        $totalRevenueLocal = $totalCreditsCharged * $creditValue;
        
        $profitLocal = $totalRevenueLocal - $totalCostLocal;
        $profitMarginPercent = $totalRevenueLocal > 0 
            ? ($profitLocal / $totalRevenueLocal) * 100 
            : 0;

        return [
            'total_revenue_local' => $totalRevenueLocal,
            'total_cost_local' => $totalCostLocal,
            'profit_local' => $profitLocal,
            'profit_margin_percent' => round($profitMarginPercent, 2),
            'credits_charged' => $totalCreditsCharged,
            'credit_value' => $creditValue,
        ];
    }

    /**
     * Get top 10 most expensive generations
     */
    public function getMostExpensiveGenerations(int $limit = 10)
    {
        return GenerationCost::with(['generation', 'user'])
            ->orderBy('total_cost_usd', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get models sorted by profitability
     */
    public function getModelsByProfitability()
    {
        return GenerationCost::select('model_name', 'provider',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('SUM(total_cost_usd) as total_cost'),
                DB::raw('SUM(credits_charged) as total_credits'),
                DB::raw('AVG(profit_margin_percent) as avg_profit_margin')
            )
            ->groupBy('model_name', 'provider')
            ->orderBy('avg_profit_margin', 'desc')
            ->get();
    }
}
