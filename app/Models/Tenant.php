<?php

namespace App\Models;

use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasAuditTrail;
    use HasFactory;

    protected $fillable = [
        'uuid',
        'company_name',
        'trade_name',
        'email',
        'phone',
        'plan_id',
        'plan_expires_at',
        'is_active',
        'logo_path',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'plan_expires_at' => 'datetime',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
