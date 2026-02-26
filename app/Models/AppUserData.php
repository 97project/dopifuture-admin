<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppUserData extends Model
{
    protected $table = 'app_user_data';

    protected $fillable = [
        'user_id',
        'application_id',
        'connector_type',
        'external_user_id',
        'external_data',
        'synced_at',
    ];

    protected $casts = [
        'external_data' => 'array',
        'synced_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
