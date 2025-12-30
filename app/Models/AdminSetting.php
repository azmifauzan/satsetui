<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * AdminSetting Model
 *
 * Stores admin-configurable settings for the platform.
 * Settings are cached for performance.
 */
class AdminSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Cache key prefix
     */
    private const CACHE_PREFIX = 'admin_setting:';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Setting type constants
     */
    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_FLOAT = 'float';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_JSON = 'json';

    /**
     * Setting group constants
     */
    public const GROUP_GENERAL = 'general';
    public const GROUP_BILLING = 'billing';
    public const GROUP_GENERATION = 'generation';

    /**
     * Get a setting value by key.
     *
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Typed value
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return $setting->getTypedValue();
        });
    }

    /**
     * Set a setting value.
     *
     * @param string $key Setting key
     * @param mixed $value Value to store
     * @param string $type Value type
     * @param string|null $description Optional description
     * @param string $group Setting group
     * @param bool $isPublic Whether clients can read this value
     */
    public static function setValue(
        string $key,
        mixed $value,
        string $type = self::TYPE_STRING,
        ?string $description = null,
        string $group = self::GROUP_GENERAL,
        bool $isPublic = false
    ): self {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => self::encodeValue($value, $type),
                'type' => $type,
                'description' => $description,
                'group' => $group,
                'is_public' => $isPublic,
            ]
        );

        // Clear cache
        Cache::forget(self::CACHE_PREFIX . $key);

        return $setting;
    }

    /**
     * Get the typed value for this setting.
     */
    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            self::TYPE_INTEGER => (int) $this->value,
            self::TYPE_FLOAT => (float) $this->value,
            self::TYPE_BOOLEAN => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_JSON => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Encode a value for storage based on type.
     */
    private static function encodeValue(mixed $value, string $type): string
    {
        return match ($type) {
            self::TYPE_JSON => json_encode($value),
            self::TYPE_BOOLEAN => $value ? 'true' : 'false',
            default => (string) $value,
        };
    }

    /**
     * Get all public settings as key-value pairs.
     *
     * Used for sending billing configuration to frontend.
     */
    public static function getPublicSettings(): array
    {
        return Cache::remember('admin_settings:public', self::CACHE_TTL, function () {
            return self::where('is_public', true)
                ->get()
                ->mapWithKeys(fn ($setting) => [$setting->key => $setting->getTypedValue()])
                ->toArray();
        });
    }

    /**
     * Get all settings in a group as key-value pairs.
     */
    public static function getGroupSettings(string $group): array
    {
        return self::where('group', $group)
            ->get()
            ->mapWithKeys(fn ($setting) => [$setting->key => $setting->getTypedValue()])
            ->toArray();
    }

    /**
     * Clear all settings cache.
     */
    public static function clearCache(): void
    {
        // Clear individual setting caches
        self::all()->each(function ($setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
        });

        // Clear public settings cache
        Cache::forget('admin_settings:public');
    }

    // ========================================================================
    // Convenience Methods for Common Settings
    // ========================================================================

    /**
     * Get error margin percentage.
     */
    public static function getErrorMargin(): float
    {
        return self::getValue('error_margin', 0.10);
    }

    /**
     * Get profit margin percentage.
     */
    public static function getProfitMargin(): float
    {
        return self::getValue('profit_margin', 0.05);
    }

    /**
     * Get max base pages included in base cost.
     */
    public static function getMaxBasePages(): int
    {
        return self::getValue('max_base_pages', 5);
    }

    /**
     * Get max base components included in base cost.
     */
    public static function getMaxBaseComponents(): int
    {
        return self::getValue('max_base_components', 6);
    }

    /**
     * Get credits per extra page.
     */
    public static function getCreditsPerExtraPage(): float
    {
        return self::getValue('credits_per_extra_page', 1.0);
    }

    /**
     * Get credits per extra component.
     */
    public static function getCreditsPerExtraComponent(): float
    {
        return self::getValue('credits_per_extra_component', 0.5);
    }

    /**
     * Get all billing settings for frontend.
     */
    public static function getBillingSettings(): array
    {
        return [
            'errorMargin' => self::getErrorMargin(),
            'profitMargin' => self::getProfitMargin(),
            'maxBasePages' => self::getMaxBasePages(),
            'maxBaseComponents' => self::getMaxBaseComponents(),
            'creditsPerExtraPage' => self::getCreditsPerExtraPage(),
            'creditsPerExtraComponent' => self::getCreditsPerExtraComponent(),
        ];
    }
}
