<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'registration', 'make', 'model', 'year', 'color', 'vin',
        'odometer', 'tank_capacity', 'fuel_type', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'odometer' => 'decimal:1',
            'tank_capacity' => 'decimal:1',
            'is_active' => 'boolean',
        ];
    }

    public function dieselLogs(): HasMany
    {
        return $this->hasMany(DieselLog::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $parts = array_filter([$this->make, $this->model, $this->registration]);
        return implode(' - ', $parts) ?: $this->registration;
    }
}
