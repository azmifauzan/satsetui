<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerationFailure extends Model
{
    protected $fillable = [
        'generation_id',
        'user_id',
        'page_generation_id',
        'failure_type',
        'error_code',
        'error_message',
        'error_stack_trace',
        'model_used',
        'page_name',
        'page_index',
        'attempt_number',
        'credits_refunded',
        'credits_refunded_at',
        'request_data',
        'response_data',
        'ip_address',
        'user_agent',
        'additional_context',
    ];

    protected $casts = [
        'credits_refunded' => 'integer',
        'page_index' => 'integer',
        'attempt_number' => 'integer',
        'credits_refunded_at' => 'datetime',
        'additional_context' => 'array',
    ];

    // Relationships
    public function generation()
    {
        return $this->belongsTo(Generation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pageGeneration()
    {
        return $this->belongsTo(PageGeneration::class);
    }

    // Scopes untuk admin analytics
    public function scopeByType($query, $type)
    {
        return $query->where('failure_type', $type);
    }

    public function scopeByModel($query, $model)
    {
        return $query->where('model_used', $model);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
