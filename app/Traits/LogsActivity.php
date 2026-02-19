<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            if (auth()->check()) {
                ActivityLog::log(
                    'created',
                    static::getActivityModule(),
                    $model,
                    [],
                    [],
                    $model->getAttributes()
                );
            }
        });

        static::updated(function ($model) {
            if (auth()->check()) {
                $dirty = $model->getDirty();
                $original = array_intersect_key($model->getOriginal(), $dirty);
                if (!empty($dirty)) {
                    ActivityLog::log(
                        'updated',
                        static::getActivityModule(),
                        $model,
                        [],
                        $original,
                        $dirty
                    );
                }
            }
        });

        static::deleted(function ($model) {
            if (auth()->check()) {
                ActivityLog::log(
                    'deleted',
                    static::getActivityModule(),
                    $model,
                    ['name' => $model->name ?? $model->title ?? $model->id]
                );
            }
        });
    }

    protected static function getActivityModule(): string
    {
        $class = class_basename(static::class);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class));
    }
}
