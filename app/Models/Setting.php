<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'is_encrypted',
        'is_translatable',
        'options',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
            'is_translatable' => 'boolean',
            'options' => 'array',
        ];
    }

    public function getTypedValueAttribute()
    {
        $value = $this->is_encrypted ? decrypt($this->value) : $this->value;

        if ($this->is_translatable && $value) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $locale = app()->getLocale();
                return $decoded[$locale] ?? $decoded['tr'] ?? $value;
            }
        }

        return match ($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    public static function getValue(string $group, string $key, $default = null)
    {
        $cacheKey = "settings.{$group}.{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($group, $key, $default) {
            $setting = static::where('group', $group)->where('key', $key)->first();
            return $setting ? $setting->typed_value : $default;
        });
    }

    public static function setValue(string $group, string $key, $value): void
    {
        $setting = static::where('group', $group)->where('key', $key)->first();
        if ($setting) {
            if ($setting->is_encrypted) {
                $value = encrypt($value);
            }
            $setting->update(['value' => is_array($value) ? json_encode($value) : $value]);
        }
        Cache::forget("settings.{$group}.{$key}");
    }

    public static function clearCache(): void
    {
        // Clear only settings cache keys — NOT the entire cache store
        $groups = self::distinct()->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("settings.{$group}");
        }
        // Also clear the full settings blob if it's cached
        Cache::forget('settings.all');
    }

    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
