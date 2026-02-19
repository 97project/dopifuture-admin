<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    protected $fillable = [
        'school_id',
        'user_id',
        'seat_count',
        'used_seats',
        'starts_at',
        'expires_at',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'seat_count' => 'integer',
            'used_seats' => 'integer',
            'starts_at' => 'date',
            'expires_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /* ─── Business logic ────────────────────────── */

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Total seats = base seat_count + purchases sum.
     */
    public function totalSeats(): int
    {
        return $this->seat_count + (int) $this->purchases()->sum('seat_count');
    }

    public function availableSeats(): int
    {
        return max(0, $this->totalSeats() - $this->used_seats);
    }

    public function hasAvailableSeats(): bool
    {
        return $this->availableSeats() > 0;
    }

    public function remainingSeats(): int
    {
        return $this->availableSeats();
    }

    /* ─── Relationships ─────────────────────────── */

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(LicensePurchase::class);
    }

    /* ─── Scopes ────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
        });
    }
}
