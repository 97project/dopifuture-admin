<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MwAssignmentPlayer extends Model
{
    protected $table = 'mw_assignment_players';
    protected $guarded = ['id'];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MwAssignment::class, 'assignment_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(MwPlayer::class, 'player_id');
    }
}
