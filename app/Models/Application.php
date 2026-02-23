<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Application extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'color',
        'connector_class',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /* ─── Translatable helpers ────────────────────────── */

    public function getTranslation(string $field, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $value = $this->{$field};

        if (is_array($value)) {
            return $value[$locale] ?? $value[config('app.fallback_locale', 'tr')] ?? '';
        }

        return (string) $value;
    }

    public function getNameAttribute($value): string
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (is_array($value)) {
            $locale = app()->getLocale();
            return $value[$locale] ?? $value[config('app.fallback_locale', 'tr')] ?? '';
        }
        return (string) $value;
    }

    /* ─── Scopes ──────────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /* ─── Relationships ──────────────────────────────── */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'application_user')
            ->withPivot('granted_by', 'granted_at', 'synced_at', 'sync_status', 'sync_error')
            ->withTimestamps();
    }

    /* ─── Connector ──────────────────────────────────── */

    /**
     * connector_class alanından connector instance oluşturur.
     */
    public function resolveConnector(): ?\App\Connectors\AppConnectorInterface
    {
        if (!$this->connector_class || !class_exists($this->connector_class)) {
            return null;
        }

        return app($this->connector_class);
    }
}
