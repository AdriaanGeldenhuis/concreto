@extends('layouts.app')
@section('title', 'Home')
@section('content')
    <section class="hero">
        @if(!empty($siteSettings['bg_image_desktop']))
            <img src="{{ url('media/' . $siteSettings['bg_image_desktop']) }}" alt="" class="hero-bg hero-bg--desktop">
            @if(!empty($siteSettings['bg_image_tablet']))
                <img src="{{ url('media/' . $siteSettings['bg_image_tablet']) }}" alt="" class="hero-bg hero-bg--tablet">
            @endif
            @if(!empty($siteSettings['bg_image_mobile']))
                <img src="{{ url('media/' . $siteSettings['bg_image_mobile']) }}" alt="" class="hero-bg hero-bg--mobile">
            @endif
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1>{{ $siteSettings['hero_title'] ?? 'Quality Building Materials, Delivered' }}</h1>
                <p>{{ $siteSettings['hero_subtitle'] ?? 'Sand, stone, and construction supplies delivered directly to your site. Order online and track your delivery in real-time.' }}</p>
                <div class="d-flex gap-1 justify-between" style="justify-content:center;">
                    <a href="{{ route('products') }}" class="btn btn-primary btn-lg">View Products</a>
                    <a href="{{ route('request-quote') }}" class="btn btn-outline btn-lg" style="border-color:#fff;color:#fff;">Get a Quote</a>
                </div>
            </div>
        @else
            <div class="container">
                <h1>{{ $siteSettings['hero_title'] ?? 'Quality Building Materials, Delivered' }}</h1>
                <p>{{ $siteSettings['hero_subtitle'] ?? 'Sand, stone, and construction supplies delivered directly to your site. Order online and track your delivery in real-time.' }}</p>
                <div class="d-flex gap-1 justify-between" style="justify-content:center;">
                    <a href="{{ route('products') }}" class="btn btn-primary btn-lg">View Products</a>
                    <a href="{{ route('request-quote') }}" class="btn btn-outline btn-lg" style="border-color:#fff;color:#fff;">Get a Quote</a>
                </div>
            </div>
        @endif
    </section>

    @if(!empty($siteSettings['delivery_info']))
    <section style="background:var(--primary);color:#fff;padding:1rem 0;text-align:center;">
        <div class="container">
            <p style="margin:0;font-weight:600;">{{ $siteSettings['delivery_info'] }}</p>
        </div>
    </section>
    @endif

    @if(!empty($siteSettings['homepage_promo']))
    <section style="background:var(--warning);color:#fff;padding:0.75rem 0;text-align:center;">
        <div class="container">
            <p style="margin:0;font-weight:600;">{{ $siteSettings['homepage_promo'] }}</p>
        </div>
    </section>
    @endif

    <section style="padding: 3rem 0; background: #fff;">
        <div class="container">
            <h2 class="section-title">Why Choose Concreto?</h2>
            <div class="feature-grid">
                <div class="feature-item">
                    <h3><span class="feature-icon">&#128666;</span> Fast Delivery</h3>
                    <p>Reliable delivery directly to your construction site, on time every time.</p>
                </div>
                <div class="feature-item">
                    <h3><span class="feature-icon">&#9989;</span> Quality Materials</h3>
                    <p>Premium sand, stone, and building supplies sourced from trusted suppliers.</p>
                </div>
                <div class="feature-item">
                    <h3><span class="feature-icon">&#128178;</span> Competitive Prices</h3>
                    <p>Fair pricing with no hidden costs. Get a quote today.</p>
                </div>
                <div class="feature-item">
                    <h3><span class="feature-icon">&#127942;</span> 15 Years of Experience</h3>
                    <p>Over 15 years in the building materials industry, serving thousands of satisfied customers.</p>
                </div>
            </div>
        </div>
    </section>

    <section style="padding: 3rem 0;">
        <div class="container">
            <h2 class="text-center mb-3">Our Products</h2>
            @if($featuredProducts->count())
            <div class="product-grid">
                @foreach($featuredProducts as $product)
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
            <div class="text-center mt-3">
                <a href="{{ route('products') }}" class="btn btn-secondary">View All Products</a>
            </div>
        </div>
    </section>

    @if($categories->count())
    <section style="padding: 2rem 0; background: #fff;">
        <div class="container">
            <h2 class="text-center mb-3">Categories</h2>
            <div class="product-grid">
                @foreach($categories as $category)
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ $category->name }}</h3>
                        <p class="text-muted text-small">{{ $category->description }}</p>
                        <a href="{{ route('products') }}?category={{ $category->slug }}" class="btn btn-outline btn-sm">Browse</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
@endsection
