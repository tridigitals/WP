<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /**
     * Cache key for all settings
     */
    const CACHE_KEY = 'cms_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
        'is_autoload',
        'is_public',
        'validation_rules'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_autoload' => 'boolean',
        'is_public' => 'boolean',
        'validation_rules' => 'json'
    ];

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::saved(function ($setting) {
            static::clearCache();
        });

        static::deleted(function ($setting) {
            static::clearCache();
        });
    }

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $settings = static::getAll();
        $setting = $settings->where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value, string $group = 'general')
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->fill([
            'group' => $group,
            'value' => $value,
            'type' => static::determineType($value)
        ]);
        $setting->save();

        return $setting;
    }

    /**
     * Get all settings.
     */
    public static function getAll()
    {
        return Cache::remember(static::CACHE_KEY, now()->addDay(), function () {
            return static::all();
        });
    }

    /**
     * Get settings by group.
     */
    public static function getGroup(string $group)
    {
        return static::getAll()->where('group', $group);
    }

    /**
     * Get only autoloaded settings.
     */
    public static function getAutoloaded()
    {
        return static::getAll()->where('is_autoload', true);
    }

    /**
     * Clear the settings cache.
     */
    public static function clearCache()
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * Cast a value to its proper type.
     */
    protected static function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return is_array($value) ? $value : json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Determine the type of a value.
     */
    protected static function determineType($value): string
    {
        switch (true) {
            case is_bool($value):
                return 'boolean';
            case is_int($value):
                return 'integer';
            case is_float($value):
                return 'float';
            case is_array($value):
                return 'array';
            case static::isJson($value):
                return 'json';
            default:
                return 'string';
        }
    }

    /**
     * Check if a string is valid JSON.
     */
    protected static function isJson($value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
