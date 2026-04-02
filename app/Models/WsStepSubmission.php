<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WsStepSubmission extends Model
{
    protected $table = 'ws_step_submissions';

    protected $fillable = [
        'external_id', 'member_id', 'step_external_id',
        'file_name', 'file_url', 'file_type', 'file_size',
        'link_url', 'link_title', 'link_platform',
        'status', 'feedback', 'points_earned',
        'submitted_at', 'synced_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'synced_at'    => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(WsMember::class, 'member_id');
    }
}
