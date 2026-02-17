@extends('layouts.admin')
@section('title', isset($product) ? 'Edit Product' : 'New Product')
@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('admin.products.index') }}">Products</a> / {{ isset($product) ? 'Edit' : 'New' }}
        </div>
        <h1>{{ isset($product) ? 'Edit' : 'New' }} Product</h1>
    </div>

    <form method="POST" action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($product)) @method('PUT') @endif

        <div class="card">
            <div class="card-header">Product Details</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">None</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ old('category_id', $product->category_id ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Price (R)</label>
                        <input type="number" name="price" class="form-control" step="0.01" value="{{ old('price', $product->price ?? '') }}" required>
                        @error('price')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cost Price (excl VAT)</label>
                        <input type="number" name="cost_price" class="form-control" step="0.01" value="{{ old('cost_price', $product->cost_price ?? '') }}">
                        <div class="form-hint">For profit tracking</div>
                        @error('cost_price')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit" class="form-control" value="{{ old('unit', $product->unit ?? 'ton') }}" required>
                        <div class="form-hint">e.g. ton, m3, bag, load</div>
                    </div>
                    @if(isset($product) && $product->cost_price)
                    <div class="form-group">
                        <label class="form-label">Profit Margin</label>
                        <div class="form-control" style="background: #f5f5f5; border: 1px solid #ddd;">
                            @php
                                $margin = $product->price > 0 && $product->cost_price > 0
                                    ? (($product->price - $product->cost_price) / $product->price) * 100
                                    : 0;
                            @endphp
                            <strong>{{ number_format($margin, 2) }}%</strong>
                            <span class="text-muted text-small"> (R{{ number_format($product->price - $product->cost_price, 2) }} per unit)</span>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_qty" class="form-control" step="1" value="{{ old('stock_qty', $product->stock_qty ?? 0) }}">
                        @error('stock_qty')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Low Stock Alert Threshold</label>
                        <input type="number" name="low_stock_threshold" class="form-control" step="1" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}">
                        <div class="form-hint">Alert when stock falls below this level</div>
                        @error('low_stock_threshold')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Image & Status</div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    @if(isset($product) && $product->image)
                        <div class="form-hint mt-1">Current image is set. Upload a new one to replace it.</div>
                    @endif
                </div>
                <div class="form-row">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                        <label for="is_active">Active</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="in_stock" value="1" id="in_stock" {{ old('in_stock', $product->in_stock ?? true) ? 'checked' : '' }}>
                        <label for="in_stock">In Stock</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>Bulk Pricing Tiers</span>
                <button type="button" class="btn btn-primary btn-sm" onclick="addTier()">+ Add Tier</button>
            </div>
            <div class="card-body">
                <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 1rem;">
                    Set volume discounts. When a customer orders a quantity within a tier range, they get that tier's price instead of the standard price.
                </p>
                <div id="bulk-tiers">
                    @if(isset($product) && $product->bulkPricingTiers && $product->bulkPricingTiers->count() > 0)
                        @foreach($product->bulkPricingTiers as $i => $tier)
                            <div class="tier-row form-row" data-index="{{ $i }}" style="align-items: flex-end; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <div class="form-group" style="flex: 1;">
                                    @if($i === 0)<label class="form-label">Min Qty</label>@endif
                                    <input type="number" name="tiers[{{ $i }}][min_qty]" class="form-control" step="0.01" min="0" value="{{ $tier->min_qty }}" placeholder="Min qty">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    @if($i === 0)<label class="form-label">Max Qty</label>@endif
                                    <input type="number" name="tiers[{{ $i }}][max_qty]" class="form-control" step="0.01" min="0" value="{{ $tier->max_qty }}" placeholder="No limit">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    @if($i === 0)<label class="form-label">Price (R per unit)</label>@endif
                                    <input type="number" name="tiers[{{ $i }}][price]" class="form-control" step="0.01" min="0" value="{{ $tier->price }}" placeholder="Tier price">
                                </div>
                                <div style="flex: 0 0 auto;">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeTier(this)" title="Remove">&times;</button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div id="no-tiers-msg" style="{{ isset($product) && $product->bulkPricingTiers && $product->bulkPricingTiers->count() > 0 ? 'display:none;' : '' }} color: #888; font-size: 0.85rem; padding: 0.5rem 0;">
                    No bulk pricing tiers configured. Standard price applies to all quantities.
                </div>
            </div>
        </div>

        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary">{{ isset($product) ? 'Update' : 'Create' }} Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>

    <script>
    let tierIndex = {{ isset($product) && $product->bulkPricingTiers ? $product->bulkPricingTiers->count() : 0 }};

    function addTier() {
        const container = document.getElementById('bulk-tiers');
        const row = document.createElement('div');
        row.className = 'tier-row form-row';
        row.dataset.index = tierIndex;
        row.style.cssText = 'align-items: flex-end; gap: 0.5rem; margin-bottom: 0.5rem;';
        row.innerHTML = `
            <div class="form-group" style="flex: 1;">
                ${tierIndex === 0 ? '<label class="form-label">Min Qty</label>' : ''}
                <input type="number" name="tiers[${tierIndex}][min_qty]" class="form-control" step="0.01" min="0" placeholder="Min qty">
            </div>
            <div class="form-group" style="flex: 1;">
                ${tierIndex === 0 ? '<label class="form-label">Max Qty</label>' : ''}
                <input type="number" name="tiers[${tierIndex}][max_qty]" class="form-control" step="0.01" min="0" placeholder="No limit">
            </div>
            <div class="form-group" style="flex: 1;">
                ${tierIndex === 0 ? '<label class="form-label">Price (R per unit)</label>' : ''}
                <input type="number" name="tiers[${tierIndex}][price]" class="form-control" step="0.01" min="0" placeholder="Tier price">
            </div>
            <div style="flex: 0 0 auto;">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeTier(this)" title="Remove">&times;</button>
            </div>
        `;
        container.appendChild(row);
        tierIndex++;
        document.getElementById('no-tiers-msg').style.display = 'none';
    }

    function removeTier(btn) {
        btn.closest('.tier-row').remove();
        if (document.querySelectorAll('.tier-row').length === 0) {
            document.getElementById('no-tiers-msg').style.display = 'block';
        }
    }
    </script>
@endsection
