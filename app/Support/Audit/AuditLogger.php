<?php

namespace App\Support\Audit;

use App\Models\AuditLog;
use App\Support\Tenancy\CurrentTenantResolver;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public function __construct(
        protected CurrentTenantResolver $tenantResolver,
    ) {
    }

    public function log(string $event, Model $model, array $oldValues = [], array $newValues = []): void
    {
        if ($model instanceof AuditLog) {
            return;
        }

        AuditLog::query()->create([
            'tenant_id' => $model->getAttribute('tenant_id') ?? $this->tenantResolver->id(),
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
        ]);
    }
}
