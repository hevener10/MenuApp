<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\CurrentTenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPermission
{
    public function __construct(
        protected CurrentTenantResolver $tenantResolver,
    ) {
    }

    public function handle(Request $request, Closure $next, string $module, string $action): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->is_active) {
            abort(403, 'Inactive users cannot access the tenant.');
        }

        $tenant = $this->tenantResolver->current();

        if ($tenant && ! $user->isSuperadmin() && $user->tenant_id !== $tenant->getKey()) {
            abort(403, 'The authenticated user does not belong to the resolved tenant.');
        }

        if (! $user->hasPermission($module, $action)) {
            abort(403, 'The authenticated user is not allowed to access this resource.');
        }

        return $next($request);
    }
}
