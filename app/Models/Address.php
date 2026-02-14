<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'customer_id',
        'label',
        'line1',
        'line2',
        'city',
        'province',
        'postal_code',
        'gps_lat',
        'gps_lng',
    ];

    protected function casts(): array
    {
        return [
            'gps_lat' => 'decimal:7',
            'gps_lng' => 'decimal:7',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([$this->line1, $this->line2, $this->city, $this->province, $this->postal_code])
            ->filter()
            ->implode(', ');
    }
}
