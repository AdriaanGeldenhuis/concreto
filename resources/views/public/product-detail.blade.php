@extends('layouts.app')
@section('title', $product->name)
@section('content')

<div class="section">
    <div class="container" style="max-width: 900px;">
        <div class="page-header">
            <div class="breadcrumb">
                <a href="{{ route('home') }}">Home</a> /
                <a href="{{ route('products') }}">Products</a>
                @if($product->category)
                    / <a href="{{ route('products') }}">{{ $product->category->name }}</a>
                @endif
                / {{ $product->name }}
            </div>
        </div>

        <div class="card">
            @if($product->image_path)
                <div class="product-detail-img overflow-hidden" style="max-height: 400px;">
                    <img src="/storage/{{ $product->image_path }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;">
                </div>
            @endif
            <div class="card-body p-3">
                <div class="d-flex items-center gap-1 flex-wrap mb-2">
                    @if($product->category)
                        <span class="badge badge-secondary">{{ $product->category->name }}</span>
                    @endif
                    @if($product->in_stock)
                        <span class="badge badge-success">In Stock</span>
                    @else
                        <span class="badge badge-danger">Out of Stock</span>
                    @endif
                </div>

                <h1 class="mb-1">{{ $product->name }}</h1>

                <div class="d-flex items-center gap-1 flex-wrap mb-3">
                    <span class="text-xl font-bold text-primary" style="font-size: 2rem; font-weight: 800;">R{{ number_format($product->price, 2) }}</span>
                    <span class="text-muted text-lg">per {{ $product->unit }}</span>
                </div>

                @if($product->description)
                    <div class="divider"></div>
                    <h4 class="mb-1">Description</h4>
                    <p class="text-muted">{{ $product->description }}</p>
                @endif

                <div class="divider"></div>

                <div class="info-grid mb-3">
                    <div class="info-row">
                        <span class="label">Unit</span>
                        <span class="value">{{ $product->unit }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Price</span>
                        <span class="value">R{{ number_format($product->price, 2) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Availability</span>
                        <span class="value">{{ $product->in_stock ? 'In Stock' : 'Out of Stock' }}</span>
                    </div>
                    @if($product->category)
                        <div class="info-row">
                            <span class="label">Category</span>
                            <span class="value">{{ $product->category->name }}</span>
                        </div>
                    @endif
                </div>

                <div class="d-flex gap-1 flex-wrap">
                    @auth
                        <a href="{{ route('customer.orders.create') }}" class="btn btn-primary btn-lg">Order Now</a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Register to Order</a>
                    @endauth
                    <a href="{{ route('request-quote') }}" class="btn btn-outline btn-lg">Request Quote</a>
                    <a href="{{ route('products') }}" class="btn btn-ghost btn-lg">Back to Products</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
