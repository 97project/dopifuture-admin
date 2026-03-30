<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MwAssignment extends Model
{
    protected $table = 'mw_assignments';
    protected $guarded = ['id'];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(RefSimulation::class, 'simulation_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(MwSimulationSession::class, 'simulation_session_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(MwAssignmentPlayer::class, 'assignment_id');
    }
}
