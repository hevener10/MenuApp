<?php

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenantResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createTenant(string $name, string $email): Tenant
{
    $plan = Plan::query()->first() ?? Plan::query()->create([
        'name' => 'Starter',
        'slug' => 'starter',
        'description' => 'Plano inicial',
        'price' => 0,
        'max_computers' => 1,
        'is_active' => true,
    ]);

    return Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'company_name' => $name,
        'trade_name' => $name,
        'email' => $email,
        'plan_id' => $plan->getKey(),
        'is_active' => true,
    ]);
}

function grantPermission(User $user, string $module, string $action): void
{
    $permission = Permission::query()->firstOrCreate([
        'module' => $module,
        'action' => $action,
    ], [
        'description' => sprintf('%s.%s', $module, $action),
    ]);

    $group = PermissionGroup::query()->create([
        'tenant_id' => $user->tenant_id,
        'name' => sprintf('%s-%s-%s', $module, $action, Str::lower(Str::random(6))),
        'description' => 'Grupo de permissao de teste',
    ]);

    $group->permissions()->attach($permission);
    $group->users()->attach($user);
}

it('isolates tenant-scoped user listing to the authenticated tenant', function () {
    $tenantA = createTenant('Tenant A', 'tenant-a@example.com');
    $tenantB = createTenant('Tenant B', 'tenant-b@example.com');

    $userA = User::factory()->forTenant($tenantA)->create([
        'email' => 'shared@example.com',
        'role' => UserRole::ADMIN,
    ]);
    $userB = User::factory()->forTenant($tenantB)->create([
        'email' => 'shared@example.com',
        'role' => UserRole::ADMIN,
    ]);

    grantPermission($userA, 'users', 'view');

    $this->actingAs($userA)
        ->getJson('/app/admin/users')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.email', $userA->email)
        ->assertJsonMissing(['email' => $userB->email]);
});

it('allows the same email in different tenants', function () {
    $tenantA = createTenant('Tenant A', 'tenant-a@example.com');
    $tenantB = createTenant('Tenant B', 'tenant-b@example.com');

    User::factory()->forTenant($tenantA)->create([
        'email' => 'duplicated@example.com',
    ]);
    User::factory()->forTenant($tenantB)->create([
        'email' => 'duplicated@example.com',
    ]);

    expect(
        User::query()
            ->withoutGlobalScopes()
            ->where('email', 'duplicated@example.com')
            ->count()
    )->toBe(2);
});

it('blocks tenant users from switching tenant context via request header', function () {
    $tenantA = createTenant('Tenant A', 'tenant-a@example.com');
    $tenantB = createTenant('Tenant B', 'tenant-b@example.com');

    $userA = User::factory()->forTenant($tenantA)->create([
        'role' => UserRole::ADMIN,
    ]);

    $this->actingAs($userA)
        ->withHeader('X-Tenant-Id', (string) $tenantB->getKey())
        ->getJson('/app/context')
        ->assertForbidden();
});

it('forbids access when the user lacks the required tenant permission', function () {
    $tenant = createTenant('Tenant A', 'tenant-a@example.com');
    $user = User::factory()->forTenant($tenant)->create([
        'role' => UserRole::MANAGER,
    ]);

    $this->actingAs($user)
        ->getJson('/app/admin/users')
        ->assertForbidden();
});

it('allows a superadmin to inspect a tenant when a tenant header is provided', function () {
    $tenant = createTenant('Tenant A', 'tenant-a@example.com');
    $tenantUser = User::factory()->forTenant($tenant)->create([
        'email' => 'tenant-user@example.com',
    ]);
    $superadmin = User::factory()->superadmin()->create([
        'email' => 'superadmin@example.com',
    ]);

    $this->actingAs($superadmin)
        ->withHeader('X-Tenant-Id', (string) $tenant->getKey())
        ->getJson('/app/admin/users')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.email', $tenantUser->email);
});

it('records audit logs for tenant-scoped changes', function () {
    $tenant = createTenant('Tenant A', 'tenant-a@example.com');
    $admin = User::factory()->forTenant($tenant)->create([
        'role' => UserRole::ADMIN,
        'email' => 'admin@example.com',
    ]);

    app(CurrentTenantResolver::class)->set($tenant);

    $this->actingAs($admin);

    $createdUser = User::factory()->create([
        'email' => 'audited@example.com',
        'role' => UserRole::OPERATOR,
    ]);

    app(CurrentTenantResolver::class)->forget();

    $auditLog = AuditLog::query()
        ->where('auditable_type', User::class)
        ->where('auditable_id', $createdUser->getKey())
        ->where('event', 'created')
        ->first();

    expect($auditLog)->not->toBeNull()
        ->and($auditLog->tenant_id)->toBe($tenant->getKey())
        ->and($auditLog->user_id)->toBe($admin->getKey());
});
