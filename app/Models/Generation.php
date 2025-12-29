<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Generation extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'model_used',
        'credits_used',
        'status',
        'mcp_prompt',
        'generated_content',
        'error_message',
        'processing_time',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'credits_used' => 'integer',
        'processing_time' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
