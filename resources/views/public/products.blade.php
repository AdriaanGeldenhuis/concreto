@extends('layouts.app')
@section('title', 'Products')
@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">
    <div class="page-header">
        <h1>Our Products</h1>
        <p class="text-muted">Browse our range of quality building materials</p>
    </div>

    @foreach($categories as $category)
        @if($category->products->count())
        <h2 class="mt-3 mb-2">{{ $category->name }}</h2>
        @if($category->description)
            <p class="text-muted mb-2">{{ $category->description }}</p>
        @endif
        <div class="product-grid mb-3">
            @foreach($category->products as $product)
            <div class="product-card">
                <a href="{{ route('products.show', $product) }}" style="color:inherit;text-decoration:none;">
                    <div class="product-card-img">
                        @if($product->image_path)
                            <img src="/media/{{ $product->image_path }}" alt="{{ $product->name }}">
                        @else
                            &#9881;
                        @endif
                    </div>
                    <div class="product-card-body">
                        <h3>{{ $product->name }}</h3>
                        <div class="price">R{{ number_format($product->price, 2) }}</div>
                        <div class="unit">per {{ $product->unit }}</div>
                    </div>
                </a>
                <div class="product-card-actions">
                    @if($product->in_stock)
                    <form class="add-to-cart-form" data-product-id="{{ $product->id }}">
                        <input type="number" name="qty" value="1" min="0.01" step="0.01" class="form-control cart-qty-sm">
                        <button type="submit" class="btn btn-primary btn-sm btn-add-cart">Add to Cart</button>
                    </form>
                    @else
                        <span class="badge badge-danger">Out of stock</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    @endforeach
</div>
@endsection
