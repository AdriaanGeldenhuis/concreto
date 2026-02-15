@extends('layouts.app')
@section('title', 'Products')
@section('content')

<div class="section section--alt">
    <div class="container">
        <h1 class="section-title">Our Products</h1>
        <p class="section-subtitle">Browse our range of quality building materials</p>

        <div style="display:grid; grid-template-columns:1fr; gap:2rem; margin-top:2rem;">
            <!-- Filter Sidebar -->
            <div>
                <div class="card" style="position:sticky; top:80px;">
                    <div class="card-header">Filter Products</div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('products') }}" id="filter-form">
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-control" onchange="document.getElementById('filter-form').submit()">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Price Range (excl VAT)</label>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
                                    <input type="number" name="price_min" class="form-control" placeholder="Min" value="{{ request('price_min') }}" step="0.01">
                                    <input type="number" name="price_max" class="form-control" placeholder="Max" value="{{ request('price_max') }}" step="0.01">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" name="in_stock" value="1" id="in_stock" {{ request('in_stock') ? 'checked' : '' }} onchange="document.getElementById('filter-form').submit()">
                                    <label for="in_stock">In Stock Only</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Sort By</label>
                                <select name="sort" class="form-control" onchange="document.getElementById('filter-form').submit()">
                                    <option value="">Default</option>
                                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                </select>
                            </div>

                            <div style="display:flex; gap:0.5rem;">
                                <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                                @if(request()->hasAny(['category', 'price_min', 'price_max', 'in_stock', 'sort']))
                                    <a href="{{ route('products') }}" class="btn btn-ghost" style="flex-shrink:0;">Clear</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div>
                @if($products->isEmpty())
                    <div class="empty-state">
                        <div class="icon" style="font-size:3rem; opacity:0.3;">&#128722;</div>
                        <h3>No Products Found</h3>
                        <p>Try adjusting your filters or browse all products.</p>
                        <a href="{{ route('products') }}" class="btn btn-primary">View All Products</a>
                    </div>
                @else
                    <div class="product-grid">
                        @foreach($products as $product)
                            <a href="{{ route('products.show', $product) }}" class="product-card">
                                <div class="product-card-img">
                                    @if($product->image_path)
                                        <img src="/media/{{ $product->image_path }}" alt="{{ $product->name }}">
                                    @else
                                        <span class="product-card-placeholder">&#9881;</span>
                                    @endif
                                    @if(!$product->in_stock)
                                        <div style="position:absolute; top:0.5rem; right:0.5rem;">
                                            <span class="badge badge-danger">Out of Stock</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="product-card-body">
                                    <h3>{{ $product->name }}</h3>
                                    <p class="product-card-meta">
                                        @if($product->category)
                                            {{ $product->category->name }}
                                            <span class="meta-dot">&middot;</span>
                                        @endif
                                        per {{ $product->unit }}
                                    </p>
                                    <div class="product-card-divider"></div>
                                    <div class="product-card-price">R {{ number_format($product->price, 2) }} excl VAT</div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($products->hasPages())
                        <div style="margin-top:2rem;">
                            {{ $products->appends(request()->query())->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<div class="section section--dark">
    <div class="container text-center">
        <h2 class="section-title text-white">Need a Custom Quote?</h2>
        <p class="section-subtitle text-white" style="opacity:0.8;">Get competitive pricing for bulk orders and deliveries</p>
        <a href="{{ route('request-quote') }}" class="btn btn-primary btn-lg">Request a Quote</a>
    </div>
</div>

@push('styles')
<style>
@media (min-width: 768px) {
    .section .container > div { grid-template-columns: 280px 1fr; }
}
@media (min-width: 992px) {
    .product-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1200px) {
    .product-grid { grid-template-columns: repeat(3, 1fr); }
}
.empty-state { text-align:center; padding:3rem 1rem; }
.empty-state .icon { font-size:3rem; margin-bottom:1rem; }
.empty-state h3 { margin-bottom:0.5rem; color:var(--text); }
.empty-state p { color:var(--text-muted); margin-bottom:1.5rem; }
</style>
@endpush
@endsection
