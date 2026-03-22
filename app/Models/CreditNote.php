<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class CreditNote extends Model
{
    protected $fillable = [
        'credit_note_no', 'invoice_id', 'order_id', 'customer_id',
        'amount', 'vat_amount', 'total', 'reason', 'notes',
        'pdf_path', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateCreditNoteNumber(): string
    {
        return DB::transaction(function () {
            $prefix = 'CN';
            $date = now()->format('Ym');
            $pattern = "{$prefix}-{$date}-%";

            $latest = static::where('credit_note_no', 'like', $pattern)
                ->lockForUpdate()
                ->orderBy('credit_note_no', 'desc')
                ->value('credit_note_no');

            $seq = $latest ? (int) substr($latest, -6) + 1 : 1;

            return sprintf('%s-%s-%06d', $prefix, $date, $seq);
        });
    }
}
