<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\LogsActivity;

class Category extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
        'type',
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

    protected static function booted(): void
    {
        static::creating(function (Category $cat) {
            if (empty($cat->slug)) {
                $name = is_array($cat->name) ? ($cat->name[app()->getLocale()] ?? reset($cat->name)) : $cat->name;
                $cat->slug = Str::slug($name);
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class);
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

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
