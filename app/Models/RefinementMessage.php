<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefinementMessage extends Model
{
    protected $fillable = [
        'generation_id',
        'role',
        'content',
        'type',
        'page_name',
    ];

    /**
     * Get the generation this message belongs to
     */
    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class);
    }
}
