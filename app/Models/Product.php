<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'name', 'sku', 'slug', 'category_id', 'unit', 'price', 'cost_price',
        'description', 'is_active', 'in_stock', 'stock_qty', 'low_stock_threshold', 'image_path',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
            'in_stock' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true);
    }

    public function bulkPricingTiers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BulkPricingTier::class)->orderBy('min_qty');
    }

    public function getPriceForQty(float $qty): float
    {
        $tier = $this->bulkPricingTiers()
            ->where('min_qty', '<=', $qty)
            ->where(function ($q) use ($qty) {
                $q->whereNull('max_qty')->orWhere('max_qty', '>=', $qty);
            })
            ->orderBy('min_qty', 'desc')
            ->first();
        return $tier ? (float) $tier->price : (float) $this->price;
    }

    public function getPriceInclVatAttribute(): float
    {
        return round((float) $this->price * 1.15, 2);
    }

    public function getProfitMarginAttribute(): ?float
    {
        if (!$this->cost_price || $this->cost_price == 0) return null;
        return round((((float) $this->price - (float) $this->cost_price) / (float) $this->price) * 100, 1);
    }

    public function isLowStock(): bool
    {
        if ($this->stock_qty === null) return false;
        return $this->stock_qty <= ($this->low_stock_threshold ?? 10);
    }
}
