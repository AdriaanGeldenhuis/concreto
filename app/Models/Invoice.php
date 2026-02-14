<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ym');
        $latest = static::where('invoice_no', 'like', "{$prefix}-{$date}-%")
            ->orderBy('invoice_no', 'desc')
            ->value('invoice_no');

        if ($latest) {
            $seq = (int) substr($latest, -4) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $seq);
    }
}
