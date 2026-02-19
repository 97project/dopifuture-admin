<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'linkable_type',
        'linkable_id',
        'target',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function linkable()
    {
        return $this->morphTo();
    }

    public function getResolvedUrlAttribute(): string
    {
        if ($this->url) {
            return $this->url;
        }
        if ($this->linkable) {
            return url($this->linkable->slug ?? '/');
        }
        return '#';
    }

    public function getTranslation(string $field, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $value = $this->$field;
        return is_array($value) ? ($value[$locale] ?? $value[config('app.fallback_locale', 'tr')] ?? '') : ($value ?? '');
    }
}
