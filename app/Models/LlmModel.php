<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlmModel extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'input_price_per_million',
        'output_price_per_million',
        'estimated_credits_per_generation',
        'context_length',
        'is_free',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'input_price_per_million' => 'decimal:7',
        'output_price_per_million' => 'decimal:7',
        'estimated_credits_per_generation' => 'integer',
        'context_length' => 'integer',
        'is_free' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get active models only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get models ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get free models
     */
    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    /**
     * Get premium models
     */
    public function scopePremium($query)
    {
        return $query->where('is_free', false);
    }
}
