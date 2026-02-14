@extends('layouts.app')
@section('title', $product->name)
@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem; max-width: 700px;">
    <div class="page-header">
        <div class="breadcrumb"><a href="{{ route('products') }}">Products</a> / {{ $product->name }}</div>
    </div>
    <div class="card">
        @if($product->image_path)
        <div style="height:250px;overflow:hidden;">
            <img src="/media/{{ $product->image_path }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;">
        </div>
        @endif
        <div class="card-body">
            <h1>{{ $product->name }}</h1>
            @if($product->category)
                <span class="badge badge-secondary">{{ $product->category->name }}</span>
            @endif
            <div style="font-size:1.5rem;font-weight:800;color:var(--primary);margin:1rem 0;">
                R{{ number_format($product->price, 2) }} <span class="text-muted text-small">per {{ $product->unit }}</span>
            </div>
            @if($product->description)
                <p>{{ $product->description }}</p>
            @endif
            @if($product->in_stock)
                <span class="badge badge-success">In Stock</span>
            @else
                <span class="badge badge-danger">Out of Stock</span>
            @endif
            @if($product->in_stock)
            <div class="mt-3">
                <form class="add-to-cart-form" data-product-id="{{ $product->id }}" style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                    <input type="number" name="qty" value="1" min="0.01" step="0.01" class="form-control" style="width:100px;text-align:center;">
                    <span class="text-muted">{{ $product->unit }}</span>
                    <button type="submit" class="btn btn-primary btn-lg btn-add-cart">Add to Cart</button>
                </form>
            </div>
            <div class="mt-2">
                <a href="{{ route('cart.index') }}" class="btn btn-outline btn-sm">View Cart</a>
                <a href="{{ route('request-quote') }}" class="btn btn-outline btn-sm">Request Quote</a>
            </div>
            @else
            <div class="mt-3">
                <span class="badge badge-danger" style="font-size:1rem;padding:0.5rem 1rem;">Out of Stock</span>
                <a href="{{ route('request-quote') }}" class="btn btn-outline mt-1">Request Quote</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
