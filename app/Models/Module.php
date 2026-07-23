<?php

namespace App\Models;

use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasAuditTrail;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_additional',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'is_additional' => 'boolean',
            'price' => 'decimal:2',
        ];
    }
}
