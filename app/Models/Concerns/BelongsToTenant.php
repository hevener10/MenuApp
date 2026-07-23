<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\Tenancy\CurrentTenantResolver;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model): void {
            if ($model->getAttribute('tenant_id')) {
                return;
            }

            $tenantId = app(CurrentTenantResolver::class)->id();

            if ($tenantId !== null) {
                $model->setAttribute('tenant_id', $tenantId);
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenantId = app(CurrentTenantResolver::class)->id();

            if ($tenantId !== null) {
                $builder->where($builder->qualifyColumn('tenant_id'), $tenantId);
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant(Builder $query, Tenant|int $tenant): Builder
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->getKey() : $tenant;

        return $query->withoutGlobalScope('tenant')->where($query->qualifyColumn('tenant_id'), $tenantId);
    }
}
