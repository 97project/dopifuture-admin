<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WsStepProgress extends Model
{
    protected $table = 'ws_step_progress';

    protected $fillable = [
        'member_id', 'assignment_id', 'step_external_id',
        'status', 'started_at', 'completed_at',
        'earned_point', 'earned_coin', 'synced_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'synced_at'    => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(WsMember::class, 'member_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(WsStep::class, 'step_external_id', 'external_id');
    }
}
