<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;

class RefMediaAsset extends Model
{
    protected $table = 'ref_media_assets';
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];
}
