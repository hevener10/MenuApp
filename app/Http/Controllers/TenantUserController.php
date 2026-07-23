<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class TenantUserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->orderBy('id')
            ->get(['id', 'tenant_id', 'name', 'email', 'role', 'is_active']);

        return response()->json([
            'data' => $users,
        ]);
    }
}
