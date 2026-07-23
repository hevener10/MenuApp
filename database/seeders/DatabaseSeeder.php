<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $starterPlan = Plan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'Plano base para bootstrap do sistema multi-tenant.',
            'price' => 0,
            'max_computers' => 1,
            'is_active' => true,
        ]);

        $usersModule = Module::query()->create([
            'name' => 'Usuarios',
            'slug' => 'users',
            'description' => 'Gestao de usuarios e permissoes por tenant.',
            'is_additional' => false,
            'price' => 0,
        ]);

        $tenant = Tenant::query()->create([
            'uuid' => (string) Str::uuid(),
            'company_name' => 'Tenant Demo',
            'trade_name' => 'Menu Demo',
            'email' => 'tenant@example.com',
            'phone' => '(11) 99999-9999',
            'plan_id' => $starterPlan->getKey(),
            'is_active' => true,
            'settings' => [
                'timezone' => 'America/Sao_Paulo',
            ],
        ]);

        TenantModule::query()->create([
            'tenant_id' => $tenant->getKey(),
            'module_id' => $usersModule->getKey(),
            'activated_at' => now(),
            'is_active' => true,
        ]);

        $permission = Permission::query()->create([
            'module' => 'users',
            'action' => 'view',
            'description' => 'Permite listar usuarios do tenant.',
        ]);

        $group = PermissionGroup::query()->create([
            'tenant_id' => $tenant->getKey(),
            'name' => 'Administradores',
            'description' => 'Grupo inicial com acesso de consulta aos usuarios do tenant.',
        ]);

        $group->permissions()->attach($permission);

        $admin = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'name' => 'Tenant Admin',
            'email' => 'admin@tenant.test',
            'role' => UserRole::ADMIN,
        ]);

        $group->users()->attach($admin);

        User::factory()->superadmin()->create([
            'name' => 'Platform Superadmin',
            'email' => 'superadmin@example.com',
        ]);
    }
}
