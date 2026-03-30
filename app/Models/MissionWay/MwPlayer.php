<?php

namespace App\Models\MissionWay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MwPlayer extends Model
{
    protected $table = 'mw_players';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(MwPlayerProfile::class, 'player_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(MwSessionPlayer::class, 'player_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(MwPlayerProgress::class, 'player_id');
    }

    public function choices(): HasMany
    {
        return $this->hasMany(MwPlayerChoice::class, 'player_id');
    }
}
