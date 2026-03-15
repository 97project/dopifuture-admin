<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MwPlayer extends Model
{
    protected $table = 'mw_players';

    protected $fillable = [
        'external_id', 'user_id', 'application_id', 'name', 'surname',
        'email', 'username', 'profile_data', 'synced_at',
    ];

    protected $casts = [
        'profile_data' => 'array',
        'synced_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function sessionPlayers(): HasMany
    {
        return $this->hasMany(MwSessionPlayer::class, 'player_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->surname}");
    }
}
