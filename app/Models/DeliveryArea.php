<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryArea extends Model
{
    protected $fillable = [
        'name',
        'fee',
        'postal_codes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
