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

    <section class="section why-choose-section">
        <div class="container">
            <div class="why-choose-header">
                <span class="why-choose-badge">Why Us</span>
                <h2 class="section-title">Built Different</h2>
                <p class="why-choose-subtitle">Four reasons contractors and homeowners trust Concreto for every project.</p>
            </div>
            <div class="why-choose-grid">
                <div class="why-choose-item" style="--i:0">
                    <div class="why-choose-card">
                        <div class="why-choose-icon-wrap">
                            <div class="why-choose-circle">
                                {{-- Truck / delivery icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                            </div>
                            <span class="why-choose-number">01</span>
                        </div>
                        <h3>Fast Delivery</h3>
                        <p>Same-day and next-day delivery. Track your driver in real-time.</p>
                        <div class="why-choose-shine"></div>
                    </div>
                </div>
                <div class="why-choose-item" style="--i:1">
                    <div class="why-choose-card">
                        <div class="why-choose-icon-wrap">
                            <div class="why-choose-circle">
                                {{-- Price tag icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            </div>
                            <span class="why-choose-number">02</span>
                        </div>
                        <h3>Fair Pricing</h3>
                        <p>Competitive prices with no hidden fees.</p>
                        <div class="why-choose-shine"></div>
                    </div>
                </div>
                <div class="why-choose-item" style="--i:2">
                    <div class="why-choose-card">
                        <div class="why-choose-icon-wrap">
                            <div class="why-choose-circle">
                                {{-- Shield / quality icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                            </div>
                            <span class="why-choose-number">03</span>
                        </div>
                        <h3>Quality Materials</h3>
                        <p>Premium sand, stone, and building supplies.</p>
                        <div class="why-choose-shine"></div>
                    </div>
                </div>
                <div class="why-choose-item" style="--i:3">
                    <div class="why-choose-card">
                        <div class="why-choose-icon-wrap">
                            <div class="why-choose-circle">
                                {{-- Headset / support icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                            </div>
                            <span class="why-choose-number">04</span>
                        </div>
                        <h3>Expert Support</h3>
                        <p>Dedicated team for orders, quotes, and advice.</p>
                        <div class="why-choose-shine"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
