<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tag $tag) {
            if (empty($tag->slug)) {
                $name = is_array($tag->name) ? ($tag->name[app()->getLocale()] ?? reset($tag->name)) : $tag->name;
                $tag->slug = Str::slug($name);
            }
        });
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    public function getTranslation(string $field, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $value = $this->$field;
        return is_array($value) ? ($value[$locale] ?? $value[config('app.fallback_locale', 'tr')] ?? '') : ($value ?? '');
    }
}
