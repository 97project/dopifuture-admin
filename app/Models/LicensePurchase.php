<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicensePurchase extends Model
{
    protected $fillable = [
        'license_id',
        'seat_count',
        'amount',
        'purchased_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'seat_count' => 'integer',
            'amount' => 'decimal:2',
            'purchased_at' => 'date',
        ];
    }

    /* ─── Relationships ─────────────────────────── */

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
