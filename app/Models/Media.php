<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;

class Media extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'path',
        'disk',
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
        'folder',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'alt_text' => 'array',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function scopeInFolder($query, string $folder)
    {
        return $query->where('folder', $folder);
    }

    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search)
            return $query;
        return $query->where('name', 'like', "%{$search}%");
    }
}
