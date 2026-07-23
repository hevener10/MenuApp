<?php

use App\Http\Controllers\AppContextController;
use App\Http\Controllers\TenantUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'resolve.tenant'])
    ->prefix('app')
    ->group(function (): void {
        Route::get('/context', AppContextController::class);
        Route::get('/admin/users', [TenantUserController::class, 'index'])
            ->middleware('tenant.permission:users,view');
    });
