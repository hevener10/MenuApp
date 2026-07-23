<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;

class CurrentTenantResolver
{
    protected ?Tenant $tenant = null;

    public function current(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->getKey();
    }

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function forget(): void
    {
        $this->tenant = null;
    }
}
