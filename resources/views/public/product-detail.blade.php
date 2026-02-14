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
            <img src="/storage/{{ $product->image_path }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;">
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
            <div class="mt-3">
                @auth
                    <a href="{{ route('customer.orders.create') }}" class="btn btn-primary">Order Now</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary">Register to Order</a>
                @endauth
                <a href="{{ route('request-quote') }}" class="btn btn-outline">Request Quote</a>
            </div>
        </div>
    </div>
</div>
@endsection
