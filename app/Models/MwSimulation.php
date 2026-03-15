<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MwSimulation extends Model
{
    protected $table = 'mw_simulations';

    protected $fillable = [
        'external_id', 'application_id', 'name', 'difficulty_level', 'status',
        'description', 'cover_image', 'min_players', 'max_players',
        'estimated_duration', 'metadata', 'synced_at',
    ];

    protected $casts = [
        'metadata'  => 'array',
        'synced_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(MwSession::class, 'simulation_id');
    }

    /** Tüm session path'leri — unique version id'ler üzerinden */
    public function paths(): HasMany
    {
        return $this->hasMany(MwSimulationPath::class, 'simulation_version_id', 'external_id');
    }
}
