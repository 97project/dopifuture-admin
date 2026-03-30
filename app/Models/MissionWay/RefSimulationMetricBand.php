<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;

class RefSimulationMetricBand extends Model
{
    protected $table = 'ref_simulation_metric_bands';
    protected $guarded = ['id'];

    public function version()
    {
        return $this->belongsTo(RefSimulationVersion::class, 'simulation_version_id');
    }

    public function metricDefinition()
    {
        return $this->belongsTo(RefMetricDefinition::class, 'metric_id');
    }

    public function category()
    {
        return $this->belongsTo(RefMetricBandCategory::class, 'category_id');
    }
}
