<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;
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

    public const ALLOWED_TRANSITIONS = [
        'DRAFT'              => ['PENDING_PAYMENT', 'PLACED', 'CANCELLED'],
        'PENDING_PAYMENT'    => ['PLACED', 'CANCELLED'],
        'PAID'               => ['PLACED', 'CANCELLED'],
        'PLACED'             => ['ASSIGNED', 'CANCELLED'],
        'ASSIGNED'           => ['ACCEPTED', 'PLACED', 'CANCELLED'],
        'ACCEPTED'           => ['LOADED', 'CANCELLED'],
        'LOADED'             => ['IN_TRANSIT', 'CANCELLED'],
        'IN_TRANSIT'         => ['ARRIVED', 'CANCELLED'],
        'ARRIVED'            => ['DELIVERED', 'CANCELLED'],
        'DELIVERED'          => [],
        'DELIVERED_PENDING_SIGNATURE' => ['DELIVERED', 'CANCELLED'],
        'CANCELLED'          => ['REFUNDED'],
        'REFUNDED'           => [],
    ];

    protected $fillable = [
        'customer_id',
        'status',
        'subtotal',
        'delivery_fee',
        'vat',
        'total',
        'locked_total',
        'delivery_address_id',
        'scheduled_date',
        'scheduled_time_window',
        'notes',
        'driver_id',
        'order_number',
        'idempotency_key',
        'promo_code_id',
        'discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'vat' => 'decimal:2',
            'total' => 'decimal:2',
            'locked_total' => 'decimal:2',
            'discount_amount' => 'decimal:2',
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

    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed, true);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');
        $discount = (float) ($this->discount_amount ?? 0);
        $taxableAmount = max(0, $subtotal - $discount);
        $vat = round($taxableAmount * 0.15, 2); // 15% VAT (South Africa)
        $total = round($taxableAmount + $vat + $this->delivery_fee, 2);
        $this->update([
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $total,
            'locked_total' => $total,
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
