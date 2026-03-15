<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MwSimulationPath extends Model
{
    protected $table = 'mw_simulation_paths';

    protected $fillable = [
        'external_id', 'simulation_version_id', 'parent_path_id',
        'path_type', 'order_index', 'points', 'metrics', 'translations',
        'is_ended', 'synced_at',
    ];

    protected $casts = [
        'metrics'      => 'array',
        'translations' => 'array',
        'is_ended'     => 'boolean',
        'synced_at'    => 'datetime',
    ];

    public function children()
    {
        return self::where('simulation_version_id', $this->simulation_version_id)
            ->where('parent_path_id', $this->external_id);
    }

    public function scopeDecisions($query)
    {
        return $query->whereIn('path_type', ['decision', 'question']);
    }
}
