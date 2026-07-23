<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    use BelongsToTenant;
    use HasAuditTrail;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_group_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permission_groups');
    }
}
