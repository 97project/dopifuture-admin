<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'native_name',
        'is_active',
        'is_default',
        'fallback_code',
        'direction',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    public static function getActiveCodes(): array
    {
        return static::active()->pluck('code')->toArray();
    }
}
