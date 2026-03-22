<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierInvoice extends Model
{
    protected $fillable = [
        'vendor_id', 'invoice_number', 'invoice_date', 'due_date',
        'amount', 'vat_amount', 'total', 'status', 'amount_paid',
        'reference', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getBalanceDueAttribute(): float
    {
        return (float) $this->total - (float) $this->amount_paid;
    }

    public function getDaysOverdueAttribute(): int
    {
        if ($this->status === 'paid') return 0;
        return max(0, (int) now()->diffInDays($this->due_date, false));
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    public function recordPayment(float $amount): void
    {
        $this->amount_paid = (float) $this->amount_paid + $amount;
        if ($this->amount_paid >= (float) $this->total) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }
        $this->save();
    }
}
