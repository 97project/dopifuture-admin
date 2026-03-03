<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'name',
        'country',
        'state',
        'city',
        'address',
        'phone',
        'email',
        'website',
        'logo',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /* ─── Scopes ─────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /* ─── Relationships ──────────────────────────── */

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'school_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'school-admin');
    }

    public function principals(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'principal');
    }

    public function teachers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'teacher');
    }

    public function students(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'student');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
