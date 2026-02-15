<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    protected $fillable = [
        'order_id',
        'invoice_no',
        'pdf_path',
        'emailed_at',
    ];

    protected function casts(): array
    {
        return [
            'emailed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function reminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceReminder::class);
    }

    public static function generateInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $prefix = 'INV';
            $date = now()->format('Ym');
            $pattern = "{$prefix}-{$date}-%";

            // Lock the table row to prevent race conditions
            $latest = static::where('invoice_no', 'like', $pattern)
                ->lockForUpdate()
                ->orderBy('invoice_no', 'desc')
                ->value('invoice_no');

            if ($latest) {
                $seq = (int) substr($latest, -6) + 1;
            } else {
                $seq = 1;
            }

            return sprintf('%s-%s-%06d', $prefix, $date, $seq);
        });
    }
}
