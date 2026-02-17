<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DieselLog extends Model
{
    protected $fillable = [
        'driver_id', 'vehicle_id', 'fill_date', 'litres', 'cost_per_litre',
        'total_cost', 'odometer', 'station', 'receipt_reference',
        'full_tank', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fill_date' => 'date',
            'litres' => 'decimal:2',
            'cost_per_litre' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'odometer' => 'decimal:1',
            'full_tank' => 'boolean',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getKmPerLitreAttribute(): ?float
    {
        if (!$this->full_tank || !$this->odometer) return null;

        $previous = static::where('vehicle_id', $this->vehicle_id)
            ->where('full_tank', true)
            ->where('id', '<', $this->id)
            ->whereNotNull('odometer')
            ->orderBy('id', 'desc')
            ->first();

        if (!$previous || !$previous->odometer) return null;

        $km = (float) $this->odometer - (float) $previous->odometer;
        return $km > 0 ? round($km / (float) $this->litres, 2) : null;
    }
}
