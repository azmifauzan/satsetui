<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * LLM Model
 * 
 * Represents 3 fixed model types (fast, professional, expert)
 * Each can be configured with different providers (Gemini or OpenAI)
 */
class LlmModel extends Model
{
    protected $fillable = [
        'model_type',
        'provider',
        'model_name',
        'api_key',
        'base_url',
        'base_credits',
        'is_active',
    ];

    protected $casts = [
        'base_credits' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'api_key',
        'base_url',
    ];

    /**
     * Get the model's encrypted API key
     */
    protected function apiKey(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    /**
     * Get the model's encrypted base URL
     */
    protected function baseUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    /**
     * Get active models only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get model by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('model_type', $type);
    }

    /**
     * Get all models ordered by type (fast -> professional -> expert)
     */
    public function scopeOrdered($query)
    {
        return $query->orderByRaw("
            CASE model_type
                WHEN 'fast' THEN 1
                WHEN 'professional' THEN 2
                WHEN 'expert' THEN 3
            END
        ");
    }

    /**
     * Get display name based on model type
     */
    public function getDisplayNameAttribute(): string
    {
        return match($this->model_type) {
            'fast' => 'Cepat',
            'professional' => 'Profesional',
            'expert' => 'Expert',
            default => ucfirst($this->model_type),
        };
    }

    /**
     * Get description based on model type
     */
    public function getDescriptionAttribute(): string
    {
        return match($this->model_type) {
            'fast' => 'Generasi cepat dengan hasil berkualitas baik untuk template sederhana',
            'professional' => 'Hasil berkualitas tinggi dengan keseimbangan kecepatan dan detail',
            'expert' => 'Kualitas terbaik untuk template kompleks dengan fitur lengkap',
            default => '',
        };
    }
}

