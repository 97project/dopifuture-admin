<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\LogsActivity;

class Post extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
        'featured_image',
        'status',
        'published_at',
        'author_id',
        'is_featured',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'content' => 'array',
            'excerpt' => 'array',
            'meta_title' => 'array',
            'meta_description' => 'array',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'view_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (empty($post->slug)) {
                $title = is_array($post->title) ? ($post->title[app()->getLocale()] ?? reset($post->title)) : $post->title;
                $post->slug = Str::slug($title);
            }
            $post->slug = static::ensureUniqueSlug($post->slug, $post->id);
        });
    }

    protected static function ensureUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;
        $query = static::withTrashed()->where('slug', $slug);
        if ($excludeId)
            $query->where('id', '!=', $excludeId);
        while ($query->exists()) {
            $slug = "{$original}-{$count}";
            $count++;
            $query = static::withTrashed()->where('slug', $slug);
            if ($excludeId)
                $query->where('id', '!=', $excludeId);
        }
        return $slug;
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search)
            return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
        });
    }
}
