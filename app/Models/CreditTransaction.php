<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'generation_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'description',
        'metadata',
        'admin_user_id',
        'admin_notes',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'metadata' => 'array',
    ];

    // Transaction types
    const TYPE_CHARGE = 'charge';       // Deduction for generation
    const TYPE_REFUND = 'refund';       // Return credits on failure
    const TYPE_TOPUP = 'topup';         // User purchases credits
    const TYPE_BONUS = 'bonus';         // Free credits (promo, etc)
    const TYPE_ADJUSTMENT = 'adjustment'; // Manual admin adjustment

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generation()
    {
        return $this->belongsTo(Generation::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeCharges($query)
    {
        return $query->where('type', self::TYPE_CHARGE);
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }

    public function scopeTopups($query)
    {
        return $query->where('type', self::TYPE_TOPUP);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
