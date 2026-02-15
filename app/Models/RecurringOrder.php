<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringOrder extends Model
{
    protected $fillable = [
        'customer_id', 'order_template_id', 'frequency', 'next_run_date',
        'last_run_date', 'delivery_address_id', 'items', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'next_run_date' => 'date',
            'last_run_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(OrderTemplate::class, 'order_template_id');
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }
}
