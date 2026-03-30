<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefSimulationVersion extends Model
{
    protected $table = 'ref_simulation_versions';
    protected $guarded = ['id'];

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(RefSimulation::class, 'simulation_id');
    }

    public function paths(): HasMany
    {
        return $this->hasMany(RefSimulationPath::class, 'simulation_version_id');
    }

    public function metricBands(): HasMany
    {
        return $this->hasMany(RefSimulationMetricBand::class, 'simulation_version_id');
    }
}
