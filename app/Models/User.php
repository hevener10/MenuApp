<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use BelongsToTenant;
    use HasAuditTrail;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function permissionGroups()
    {
        return $this->belongsToMany(PermissionGroup::class, 'user_permission_groups');
    }

    public function isSuperadmin(): bool
    {
        return $this->role === UserRole::SUPERADMIN;
    }

    public function hasPermission(string $module, string $action): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isSuperadmin()) {
            return true;
        }

        return $this->permissionGroups()
            ->whereHas('permissions', function ($query) use ($module, $action): void {
                $query
                    ->where('module', $module)
                    ->where('action', $action);
            })
            ->exists();
    }
}
