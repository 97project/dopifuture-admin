<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WsAssignmentMember extends Model
{
    protected $table = 'ws_assignment_members';

    protected $fillable = [
        'assignment_id', 'member_id',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(WsAssignment::class, 'assignment_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(WsMember::class, 'member_id');
    }
}
