<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenancy\CurrentTenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentTenant
{
    public function __construct(
        protected CurrentTenantResolver $tenantResolver,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if (! $tenant) {
            abort(400, 'Tenant context is required.');
        }

        $this->tenantResolver->set($tenant);

        try {
            return $next($request);
        } finally {
            $this->tenantResolver->forget();
        }
    }

    protected function resolveTenant(Request $request): ?Tenant
    {
        $user = $request->user();
        $tenantHeader = $request->header('X-Tenant-Id');

        if ($user && $user->tenant_id) {
            if ($tenantHeader && (int) $tenantHeader !== $user->tenant_id) {
                abort(403, 'The authenticated user cannot switch tenant context.');
            }

            return $user->tenant;
        }

        if (! $tenantHeader) {
            return null;
        }

        $tenant = Tenant::query()->find($tenantHeader);

        if (! $tenant) {
            abort(404, 'Tenant not found.');
        }

        return $tenant;
    }
}
