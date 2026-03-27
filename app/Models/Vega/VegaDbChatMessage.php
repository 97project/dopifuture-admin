<?php

declare(strict_types=1);

namespace App\Models\Vega;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vega remote DB — vega_chat_messages tablosu.
 * Study Space sohbet mesajları.
 */
class VegaDbChatMessage extends Model
{
    protected $connection = 'vega_db';
    protected $table = 'vega_chat_messages';

    protected $casts = [
        'metadata' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(VegaDbSession::class, 'session_id');
    }
}
