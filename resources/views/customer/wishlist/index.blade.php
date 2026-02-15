@extends('layouts.portal')
@section('title', 'My Wishlist')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="page-header">
        <h1>My Wishlist</h1>
        <p class="text-muted">Save products you're interested in for later</p>
    </div>

    @if($items->isEmpty())
        <div class="empty-state">
            <div class="icon" style="font-size:3rem; opacity:0.3;">&#10084;</div>
            <h3>Your Wishlist is Empty</h3>
            <p>Start adding products to your wishlist to keep track of items you like.</p>
            <a href="{{ route('products') }}" class="btn btn-primary">Browse Products</a>
        </div>
    @else
        <div class="product-grid">
            @foreach($items as $item)
                <div class="product-card" style="position:relative;">
                    <a href="{{ route('products.show', $item->product) }}" style="display:contents;">
                        <div class="product-card-img">
                            @if($item->product->image_path)
                                <img src="/media/{{ $item->product->image_path }}" alt="{{ $item->product->name }}">
                            @else
                                <span class="product-card-placeholder">&#9881;</span>
                            @endif
                            @if(!$item->product->in_stock)
                                <div style="position:absolute; top:0.5rem; right:0.5rem;">
                                    <span class="badge badge-danger">Out of Stock</span>
                                </div>
                            @endif
                        </div>
                        <div class="product-card-body">
                            <h3>{{ $item->product->name }}</h3>
                            <p class="product-card-meta">
                                @if($item->product->category)
                                    {{ $item->product->category->name }}
                                    <span class="meta-dot">&middot;</span>
                                @endif
                                per {{ $item->product->unit }}
                            </p>
                            <div class="product-card-divider"></div>
                            <div class="product-card-price">R {{ number_format($item->product->price, 2) }} excl VAT</div>
                        </div>
                    </a>

                    <div style="padding:0 1rem 1rem; display:flex; gap:0.5rem;">
                        @if($item->product->in_stock)
                            <form class="add-to-cart-form" data-product-id="{{ $item->product->id }}" style="flex:1;">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-primary btn-sm btn-block btn-add-cart">
                                    Add to Order
                                </button>
                            </form>
                        @else
                            <a href="{{ route('request-quote') }}" class="btn btn-ghost btn-sm" style="flex:1; text-align:center;">
                                Request Quote
                            </a>
                        @endif

                        <form method="POST" action="{{ route('customer.wishlist.toggle') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                            <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Remove from wishlist" style="border-radius:var(--radius-sm); width:auto; height:auto; padding:0.4rem 0.875rem;">
                                &#10005;
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if($items->hasPages())
            <div style="margin-top:2rem;">
                {{ $items->links() }}
            </div>
        @endif
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.account') }}"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection

@push('styles')
<style>
@@media (min-width: 768px) {
    .product-grid { grid-template-columns: repeat(2, 1fr); }
}
@@media (min-width: 992px) {
    .product-grid { grid-template-columns: repeat(3, 1fr); }
}
@@media (min-width: 1200px) {
    .product-grid { grid-template-columns: repeat(4, 1fr); }
}
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: rgba(255,255,255,0.02);
    border-radius: var(--radius-lg);
    border: 1px solid var(--glass-border);
}
.empty-state .icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}
.empty-state h3 {
    margin-bottom: 0.5rem;
    color: var(--text);
}
.empty-state p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}
</style>
@endpush
