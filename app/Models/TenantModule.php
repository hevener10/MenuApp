<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantModule extends Model
{
    use BelongsToTenant;
    use HasAuditTrail;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'module_id',
        'activated_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
