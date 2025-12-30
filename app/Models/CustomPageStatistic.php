<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * CustomPageStatistic Model
 *
 * Tracks custom page usage across all generations for admin visibility.
 * Used to:
 * - Identify popular custom pages that could be promoted to predefined options
 * - Understand user needs and feature requests
 * - Track success/failure rates for custom page types
 */
class CustomPageStatistic extends Model
{
    protected $fillable = [
        'normalized_name',
        'original_name',
        'sample_description',
        'usage_count',
        'success_count',
        'failure_count',
        'avg_generation_time_ms',
        'avg_tokens_used',
        'promoted_to_predefined',
        'promoted_at',
        'first_used_at',
        'last_used_at',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'success_count' => 'integer',
        'failure_count' => 'integer',
        'avg_generation_time_ms' => 'decimal:2',
        'avg_tokens_used' => 'decimal:2',
        'promoted_to_predefined' => 'boolean',
        'promoted_at' => 'datetime',
        'first_used_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Normalize a page name for consistent tracking.
     *
     * Normalization rules:
     * - Lowercase
     * - Trim whitespace
     * - Replace multiple spaces with single space
     * - Remove special characters except hyphens and underscores
     */
    public static function normalizeName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/\s+/', ' ', $name);
        $name = preg_replace('/[^a-z0-9\s\-_]/', '', $name);
        $name = str_replace(' ', '-', $name);
        return $name;
    }

    /**
     * Record usage of a custom page.
     *
     * @param string $pageName Original page name from user
     * @param string|null $description Page description
     * @param bool $wasSuccessful Whether generation succeeded
     * @param int|null $generationTimeMs Processing time in milliseconds
     * @param int|null $tokensUsed Total tokens used
     */
    public static function recordUsage(
        string $pageName,
        ?string $description = null,
        bool $wasSuccessful = false,
        ?int $generationTimeMs = null,
        ?int $tokensUsed = null
    ): self {
        $normalizedName = self::normalizeName($pageName);
        $now = now();

        $stat = self::firstOrNew(['normalized_name' => $normalizedName]);

        if (!$stat->exists) {
            // New custom page
            $stat->original_name = $pageName;
            $stat->sample_description = $description;
            $stat->usage_count = 1;
            $stat->success_count = $wasSuccessful ? 1 : 0;
            $stat->failure_count = $wasSuccessful ? 0 : 1;
            $stat->avg_generation_time_ms = $generationTimeMs;
            $stat->avg_tokens_used = $tokensUsed;
            $stat->first_used_at = $now;
            $stat->last_used_at = $now;
        } else {
            // Update existing statistics
            $stat->usage_count++;
            
            if ($wasSuccessful) {
                $stat->success_count++;
            } else {
                $stat->failure_count++;
            }

            // Update rolling averages
            if ($generationTimeMs !== null) {
                if ($stat->avg_generation_time_ms === null) {
                    $stat->avg_generation_time_ms = $generationTimeMs;
                } else {
                    // Simple rolling average
                    $stat->avg_generation_time_ms = (
                        ($stat->avg_generation_time_ms * ($stat->usage_count - 1)) + $generationTimeMs
                    ) / $stat->usage_count;
                }
            }

            if ($tokensUsed !== null) {
                if ($stat->avg_tokens_used === null) {
                    $stat->avg_tokens_used = $tokensUsed;
                } else {
                    $stat->avg_tokens_used = (
                        ($stat->avg_tokens_used * ($stat->usage_count - 1)) + $tokensUsed
                    ) / $stat->usage_count;
                }
            }

            $stat->last_used_at = $now;

            // Update description if we have a better one
            if ($description && (!$stat->sample_description || strlen($description) > strlen($stat->sample_description))) {
                $stat->sample_description = $description;
            }
        }

        $stat->save();
        return $stat;
    }

    /**
     * Get success rate as percentage.
     */
    public function getSuccessRate(): float
    {
        if ($this->usage_count === 0) {
            return 0;
        }
        return ($this->success_count / $this->usage_count) * 100;
    }

    /**
     * Check if this page is a candidate for promotion to predefined.
     *
     * Criteria:
     * - At least 10 uses
     * - At least 70% success rate
     * - Not already promoted
     */
    public function isPromotionCandidate(): bool
    {
        return !$this->promoted_to_predefined
            && $this->usage_count >= 10
            && $this->getSuccessRate() >= 70;
    }

    /**
     * Mark this page as promoted to predefined options.
     */
    public function markAsPromoted(): void
    {
        $this->promoted_to_predefined = true;
        $this->promoted_at = now();
        $this->save();
    }

    /**
     * Get top custom pages by usage.
     *
     * @param int $limit Number of results
     * @param bool $excludePromoted Whether to exclude already promoted pages
     */
    public static function getTopByUsage(int $limit = 10, bool $excludePromoted = true): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::query()
            ->orderBy('usage_count', 'desc')
            ->limit($limit);

        if ($excludePromoted) {
            $query->where('promoted_to_predefined', false);
        }

        return $query->get();
    }

    /**
     * Get promotion candidates ordered by usage.
     */
    public static function getPromotionCandidates(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::query()
            ->where('promoted_to_predefined', false)
            ->where('usage_count', '>=', 10)
            ->whereRaw('(success_count * 100.0 / usage_count) >= 70')
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
