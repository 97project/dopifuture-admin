<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class NotificationTemplate extends Model
{
    use LogsActivity;

    protected $fillable = [
        'key',
        'title',
        'body',
        'channels',
        'is_active',
    ];

    protected $attributes = [
        'channels' => '["database"]',
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'body' => 'array',
            'channels' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getTranslation(string $field, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $value = $this->$field;
        if (is_array($value)) {
            return $value[$locale] ?? $value[config('app.fallback_locale', 'tr')] ?? '';
        }
        return $value ?? '';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function findByKey(string $key): ?self
    {
        return static::active()->where('key', $key)->first();
    }
}
