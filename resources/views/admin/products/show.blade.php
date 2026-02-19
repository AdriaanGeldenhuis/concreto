@extends('layouts.admin')
@section('title', $product->name)
@section('content')
    <div class="page-header">
        <div class="breadcrumb"><a href="{{ route('admin.products.index') }}">Products</a> / {{ $product->name }}</div>
        <h1>{{ $product->name }}</h1>
        <span class="badge badge-{{ $product->is_active ? 'success' : 'secondary' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
        @if(!$product->in_stock || ($product->stock_qty !== null && $product->stock_qty <= 0))
            <span class="badge badge-danger">Out of Stock</span>
        @elseif($product->isLowStock())
            <span class="badge badge-warning">Low Stock</span>
        @endif
        <div style="margin-left:auto; display:flex; gap:0.5rem;">
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">Edit Product</a>
        </div>
    </div>

    <div class="form-row">
        {{-- Left Column --}}
        <div>
            {{-- Product Details --}}
            <div class="card">
                <div class="card-header">Product Details</div>
                <div class="card-body">
                    @if($product->image_path)
                        <div style="margin-bottom:1rem;">
                            <img src="{{ url('media/' . $product->image_path) }}" alt="{{ $product->name }}" style="max-width:100%; max-height:250px; object-fit:contain; border-radius:var(--radius, 6px);">
                        </div>
                    @endif
                    <div class="info-grid">
                        @if($product->sku)
                        <div class="info-row">
                            <span class="label">SKU</span>
                            <span class="value">{{ $product->sku }}</span>
                        </div>
                        @endif
                        <div class="info-row">
                            <span class="label">Category</span>
                            <span class="value">
                                @if($product->category)
                                    <span class="badge badge-secondary">{{ $product->category->name }}</span>
                                @else
                                    <span class="text-muted">Uncategorised</span>
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="label">Supplier</span>
                            <span class="value">
                                @if($product->vendor)
                                    <a href="{{ route('admin.vendors.show', $product->vendor) }}">{{ $product->vendor->name }}</a>
                                @else
                                    <span class="text-muted">No supplier assigned</span>
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="label">Unit</span>
                            <span class="value">{{ $product->unit }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Slug</span>
                            <span class="value text-muted">{{ $product->slug }}</span>
                        </div>
                    </div>
                    @if($product->description)
                        <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border, #e5e7eb);">
                            <div class="text-muted" style="font-size:0.8rem; margin-bottom:0.25rem;">Description</div>
                            <div>{{ $product->description }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Pricing --}}
            <div class="card">
                <div class="card-header">Pricing</div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="label">Price (excl VAT)</span>
                            <span class="value font-semibold">R{{ number_format($product->price, 2) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Price (incl VAT)</span>
                            <span class="value">R{{ number_format($product->price_incl_vat, 2) }}</span>
                        </div>
                        @if($product->cost_price)
                        <div class="info-row">
                            <span class="label">Cost Price</span>
                            <span class="value">R{{ number_format($product->cost_price, 2) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Profit Margin</span>
                            <span class="value">
                                @php $margin = $product->profit_margin; @endphp
                                @if($margin !== null)
                                    <span class="badge badge-{{ $margin >= 30 ? 'success' : ($margin >= 15 ? 'warning' : 'danger') }}">{{ number_format($margin, 1) }}%</span>
                                    <span class="text-muted" style="font-size:0.85rem;"> (R{{ number_format($product->price - $product->cost_price, 2) }} per {{ $product->unit }})</span>
                                @endif
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Bulk Pricing Tiers --}}
            @if($product->bulkPricingTiers->count() > 0)
            <div class="card">
                <div class="card-header">Bulk Pricing Tiers</div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Min Qty</th>
                                <th>Max Qty</th>
                                <th class="text-right">Tier Price</th>
                                <th class="text-right">Savings</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($product->bulkPricingTiers as $tier)
                            <tr>
                                <td>{{ $tier->min_qty }}</td>
                                <td>{{ $tier->max_qty ?? 'No limit' }}</td>
                                <td class="text-right font-semibold">R{{ number_format($tier->price, 2) }}</td>
                                <td class="text-right">
                                    @if($product->price > 0)
                                        <span class="badge badge-success">{{ number_format((($product->price - $tier->price) / $product->price) * 100, 0) }}% off</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column --}}
        <div>
            {{-- Stock & Status --}}
            <div class="card">
                <div class="card-header">Stock & Status</div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="label">In Stock</span>
                            <span class="value">
                                <span class="badge badge-{{ $product->in_stock ? 'success' : 'danger' }}">{{ $product->in_stock ? 'Yes' : 'No' }}</span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="label">Active</span>
                            <span class="value">
                                <span class="badge badge-{{ $product->is_active ? 'success' : 'secondary' }}">{{ $product->is_active ? 'Yes' : 'No' }}</span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="label">Stock Qty</span>
                            <span class="value font-semibold">
                                @if($product->stock_qty !== null)
                                    {{ $product->stock_qty }}
                                    @if($product->isLowStock())
                                        <span class="badge badge-warning" style="margin-left:0.25rem;">Low</span>
                                    @endif
                                @else
                                    <span class="text-muted">Not tracked</span>
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="label">Low Stock Threshold</span>
                            <span class="value">{{ $product->low_stock_threshold ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sales Performance --}}
            <div class="card">
                <div class="card-header">Sales Performance (Delivered)</div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="label">Total Orders</span>
                            <span class="value font-semibold">{{ number_format($salesStats->order_count ?? 0) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Total Qty Sold</span>
                            <span class="value font-semibold">{{ number_format($salesStats->total_qty ?? 0, 1) }} {{ $product->unit }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Total Revenue</span>
                            <span class="value font-semibold">R{{ number_format($salesStats->total_revenue ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reviews --}}
            <div class="card">
                <div class="card-header">
                    <span>Reviews</span>
                    <span class="badge badge-secondary">{{ $product->reviews->count() }}</span>
                </div>
                <div class="card-body" style="padding:0;">
                    @forelse($product->reviews->take(10) as $review)
                        <div style="padding:0.75rem 1rem; border-bottom:1px solid var(--border, #e5e7eb);">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span class="font-semibold">{{ $review->customer?->user?->name ?? 'Customer' }}</span>
                                <div>
                                    <span style="color: #f59e0b;">
                                        @for($i = 1; $i <= 5; $i++)
                                            {{ $i <= $review->rating ? '&#9733;' : '&#9734;' }}
                                        @endfor
                                    </span>
                                    <span class="badge badge-{{ $review->is_approved ? 'success' : 'warning' }}" style="margin-left:0.25rem;">
                                        {{ $review->is_approved ? 'Approved' : 'Pending' }}
                                    </span>
                                </div>
                            </div>
                            @if($review->comment)
                                <p style="margin:0.25rem 0 0; font-size:0.85rem; color:var(--text-secondary, #6b7280);">{{ Str::limit($review->comment, 120) }}</p>
                            @endif
                        </div>
                    @empty
                        <div style="padding:1.5rem; text-align:center; color:var(--text-secondary, #6b7280);">No reviews yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="card">
                <div class="card-header">Timestamps</div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="label">Created</span>
                            <span class="value text-muted">{{ $product->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Updated</span>
                            <span class="value text-muted">{{ $product->updated_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card">
                <div class="card-header">Actions</div>
                <div class="card-body">
                    <div class="d-flex gap-1 flex-wrap">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">Edit Product</a>
                        <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline btn-sm" target="_blank">View on Site</a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product? This cannot be undone.')">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
