<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'subject_type',
        'subject_id',
        'action',
        'module',
        'properties',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor()
    {
        return $this->morphTo();
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function scopeForActor($query, $actorType, $actorId)
    {
        return $query->where('actor_type', $actorType)->where('actor_id', $actorId);
    }

    public function scopeForSubject($query, $subjectType, $subjectId)
    {
        return $query->where('subject_type', $subjectType)->where('subject_id', $subjectId);
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->where('action', 'like', "%{$search}%")
                ->orWhere('module', 'like', "%{$search}%");
        });
    }

    public static function log(
        string $action,
        string $module,
        $subject = null,
        array $properties = [],
        array $oldValues = [],
        array $newValues = []
    ): self {
        $actor = auth()->user();

        return static::create([
            'actor_type' => $actor ? get_class($actor) : null,
            'actor_id' => $actor?->id,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id ?? null,
            'action' => $action,
            'module' => $module,
            'properties' => $properties ?: null,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
