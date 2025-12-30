<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PageGeneration Model
 *
 * Represents a single page generation within a template generation request.
 * Each page is generated separately with its own MCP prompt for:
 * - Better error recovery (can regenerate single page)
 * - Credit learning (track actual vs estimated costs per page type)
 * - Debugging and audit trail
 */
class PageGeneration extends Model
{
    protected $fillable = [
        'generation_id',
        'page_name',
        'page_type',
        'page_index',
        'mcp_prompt',
        'llm_response',
        'generated_content',
        'status',
        'error_message',
        'input_tokens',
        'output_tokens',
        'processing_time_ms',
        'estimated_credits',
        'actual_credits',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'page_index' => 'integer',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'processing_time_ms' => 'integer',
        'estimated_credits' => 'decimal:4',
        'actual_credits' => 'decimal:4',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Page type constants
     */
    public const TYPE_PREDEFINED = 'predefined';
    public const TYPE_CUSTOM = 'custom';

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_GENERATING = 'generating';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Get the parent generation.
     */
    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class);
    }

    /**
     * Check if this is a custom page.
     */
    public function isCustom(): bool
    {
        return $this->page_type === self::TYPE_CUSTOM;
    }

    /**
     * Check if generation completed successfully.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if generation failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Calculate total tokens used.
     */
    public function getTotalTokens(): int
    {
        return ($this->input_tokens ?? 0) + ($this->output_tokens ?? 0);
    }

    /**
     * Get the clean page name (without custom: prefix).
     */
    public function getCleanPageName(): string
    {
        if (str_starts_with($this->page_name, 'custom:')) {
            return substr($this->page_name, 7);
        }
        return $this->page_name;
    }

    /**
     * Calculate credit accuracy for learning.
     *
     * @return float|null Percentage difference (negative means over-estimated, positive means under-estimated)
     */
    public function getCreditAccuracy(): ?float
    {
        if ($this->estimated_credits === null || $this->actual_credits === null || $this->estimated_credits == 0) {
            return null;
        }

        return (($this->actual_credits - $this->estimated_credits) / $this->estimated_credits) * 100;
    }
}
