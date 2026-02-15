<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkPricingTier extends Model
{
    protected $fillable = ['product_id', 'min_qty', 'max_qty', 'price'];

    protected function casts(): array
    {
        return [
            'min_qty' => 'decimal:2',
            'max_qty' => 'decimal:2',
            'price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
