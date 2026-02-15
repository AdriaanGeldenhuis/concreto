@extends('layouts.app')
@section('title', 'Order')
@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem; max-width: 800px;">
    <div class="page-header">
        <h1>Your Order</h1>
    </div>

    @if(empty($items))
        <div class="empty-state">
            <div class="icon">&#128722;</div>
            <h3>Your order is empty</h3>
            <p class="text-muted">Browse our products and add items to get started.</p>
            <a href="{{ route('products') }}" class="btn btn-primary">View Products</a>
        </div>
    @else
        <div class="card">
            <div class="card-body" style="padding:0;">
                @foreach($items as $item)
                <div class="cart-item" data-product-id="{{ $item['product']->id }}">
                    <div class="cart-item-img">
                        @if($item['product']->image_path)
                            <img src="/media/{{ $item['product']->image_path }}" alt="{{ $item['product']->name }}">
                        @else
                            <span>&#9881;</span>
                        @endif
                    </div>
                    <div class="cart-item-info">
                        <h4>{{ $item['product']->name }}</h4>
                        <div class="text-muted text-small">R{{ number_format($item['product']->price, 2) }} excl VAT per {{ $item['product']->unit }}</div>
                    </div>
                    <div class="cart-item-qty">
                        <form method="POST" action="{{ route('cart.update') }}" class="cart-qty-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                            <input type="number" name="qty" value="{{ $item['qty'] }}" min="0.01" step="0.01" class="form-control cart-qty-input" style="width:80px;text-align:center;">
                        </form>
                    </div>
                    <div class="cart-item-total">
                        <strong>R{{ number_format($item['line_total'], 2) }}</strong>
                    </div>
                    <form method="POST" action="{{ route('cart.remove') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                        <button type="submit" class="btn btn-danger btn-sm" title="Remove">&times;</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="cart-totals">
                    <div class="cart-totals-row">
                        <span>Subtotal (excl VAT)</span>
                        <span>R{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="cart-totals-row">
                        <span>VAT (15%)</span>
                        <span>R{{ number_format($vat, 2) }}</span>
                    </div>
                    <div class="cart-totals-row cart-totals-total">
                        <span>Total</span>
                        <span>R{{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-1" style="justify-content:space-between;align-items:center;flex-wrap:wrap;">
            <a href="{{ route('products') }}" class="btn btn-outline">Continue Shopping</a>
            @auth
                <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg">Proceed to Checkout</a>
            @else
                <a href="{{ route('login') }}?redirect={{ urlencode(route('checkout')) }}" class="btn btn-primary btn-lg">Login to Checkout</a>
            @endauth
        </div>
    @endif
</div>
@endsection
