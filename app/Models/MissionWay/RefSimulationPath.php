<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefSimulationPath extends Model
{
    protected $table = 'ref_simulation_paths';
    protected $guarded = ['id'];

    protected $casts = [
        'metrics' => 'array',
        'is_ended' => 'boolean',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(RefSimulationVersion::class, 'simulation_version_id');
    }

    public function parentPath(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_path_id');
    }

    public function childPaths(): HasMany
    {
        return $this->hasMany(self::class, 'parent_path_id');
    }

    public function infoCards(): HasMany
    {
        return $this->hasMany(RefInfoCard::class, 'simulation_path_id');
    }
}
