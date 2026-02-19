<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class ApiKey extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'name',
        'key_hash',
        'key_prefix',
        'abilities',
        'ip_restrictions',
        'expires_at',
        'last_used_at',
        'last_used_ip',
        'is_active',
    ];

    protected $hidden = [
        'key_hash',
    ];

    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'ip_restrictions' => 'array',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    public function hasAbility(string $ability): bool
    {
        $abilities = $this->abilities ?? ['*'];
        return in_array('*', $abilities) || in_array($ability, $abilities);
    }

    public function isIpAllowed(string $ip): bool
    {
        $restrictions = $this->ip_restrictions ?? [];
        if (empty($restrictions)) {
            return true;
        }
        return in_array($ip, $restrictions);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}
