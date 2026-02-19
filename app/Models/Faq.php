<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'faq_category_id',
        'question',
        'answer',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'question' => 'array',
            'answer' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
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
