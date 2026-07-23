<?php

namespace App\Providers;

use App\Support\Audit\AuditLogger;
use App\Support\Tenancy\CurrentTenantResolver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrentTenantResolver::class);
        $this->app->singleton(AuditLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
