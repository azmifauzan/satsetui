<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\OpenAICompatibleService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'credits',
        'is_premium',
        'preferred_model',
        'language',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_premium' => 'boolean',
            'credits' => 'integer',
        ];
    }

    /**
     * Check if user can use premium models
     */
    public function hasPremiumAccess(): bool
    {
        return $this->credits > 0;
    }

    /**
     * Get available models for this user
     */
    public function getAvailableModels(): array
    {
        return app(OpenAICompatibleService::class)
            ->getAvailableModels($this->hasPremiumAccess());
    }

    /**
     * Projects relationship
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Generations relationship
     */
    public function generations()
    {
        return $this->hasMany(Generation::class);
    }
}
