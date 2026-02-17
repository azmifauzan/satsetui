<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PreviewSession Model
 *
 * Represents a live preview session for a generated template.
 * Tracks workspace path, dev server port, and session lifecycle.
 *
 * Lifecycle:
 * creating → installing → running → stopped
 *                                → error
 */
class PreviewSession extends Model
{
    protected $fillable = [
        'generation_id',
        'user_id',
        'workspace_path',
        'preview_port',
        'preview_type',
        'status',
        'started_at',
        'last_activity_at',
        'stopped_at',
        'error_message',
    ];

    protected $casts = [
        'preview_port' => 'integer',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'stopped_at' => 'datetime',
    ];

    /**
     * Preview type constants
     */
    public const TYPE_SERVER = 'server';

    public const TYPE_STATIC = 'static';

    /**
     * Status constants
     */
    public const STATUS_CREATING = 'creating';

    public const STATUS_INSTALLING = 'installing';

    public const STATUS_RUNNING = 'running';

    public const STATUS_STOPPED = 'stopped';

    public const STATUS_ERROR = 'error';

    /**
     * Get the generation being previewed.
     */
    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class);
    }

    /**
     * Get the user who started this preview.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the session is currently running.
     */
    public function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    /**
     * Check if the session has an error.
     */
    public function hasError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    /**
     * Check if the session is setting up (creating or installing).
     */
    public function isSettingUp(): bool
    {
        return in_array($this->status, [self::STATUS_CREATING, self::STATUS_INSTALLING]);
    }

    /**
     * Check if the session has timed out (30 minutes of inactivity).
     */
    public function hasTimedOut(int $timeoutMinutes = 30): bool
    {
        if (! $this->last_activity_at) {
            return false;
        }

        return $this->last_activity_at->diffInMinutes(now()) >= $timeoutMinutes;
    }

    /**
     * Update the last activity timestamp and parent timestamps.
     */
    public function touch($attribute = null): bool
    {
        $this->last_activity_at = now();

        return parent::touch($attribute);
    }
}
