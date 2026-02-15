<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value', 'min_order_amount',
        'max_discount', 'max_uses', 'times_used', 'max_uses_per_customer',
        'is_active', 'starts_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function usage(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    public function isValid(?float $orderAmount = null, ?int $customerId = null): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses && $this->times_used >= $this->max_uses) return false;
        if ($orderAmount && $this->min_order_amount && $orderAmount < $this->min_order_amount) return false;

        if ($customerId && $this->max_uses_per_customer) {
            $customerUses = $this->usage()->where('customer_id', $customerId)->count();
            if ($customerUses >= $this->max_uses_per_customer) return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        $discount = $this->type === 'percentage'
            ? round($subtotal * ($this->value / 100), 2)
            : (float) $this->value;

        if ($this->max_discount) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return min($discount, $subtotal);
    }
}
