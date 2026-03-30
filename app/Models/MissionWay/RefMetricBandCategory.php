<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;

class RefMetricBandCategory extends Model
{
    protected $table = 'ref_metric_band_categories';
    protected $guarded = ['id'];

    public function metricBands()
    {
        return $this->hasMany(RefSimulationMetricBand::class, 'category_id');
    }
}
