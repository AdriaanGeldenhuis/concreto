<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'customer_id',
        'quote_number',
        'status',
        'total',
        'subtotal',
        'delivery_fee',
        'vat',
        'expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'vat' => 'decimal:2',
            'expires_at' => 'date',
        ];
    }

    public const STATUSES = ['draft', 'sent', 'approved', 'rejected', 'converted', 'expired'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public static function generateQuoteNumber(): string
    {
        $latest = static::whereNotNull('quote_number')
            ->orderBy('id', 'desc')
            ->value('quote_number');

        if ($latest && preg_match('/Q-(\d+)/', $latest, $m)) {
            $next = (int) $m[1] + 1;
        } else {
            $next = 1;
        }

        return 'Q-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('line_total');
        $vat = round($subtotal * 0.15, 2);
        $total = round($subtotal + (float) $this->delivery_fee + $vat, 2);

        $this->update([
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $total,
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
