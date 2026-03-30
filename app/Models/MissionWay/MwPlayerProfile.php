<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MwPlayerProfile extends Model
{
    protected $table = 'mw_player_profiles';
    protected $guarded = ['id'];

    protected $casts = [
        'achievements' => 'array',
        'statistics' => 'array',
        'metric_stats' => 'array',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(MwPlayer::class, 'player_id');
    }
}
