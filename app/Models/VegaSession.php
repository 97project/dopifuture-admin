<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VegaSession extends Model
{
    protected $table = 'vega_sessions';

    protected $fillable = [
        'external_id', 'user_id', 'application_id', 'module',
        'user_name', 'user_surname', 'score', 'duration_minutes',
        'summary', 'started_at', 'ended_at', 'synced_at',
    ];

    protected $casts = [
        'summary'    => 'array',
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'synced_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(VegaSessionMessage::class, 'session_id')->orderBy('order_index');
    }

    public function scopeLecturer($query)
    {
        return $query->where('module', 'lecturer');
    }

    public function scopeSimulator($query)
    {
        return $query->where('module', 'simulator');
    }

    public function scopeChatbot($query)
    {
        return $query->whereNotIn('module', ['lecturer', 'simulator']);
    }

    public function getDurationFormattedAttribute(): ?string
    {
        if (!$this->duration_minutes) return null;
        if ($this->duration_minutes > 60) {
            return floor($this->duration_minutes / 60) . 'sa ' . ($this->duration_minutes % 60) . 'dk';
        }
        return $this->duration_minutes . ' dk';
    }
}
