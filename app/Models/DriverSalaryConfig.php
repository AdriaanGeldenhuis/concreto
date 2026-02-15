<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverSalaryConfig extends Model
{
    protected $fillable = [
        'driver_id', 'pay_type', 'rate', 'overtime_rate', 'bonus_per_delivery', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'overtime_rate' => 'decimal:2',
            'bonus_per_delivery' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
