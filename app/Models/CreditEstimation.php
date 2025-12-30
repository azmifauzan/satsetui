<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CreditEstimation Model
 *
 * Stores historical data about credit predictions vs actuals for learning.
 * Used by the credit learning algorithm to improve future estimates.
 */
class CreditEstimation extends Model
{
    protected $fillable = [
        'generation_id',
        'model_id',
        'page_type',
        'page_name',
        'category',
        'framework',
        'output_format',
        'page_count',
        'component_count',
        'estimated_credits',
        'actual_credits',
        'error_percentage',
        'estimated_tokens',
        'actual_input_tokens',
        'actual_output_tokens',
        'was_successful',
    ];

    protected $casts = [
        'page_count' => 'integer',
        'component_count' => 'integer',
        'estimated_credits' => 'decimal:4',
        'actual_credits' => 'decimal:4',
        'error_percentage' => 'decimal:4',
        'estimated_tokens' => 'integer',
        'actual_input_tokens' => 'integer',
        'actual_output_tokens' => 'integer',
        'was_successful' => 'boolean',
    ];

    /**
     * Get the parent generation.
     */
    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class);
    }

    /**
     * Record a credit estimation result.
     *
     * @param Generation $generation Parent generation
     * @param string $pageType 'predefined' or 'custom'
     * @param string|null $pageName Page name (for predefined)
     * @param float $estimatedCredits Credits estimated before generation
     * @param float|null $actualCredits Actual credits used (null if failed)
     * @param int|null $estimatedTokens Estimated token count
     * @param int|null $actualInputTokens Actual input tokens
     * @param int|null $actualOutputTokens Actual output tokens
     * @param bool $wasSuccessful Whether generation succeeded
     */
    public static function record(
        Generation $generation,
        string $pageType,
        ?string $pageName,
        float $estimatedCredits,
        ?float $actualCredits = null,
        ?int $estimatedTokens = null,
        ?int $actualInputTokens = null,
        ?int $actualOutputTokens = null,
        bool $wasSuccessful = false
    ): self {
        $blueprint = $generation->blueprint_json ?? [];

        $errorPercentage = null;
        if ($actualCredits !== null && $estimatedCredits > 0) {
            $errorPercentage = (($actualCredits - $estimatedCredits) / $estimatedCredits) * 100;
        }

        return self::create([
            'generation_id' => $generation->id,
            'model_id' => $generation->model_used,
            'page_type' => $pageType,
            'page_name' => $pageName,
            'category' => $blueprint['category'] ?? $generation->category,
            'framework' => $blueprint['framework'] ?? $generation->framework,
            'output_format' => $blueprint['outputFormat'] ?? $generation->output_format,
            'page_count' => $generation->total_pages,
            'component_count' => count($blueprint['components'] ?? []) + count($blueprint['customComponents'] ?? []),
            'estimated_credits' => $estimatedCredits,
            'actual_credits' => $actualCredits,
            'error_percentage' => $errorPercentage,
            'estimated_tokens' => $estimatedTokens,
            'actual_input_tokens' => $actualInputTokens,
            'actual_output_tokens' => $actualOutputTokens,
            'was_successful' => $wasSuccessful,
        ]);
    }

    /**
     * Get average error percentage for a specific model.
     */
    public static function getAverageErrorForModel(string $modelId): ?float
    {
        return self::where('model_id', $modelId)
            ->where('was_successful', true)
            ->whereNotNull('error_percentage')
            ->avg('error_percentage');
    }

    /**
     * Get average error percentage for a specific page type.
     */
    public static function getAverageErrorForPageType(string $pageType): ?float
    {
        return self::where('page_type', $pageType)
            ->where('was_successful', true)
            ->whereNotNull('error_percentage')
            ->avg('error_percentage');
    }

    /**
     * Get average tokens for a specific page name (predefined pages).
     */
    public static function getAverageTokensForPage(string $pageName): ?array
    {
        $result = self::where('page_name', $pageName)
            ->where('was_successful', true)
            ->selectRaw('AVG(actual_input_tokens) as avg_input, AVG(actual_output_tokens) as avg_output')
            ->first();

        if (!$result || $result->avg_input === null) {
            return null;
        }

        return [
            'input' => (int) round($result->avg_input),
            'output' => (int) round($result->avg_output),
        ];
    }

    /**
     * Get credit estimation adjustment factor based on historical data.
     *
     * Returns a multiplier to adjust future estimates based on past accuracy.
     * - Returns 1.0 if no historical data
     * - Returns > 1.0 if we tend to under-estimate (need to charge more)
     * - Returns < 1.0 if we tend to over-estimate (can charge less)
     *
     * @param string $modelId LLM model ID
     * @param string|null $category Template category (optional)
     * @param string|null $framework CSS framework (optional)
     * @param int $minSamples Minimum samples required for adjustment
     */
    public static function getAdjustmentFactor(
        string $modelId,
        ?string $category = null,
        ?string $framework = null,
        int $minSamples = 10
    ): float {
        $query = self::where('model_id', $modelId)
            ->where('was_successful', true)
            ->whereNotNull('error_percentage');

        if ($category) {
            $query->where('category', $category);
        }

        if ($framework) {
            $query->where('framework', $framework);
        }

        $count = $query->count();
        if ($count < $minSamples) {
            return 1.0; // Not enough data, use base estimate
        }

        $avgError = $query->avg('error_percentage');
        
        // Convert error percentage to adjustment factor
        // If avgError is +10% (under-estimated), return 1.10
        // If avgError is -10% (over-estimated), return 0.90
        return 1 + ($avgError / 100);
    }

    /**
     * Get statistics for admin dashboard.
     */
    public static function getStatistics(): array
    {
        $total = self::count();
        $successful = self::where('was_successful', true)->count();
        
        return [
            'total_estimations' => $total,
            'successful_generations' => $successful,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'avg_error_percentage' => self::where('was_successful', true)->avg('error_percentage'),
            'avg_actual_credits' => self::where('was_successful', true)->avg('actual_credits'),
            'total_credits_estimated' => self::sum('estimated_credits'),
            'total_credits_actual' => self::whereNotNull('actual_credits')->sum('actual_credits'),
            'by_model' => self::selectRaw('model_id, COUNT(*) as count, AVG(error_percentage) as avg_error')
                ->where('was_successful', true)
                ->groupBy('model_id')
                ->get()
                ->keyBy('model_id')
                ->toArray(),
            'by_page_type' => self::selectRaw('page_type, COUNT(*) as count, AVG(error_percentage) as avg_error')
                ->where('was_successful', true)
                ->groupBy('page_type')
                ->get()
                ->keyBy('page_type')
                ->toArray(),
        ];
    }
}
