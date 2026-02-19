<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\LogsActivity;

class Page extends Model
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
        'template',
        'status',
        'published_at',
        'author_id',
        'sort_order',
        'is_homepage',
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
            'is_homepage' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Page $page) {
            if (empty($page->slug)) {
                $title = is_array($page->title) ? ($page->title[app()->getLocale()] ?? reset($page->title)) : $page->title;
                $page->slug = Str::slug($title);
            }
            $page->slug = static::ensureUniqueSlug($page->slug, $page->id);
        });

        static::updating(function (Page $page) {
            if ($page->isDirty('slug')) {
                $page->slug = static::ensureUniqueSlug($page->slug, $page->id);
            }
        });
    }

    protected static function ensureUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;
        $query = static::withTrashed()->where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        while ($query->exists()) {
            $slug = "{$original}-{$count}";
            $count++;
            $query = static::withTrashed()->where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }
        return $slug;
    }

    // ── Relationships ───────────────────────────────────────

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // ── Accessors ───────────────────────────────────────────

    public function getTranslatedTitleAttribute(): string
    {
        return $this->getTranslation('title');
    }

    public function getTranslatedContentAttribute(): string
    {
        return $this->getTranslation('content');
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

    // ── Scopes ──────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
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

    public function scopeByTemplate($query, string $template)
    {
        return $query->where('template', $template);
    }
}
