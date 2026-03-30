<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;

class RefLanguage extends Model
{
    protected $table = 'ref_languages';
    protected $guarded = ['id'];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
