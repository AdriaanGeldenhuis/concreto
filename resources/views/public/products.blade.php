@extends('layouts.app')
@section('title', 'Products')
@section('content')

<div class="section section--alt">
    <div class="container">
        <h1 class="section-title">Our Products</h1>
        <p class="section-subtitle">Browse our range of quality building materials</p>

        @php
            $allCategories = $categories->filter(fn($c) => $c->products->count() > 0);
        @endphp

        @if($allCategories->count() > 1)
            <div class="d-flex gap-1 flex-wrap justify-center mb-4" id="category-filters">
                <button class="btn btn-primary btn-sm category-filter active" data-category="all">All Products</button>
                @foreach($allCategories as $category)
                    <button class="btn btn-ghost btn-sm category-filter" data-category="cat-{{ $category->id }}">{{ $category->name }}</button>
                @endforeach
            </div>
        @endif

        @if($allCategories->isEmpty())
            <div class="empty-state">
                <div class="icon">&#128722;</div>
                <h3>No Products Available</h3>
                <p>We are currently updating our product catalog. Please check back soon or contact us for information.</p>
                <a href="{{ route('contact') }}" class="btn btn-primary">Contact Us</a>
            </div>
        @else
            @foreach($allCategories as $category)
                <div class="category-section mb-4" data-category-id="cat-{{ $category->id }}">
                    <h2 class="mb-1">{{ $category->name }}</h2>
                    @if($category->description)
                        <p class="text-muted mb-2">{{ $category->description }}</p>
                    @endif
                    <div class="product-grid">
                        @foreach($category->products as $product)
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
                                        {{ $category->name }}
                                        <span class="meta-dot">&middot;</span>
                                        per {{ $product->unit }}
                                    </p>
                                    <div class="product-card-divider"></div>
                                    <div class="product-card-price">R {{ number_format($product->price, 2) }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<div class="section section--dark">
    <div class="container text-center">
        <h2 class="section-title text-white">Need a Custom Quote?</h2>
        <p class="section-subtitle text-white" style="opacity:0.8;">Get competitive pricing for bulk orders and deliveries</p>
        <a href="{{ route('request-quote') }}" class="btn btn-primary btn-lg">Request a Quote</a>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.category-filter').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.category-filter').forEach(function(b) {
            b.classList.remove('active');
            b.classList.remove('btn-primary');
            b.classList.add('btn-ghost');
        });
        this.classList.add('active');
        this.classList.remove('btn-ghost');
        this.classList.add('btn-primary');

        var cat = this.getAttribute('data-category');
        document.querySelectorAll('.category-section').forEach(function(section) {
            if (cat === 'all' || section.getAttribute('data-category-id') === cat) {
                section.style.display = '';
            } else {
                section.style.display = 'none';
            }
        });
    });
});
</script>
@endpush
@endsection
