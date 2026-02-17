<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Generation Model
 *
 * Represents a complete template generation request.
 * Each generation can have multiple page generations (per-page LLM calls).
 */
class Generation extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'model_used',
        'output_format',
        'framework',
        'category',
        'credits_used',
        'base_credits',
        'extra_page_credits',
        'extra_component_credits',
        'subtotal_credits',
        'error_margin',
        'profit_margin',
        'error_margin_amount',
        'profit_margin_amount',
        'status',
        'mcp_prompt',
        'blueprint_json',
        'generated_content',
        'progress_data',
        'current_page_index',
        'total_pages',
        'current_status',
        'error_message',
        'processing_time',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'credits_used' => 'integer',
        'base_credits' => 'decimal:4',
        'extra_page_credits' => 'decimal:4',
        'extra_component_credits' => 'decimal:4',
        'subtotal_credits' => 'decimal:4',
        'error_margin' => 'decimal:4',
        'profit_margin' => 'decimal:4',
        'error_margin_amount' => 'decimal:4',
        'profit_margin_amount' => 'decimal:4',
        'processing_time' => 'integer',
        'blueprint_json' => 'array',
        'progress_data' => 'array',
        'current_page_index' => 'integer',
        'total_pages' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user who owns this generation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project this generation belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the per-page generation records.
     */
    public function pageGenerations(): HasMany
    {
        return $this->hasMany(PageGeneration::class)->orderBy('page_index');
    }

    /**
     * Get the credit estimation records for learning.
     */
    public function creditEstimations(): HasMany
    {
        return $this->hasMany(CreditEstimation::class);
    }

    /**
     * Get all failures for this generation.
     */
    public function failures(): HasMany
    {
        return $this->hasMany(GenerationFailure::class);
    }

    /**
     * Get all credit transactions for this generation.
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    /**
     * Get all cost records for this generation.
     */
    public function costs(): HasMany
    {
        return $this->hasMany(GenerationCost::class);
    }

    /**
     * Get all generated files for this generation.
     */
    public function generationFiles(): HasMany
    {
        return $this->hasMany(GenerationFile::class);
    }

    /**
     * Get scaffold files only.
     */
    public function scaffoldFiles(): HasMany
    {
        return $this->hasMany(GenerationFile::class)->where('is_scaffold', true);
    }

    /**
     * Get page component files only.
     */
    public function componentFiles(): HasMany
    {
        return $this->hasMany(GenerationFile::class)->where('is_scaffold', false);
    }

    /**
     * Get all preview sessions for this generation.
     */
    public function previewSessions(): HasMany
    {
        return $this->hasMany(PreviewSession::class);
    }

    /**
     * Get the active preview session (if any).
     */
    public function activePreviewSession(): HasMany
    {
        return $this->hasMany(PreviewSession::class)->where('status', 'running');
    }

    /**
     * Get all refinement/chat messages for this generation.
     */
    public function refinementMessages(): HasMany
    {
        return $this->hasMany(RefinementMessage::class)->orderBy('created_at');
    }

    /**
     * Calculate the credit breakdown from current state.
     *
     * @return array{base: float, extraPages: float, extraComponents: float, subtotal: float, errorMargin: float, profitMargin: float, total: int}
     */
    public function getCreditBreakdown(): array
    {
        return [
            'base' => (float) $this->base_credits,
            'extraPages' => (float) $this->extra_page_credits,
            'extraComponents' => (float) $this->extra_component_credits,
            'subtotal' => (float) $this->subtotal_credits,
            'errorMargin' => (float) $this->error_margin,
            'profitMargin' => (float) $this->profit_margin,
            'errorMarginAmount' => (float) $this->error_margin_amount,
            'profitMarginAmount' => (float) $this->profit_margin_amount,
            'total' => (int) $this->credits_used,
        ];
    }

    /**
     * Check if all page generations are complete.
     */
    public function isComplete(): bool
    {
        return $this->pageGenerations()
            ->where('status', '!=', 'completed')
            ->doesntExist();
    }

    /**
     * Get generation progress as percentage.
     */
    public function getProgressPercentage(): int
    {
        if ($this->total_pages === 0) {
            return 0;
        }

        $completed = $this->pageGenerations()
            ->where('status', 'completed')
            ->count();

        return (int) round(($completed / $this->total_pages) * 100);
    }
}
