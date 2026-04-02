<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WsAssignment extends Model
{
    protected $table = 'ws_assignments';

    protected $fillable = [
        'external_id', 'simulation_id', 'name', 'description',
        'due_date', 'status', 'synced_at',
    ];

    protected $casts = [
        'due_date'  => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(WsSimulation::class, 'simulation_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(WsAssignmentMember::class, 'assignment_id');
    }
}
