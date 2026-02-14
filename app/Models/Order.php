<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUSES = [
        'DRAFT',
        'PENDING_PAYMENT',
        'PAID',
        'PLACED',
        'ASSIGNED',
        'ACCEPTED',
        'LOADED',
        'IN_TRANSIT',
        'ARRIVED',
        'DELIVERED_PENDING_SIGNATURE',
        'DELIVERED',
        'CANCELLED',
        'REFUNDED',
    ];

    protected $fillable = [
        'customer_id',
        'status',
        'subtotal',
        'delivery_fee',
        'vat',
        'total',
        'delivery_address_id',
        'scheduled_date',
        'scheduled_time_window',
        'notes',
        'driver_id',
        'order_number',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'vat' => 'decimal:2',
            'total' => 'decimal:2',
            'scheduled_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function proofOfDelivery(): HasOne
    {
        return $this->hasOne(ProofOfDelivery::class);
    }

    public function driverLocations(): HasMany
    {
        return $this->hasMany(DriverLocation::class);
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');
        $vat = round($subtotal * 0.15, 2); // 15% VAT (South Africa)
        $this->update([
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $subtotal + $vat + $this->delivery_fee,
        ]);
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'CON';
        $date = now()->format('Ymd');
        $latest = static::where('order_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('order_number', 'desc')
            ->value('order_number');

        if ($latest) {
            $seq = (int) substr($latest, -4) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $seq);
    }
}
