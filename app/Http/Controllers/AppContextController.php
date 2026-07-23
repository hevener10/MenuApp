<?php

namespace App\Http\Controllers;

use App\Support\Tenancy\CurrentTenantResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppContextController extends Controller
{
    public function __invoke(Request $request, CurrentTenantResolver $tenantResolver): JsonResponse
    {
        $tenant = $tenantResolver->current();
        $user = $request->user();

        return response()->json([
            'tenant' => [
                'id' => $tenant?->id,
                'company_name' => $tenant?->company_name,
            ],
            'user' => [
                'id' => $user?->id,
                'tenant_id' => $user?->tenant_id,
                'role' => $user?->role?->value,
                'email' => $user?->email,
            ],
        ]);
    }
}
