<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FaqCategory extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FaqCategory $cat) {
            if (empty($cat->slug)) {
                $name = is_array($cat->name) ? ($cat->name[app()->getLocale()] ?? reset($cat->name)) : $cat->name;
                $cat->slug = Str::slug($name);
            }
        });
    }

    public function faqs()
    {
        return $this->hasMany(Faq::class)->orderBy('sort_order');
    }

    public function getTranslation(string $field, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $value = $this->$field;
        return is_array($value) ? ($value[$locale] ?? $value[config('app.fallback_locale', 'tr')] ?? '') : ($value ?? '');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
