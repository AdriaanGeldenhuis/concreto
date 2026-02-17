@extends('layouts.app')
@section('title', 'Home')
@section('content')
    <section class="hero">
        @if(!empty($siteSettings['bg_image_desktop']))
            <img src="{{ url('media/' . $siteSettings['bg_image_desktop']) }}" alt="" class="hero-bg hero-bg--desktop">
        @endif
        @if(!empty($siteSettings['bg_image_tablet']))
            <img src="{{ url('media/' . $siteSettings['bg_image_tablet']) }}" alt="" class="hero-bg hero-bg--tablet">
        @elseif(!empty($siteSettings['bg_image_desktop']))
            <img src="{{ url('media/' . $siteSettings['bg_image_desktop']) }}" alt="" class="hero-bg hero-bg--tablet">
        @endif
        @if(!empty($siteSettings['bg_image_mobile']))
            <img src="{{ url('media/' . $siteSettings['bg_image_mobile']) }}" alt="" class="hero-bg hero-bg--mobile">
        @elseif(!empty($siteSettings['bg_image_desktop']))
            <img src="{{ url('media/' . $siteSettings['bg_image_desktop']) }}" alt="" class="hero-bg hero-bg--mobile">
        @endif
        @if(!empty($siteSettings['bg_image_desktop']))
            <div class="hero-overlay"></div>
        @endif
        <div class="{{ !empty($siteSettings['bg_image_desktop']) ? 'hero-content' : 'container' }}">
            <h1>{{ $siteSettings['hero_title'] ?? 'Quality Building Materials, Delivered' }}</h1>
            <p>{{ $siteSettings['hero_subtitle'] ?? 'Sand, stone, and construction supplies delivered directly to your site. Order online and track your delivery in real-time.' }}</p>
            <div class="d-flex gap-2 justify-center flex-wrap">
                <a href="{{ route('products') }}" class="btn btn-primary btn-xl">View Products</a>
                <a href="{{ route('request-quote') }}" class="btn btn-outline-white btn-xl">Get a Quote</a>
            </div>
        </div>
    </section>

    @if(!empty($siteSettings['delivery_info']))
    <section class="info-banner info-banner--primary">
        <div class="container">
            <p>{{ $siteSettings['delivery_info'] }}</p>
        </div>
    </section>
    @endif

    @if(!empty($siteSettings['homepage_promo']))
    <section class="info-banner info-banner--warning">
        <div class="container">
            <p>{{ $siteSettings['homepage_promo'] }}</p>
        </div>
    </section>
    @endif

    <section class="section">
        <div class="container">
            <h2 class="section-title">Our Products</h2>
            @if($featuredProducts->count())
            <div class="product-grid">
                @foreach($featuredProducts as $product)
                <a href="{{ route('products.show', $product) }}" class="product-card">
                    <div class="product-card-img">
                        @if($product->image_path)
                            <img src="/media/{{ $product->image_path }}" alt="{{ $product->name }}">
                        @else
                            <span class="product-card-placeholder">&#9881;</span>
                        @endif
                    </div>
                    <div class="product-card-body">
                        <h3>{{ $product->name }}</h3>
                        <p class="product-card-meta">
                            @if($product->category){{ $product->category->name }}@endif
                            <span class="meta-dot">&middot;</span>
                            per {{ $product->unit }}
                        </p>
                        <div class="product-card-divider"></div>
                        <div class="product-card-price">R {{ number_format($product->price, 2) }}</div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <div class="icon">&#9881;</div>
                <h3>Products coming soon</h3>
                <p>We're setting up our product catalog. Check back soon or request a quote.</p>
            </div>
            @endif
            <div class="text-center mt-4">
                <a href="{{ route('products') }}" class="btn btn-secondary btn-lg">View All Products</a>
            </div>
        </div>
    </section>

    @if($categories->count())
    <section class="section section--alt">
        <div class="container">
            <h2 class="section-title">Categories</h2>
            <div class="product-grid">
                @foreach($categories as $category)
                <a href="{{ route('products') }}?category={{ $category->slug }}" class="card" style="color:inherit;text-decoration:none;">
                    <div class="card-body text-center">
                        <h3>{{ $category->name }}</h3>
                        <p class="text-muted text-small">{{ $category->description }}</p>
                        <span class="btn btn-outline btn-sm">Browse</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="section">
        <div class="container">
            <h2 class="section-title">Why Choose Us?</h2>
            <div class="why-choose-grid">
                <div class="why-choose-item">
                    <div class="why-choose-circle"><img src="/assets/WhyChooseUs deliveryImage.webp" alt="Fast Delivery"></div>
                    <h3>Fast Delivery</h3>
                    <p>Same-day and next-day delivery. Track your driver in real-time.</p>
                </div>
                <div class="why-choose-item">
                    <div class="why-choose-circle"><img src="/assets/whyChooseUs FairPricing.webp" alt="Fair Pricing"></div>
                    <h3>Fair Pricing</h3>
                    <p>Competitive prices with no hidden fees.</p>
                </div>
                <div class="why-choose-item">
                    <div class="why-choose-circle"><img src="/assets/WhyChooseUs QualityMaterials.webp" alt="Quality Materials"></div>
                    <h3>Quality Materials</h3>
                    <p>Premium sand, stone, and building supplies.</p>
                </div>
                <div class="why-choose-item">
                    <div class="why-choose-circle"><img src="/assets/WhyChooseUs ExpertSupport.webp" alt="Expert Support"></div>
                    <h3>Expert Support</h3>
                    <p>Dedicated team for orders, quotes, and advice.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
