<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTemplate extends Model
{
    protected $fillable = [
        'customer_id', 'name', 'delivery_address_id', 'items', 'notes',
    ];

    protected function casts(): array
    {
        return ['items' => 'array'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function recurringOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RecurringOrder::class, 'order_template_id');
    }
}
