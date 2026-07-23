<?php

namespace App\Models;

use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasAuditTrail;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'max_computers',
        'is_active',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'features' => 'array',
        ];
    }
}
