<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerationCost extends Model
{
    protected $fillable = [
        'generation_id',
        'page_generation_id',
        'user_id',
        'model_name',
        'provider',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'input_price_per_million',
        'output_price_per_million',
        'input_cost_usd',
        'output_cost_usd',
        'total_cost_usd',
        'credits_charged',
        'profit_margin_percent',
        'processing_time_ms',
        'usd_to_local_rate',
        'total_cost_local',
        'raw_request',
        'raw_response',
    ];

    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'total_tokens' => 'integer',
        'input_price_per_million' => 'decimal:7',
        'output_price_per_million' => 'decimal:7',
        'input_cost_usd' => 'decimal:6',
        'output_cost_usd' => 'decimal:6',
        'total_cost_usd' => 'decimal:6',
        'credits_charged' => 'integer',
        'profit_margin_percent' => 'decimal:2',
        'processing_time_ms' => 'integer',
        'usd_to_local_rate' => 'decimal:4',
        'total_cost_local' => 'decimal:2',
    ];

    // Relationships
    public function generation()
    {
        return $this->belongsTo(Generation::class);
    }

    public function pageGeneration()
    {
        return $this->belongsTo(PageGeneration::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes untuk admin analytics
    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByModel($query, $model)
    {
        return $query->where('model_name', $model);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeProfitable($query)
    {
        return $query->where('profit_margin_percent', '>', 0);
    }

    public function scopeUnprofitable($query)
    {
        return $query->where('profit_margin_percent', '<', 0);
    }
}
