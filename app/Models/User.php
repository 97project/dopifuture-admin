<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, LogsActivity;

    protected $fillable = [
        'id',
        'name',
        'surname',
        'email',
        'phone',
        'locale',
        'timezone',
        'status',
        'avatar_path',
        'avatar_disk',
        'dark_mode',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'failed_login_count',
        'locked_until',
        'last_login_at',
        'last_login_ip',
        'device_token',
        'device_platform',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'dark_mode' => 'boolean',
            'two_factor_recovery_codes' => 'encrypted:array',
            'failed_login_count' => 'integer',
        ];
    }

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function activityLogsAsActor()
    {
        return $this->morphMany(ActivityLog::class, 'actor');
    }

    public function activityLogsAsSubject()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    /* ─── DopiFuture Relationships ──────────────── */

    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_user')->withPivot('role')->withTimestamps();
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_user', 'user_id', 'class_id')->withPivot('role')->withTimestamps();
    }

    public function applications()
    {
        return $this->belongsToMany(Application::class, 'application_user')
            ->withPivot('granted_by', 'granted_at', 'synced_at', 'sync_status', 'sync_error')
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->surname}");
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar_path) {
            return null;
        }
        $disk = \Illuminate\Support\Facades\Storage::disk($this->avatar_disk);
        return $disk->url($this->avatar_path);
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_confirmed_at);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('surname', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}
