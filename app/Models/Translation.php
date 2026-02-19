<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'group',
        'key',
        'locale',
        'value',
    ];

    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->where('key', 'like', "%{$search}%")
                ->orWhere('value', 'like', "%{$search}%");
        });
    }

    public function scopeMissing($query, string $locale)
    {
        return $query->where('locale', $locale)
            ->whereNull('value')
            ->orWhere(function ($q) use ($locale) {
                $q->where('locale', $locale)->where('value', '');
            });
    }
}
