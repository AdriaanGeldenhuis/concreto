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
            <a href="{{ route('products.show', $product) }}" class="product-card" style="color:inherit;">
                <div class="product-card-img">
                    @if($product->image_path)
                        <img src="/storage/{{ $product->image_path }}" alt="{{ $product->name }}">
                    @else
                        &#9881;
                    @endif
                </div>
                <div class="product-card-body">
                    <h3>{{ $product->name }}</h3>
                    <div class="price">R{{ number_format($product->price, 2) }}</div>
                    <div class="unit">per {{ $product->unit }}</div>
                    @if(!$product->in_stock)
                        <span class="badge badge-danger mt-1">Out of stock</span>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
        @endif
    @endforeach
</div>
@endsection
