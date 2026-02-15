@extends('layouts.app')
@section('title', $product->name)
@section('content')

<div class="section">
    <div class="container">
        <div class="page-header">
            <div class="breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span class="breadcrumb-sep">/</span>
                <a href="{{ route('products') }}">Products</a>
                @if($product->category)
                    <span class="breadcrumb-sep">/</span>
                    <a href="{{ route('products') }}?category={{ $product->category->slug }}">{{ $product->category->name }}</a>
                @endif
                <span class="breadcrumb-sep">/</span>
                <span>{{ $product->name }}</span>
            </div>
        </div>

        <div class="product-detail">
            <div class="product-detail-gallery">
                <div class="product-detail-img-wrap">
                    @if($product->image_path)
                        <img src="/media/{{ $product->image_path }}" alt="{{ $product->name }}" class="product-detail-main-img">
                    @else
                        <div class="product-detail-no-img">&#9881;</div>
                    @endif
                </div>
            </div>

            <div class="product-detail-info">
                <div class="d-flex gap-1 flex-wrap mb-1">
                    @if($product->category)
                        <span class="badge badge-primary">{{ $product->category->name }}</span>
                    @endif
                    @if($product->in_stock)
                        <span class="badge badge-success">In Stock</span>
                    @else
                        <span class="badge badge-danger">Out of Stock</span>
                    @endif
                </div>

                <h1 class="product-detail-title">{{ $product->name }}</h1>

                <div class="product-detail-price-row">
                    <span class="product-detail-price">R{{ number_format($product->price, 2) }}</span>
                    <span class="product-detail-unit">per {{ $product->unit }}</span>
                </div>

                @if($product->description)
                    <div class="product-detail-desc">
                        <p>{{ $product->description }}</p>
                    </div>
                @endif

                <div class="product-detail-specs">
                    <div class="spec-row">
                        <span class="spec-label">Unit</span>
                        <span class="spec-value">{{ ucfirst($product->unit) }}</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Price (excl. VAT)</span>
                        <span class="spec-value">R{{ number_format($product->price, 2) }}</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">VAT (15%)</span>
                        <span class="spec-value">R{{ number_format($product->price * 0.15, 2) }}</span>
                    </div>
                    <div class="spec-row spec-row--total">
                        <span class="spec-label">Total (incl. VAT)</span>
                        <span class="spec-value">R{{ number_format($product->price * 1.15, 2) }}</span>
                    </div>
                    @if($product->category)
                    <div class="spec-row">
                        <span class="spec-label">Category</span>
                        <span class="spec-value">{{ $product->category->name }}</span>
                    </div>
                    @endif
                    <div class="spec-row">
                        <span class="spec-label">Availability</span>
                        <span class="spec-value">{{ $product->in_stock ? 'Available for delivery' : 'Currently unavailable' }}</span>
                    </div>
                </div>

                @if($product->in_stock)
                <div class="product-detail-cart">
                    <form class="add-to-cart-form" data-product-id="{{ $product->id }}">
                        <div class="product-detail-qty">
                            <label class="form-label">Quantity ({{ $product->unit }})</label>
                            <input type="number" name="qty" value="1" min="0.01" step="0.01" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg btn-block btn-add-cart">
                            Add to Order
                        </button>
                    </form>
                </div>
                @else
                <div class="product-detail-cart">
                    <a href="{{ route('request-quote') }}" class="btn btn-primary btn-lg btn-block">Request a Quote</a>
                </div>
                @endif

                <div class="product-detail-links">
                    <a href="{{ route('cart.index') }}" class="btn btn-outline btn-sm">View Order</a>
                    <a href="{{ route('request-quote') }}" class="btn btn-outline btn-sm">Request Quote</a>
                    <a href="{{ route('products') }}" class="btn btn-ghost btn-sm">All Products</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
