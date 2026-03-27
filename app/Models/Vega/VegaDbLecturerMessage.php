<?php

declare(strict_types=1);

namespace App\Models\Vega;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vega remote DB — vega_lecturer_messages tablosu.
 * Way AI Coach oturumlarının mesaj geçmişi.
 */
class VegaDbLecturerMessage extends Model
{
    protected $connection = 'vega_db';
    protected $table = 'vega_lecturer_messages';

    public $timestamps = false;

    protected $casts = [
        'input_tokens'   => 'integer',
        'output_tokens'  => 'integer',
        'total_tokens'   => 'integer',
        'score'          => 'integer',
        'created_at_ext' => 'datetime',
        'created_at'     => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(VegaDbSession::class, 'session_id');
    }

    public function scopeUserMessages($query)
    {
        return $query->where('role', 'user');
    }

    public function scopeAssistantMessages($query)
    {
        return $query->where('role', 'assistant');
    }

    public function getIsUserAttribute(): bool
    {
        return $this->role === 'user';
    }

    public function getIsAssistantAttribute(): bool
    {
        return $this->role === 'assistant';
    }
}
