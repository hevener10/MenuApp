<?php

namespace App\Models\Concerns;

use App\Support\Audit\AuditLogger;

trait HasAuditTrail
{
    public static function bootHasAuditTrail(): void
    {
        static::created(function ($model): void {
            app(AuditLogger::class)->log('created', $model, [], $model->attributesToArray());
        });

        static::updated(function ($model): void {
            $changes = $model->getChanges();

            if ($changes === []) {
                return;
            }

            $oldValues = [];

            foreach (array_keys($changes) as $attribute) {
                $oldValues[$attribute] = $model->getOriginal($attribute);
            }

            app(AuditLogger::class)->log('updated', $model, $oldValues, $changes);
        });

        static::deleted(function ($model): void {
            app(AuditLogger::class)->log('deleted', $model, $model->attributesToArray(), []);
        });
    }
}
