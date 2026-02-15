<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'type',
        'credit_limit',
        'payment_terms',
        'pay_before_dispatch',
        'default_address_id',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'pay_before_dispatch' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'default_address_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function isCod(): bool
    {
        return $this->type === 'COD';
    }

    public function isAccount(): bool
    {
        return $this->type === 'ACCOUNT';
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orderTemplates(): HasMany
    {
        return $this->hasMany(OrderTemplate::class);
    }

    public function recurringOrders(): HasMany
    {
        return $this->hasMany(RecurringOrder::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /** Outstanding balance for ACCOUNT customers */
    public function getOutstandingBalanceAttribute(): float
    {
        return (float) $this->orders()
            ->where('status', 'DELIVERED')
            ->whereDoesntHave('payments', fn($q) => $q->where('status', 'completed'))
            ->sum('total');
    }

    /** Available credit = limit - outstanding */
    public function getAvailableCreditAttribute(): ?float
    {
        if (!$this->credit_limit) return null;
        return max(0, (float) $this->credit_limit - $this->outstanding_balance);
    }
}
