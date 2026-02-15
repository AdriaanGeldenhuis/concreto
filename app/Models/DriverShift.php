<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverShift extends Model
{
    protected $fillable = [
        'driver_id', 'clock_in', 'clock_out', 'hours_worked', 'deliveries_count', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'hours_worked' => 'decimal:2',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function calculateHours(): float
    {
        if (!$this->clock_out) return 0;
        return round($this->clock_in->diffInMinutes($this->clock_out) / 60, 2);
    }
}
