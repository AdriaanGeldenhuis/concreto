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
                    <a href="{{ route('products') }}?category={{ $product->category->id }}">{{ $product->category->name }}</a>
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
                <div class="d-flex gap-1 flex-wrap mb-1" style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.5rem;">
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

                @if(isset($avgRating) && $avgRating > 0)
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem;">
                        <div style="color:var(--warning); font-size:1rem;">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($avgRating))
                                    &#9733;
                                @elseif($i - $avgRating < 1)
                                    &#9733;
                                @else
                                    &#9734;
                                @endif
                            @endfor
                        </div>
                        <span class="text-muted" style="font-size:0.875rem;">{{ number_format($avgRating, 1) }} ({{ $reviewCount ?? 0 }} {{ Str::plural('review', $reviewCount ?? 0) }})</span>
                    </div>
                @endif

                <div class="product-detail-price-row">
                    <div>
                        <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:0.25rem;">Price per {{ $product->unit }}</div>
                        <span class="product-detail-price">R {{ number_format($product->price, 2) }}</span>
                        <span style="font-size:0.875rem; color:var(--text-muted); margin-left:0.5rem;">excl VAT</span>
                    </div>
                </div>
                <div style="margin-bottom:1.5rem; padding:0.75rem 1rem; background:rgba(255,255,255,0.04); border-radius:var(--radius); border:1px solid var(--glass-border);">
                    <span class="text-muted" style="font-size:0.875rem;">Incl VAT: </span>
                    <span style="font-weight:700; color:var(--text);">R {{ number_format($product->price * 1.15, 2) }}</span>
                </div>

                @if($product->description)
                    <div class="product-detail-desc">
                        <p>{{ $product->description }}</p>
                    </div>
                @endif

                <!-- Bulk Pricing Tiers -->
                @if($product->bulkPricingTiers && $product->bulkPricingTiers->count() > 0)
                    <div style="margin-bottom:1.5rem;">
                        <h3 style="font-size:1.125rem; margin-bottom:0.75rem;">Bulk Pricing</h3>
                        <div class="table-responsive">
                            <table style="font-size:0.875rem;">
                                <thead>
                                    <tr>
                                        <th>Quantity</th>
                                        <th>Price per {{ $product->unit }}</th>
                                        <th>You Save</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->bulkPricingTiers as $tier)
                                        <tr>
                                            <td>{{ number_format($tier->min_qty) }}+ {{ Str::plural($product->unit, $tier->min_qty) }}</td>
                                            <td style="font-weight:700; color:var(--primary);">R {{ number_format($tier->price, 2) }} excl VAT</td>
                                            <td style="color:var(--success);">{{ number_format((($product->price - $tier->price) / $product->price) * 100, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="product-detail-specs">
                    <div class="spec-row">
                        <span class="spec-label">Unit</span>
                        <span class="spec-value">{{ ucfirst($product->unit) }}</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Price (excl. VAT)</span>
                        <span class="spec-value">R {{ number_format($product->price, 2) }}</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">VAT (15%)</span>
                        <span class="spec-value">R {{ number_format($product->price * 0.15, 2) }}</span>
                    </div>
                    <div class="spec-row spec-row--total">
                        <span class="spec-label">Total (incl. VAT)</span>
                        <span class="spec-value">R {{ number_format($product->price * 1.15, 2) }}</span>
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

        <!-- Related Products -->
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div style="margin-top:4rem;">
                <h2 style="font-size:1.5rem; margin-bottom:1.5rem; text-align:center;">Related Products</h2>
                <div class="product-grid">
                    @foreach($relatedProducts as $relatedProduct)
                        <a href="{{ route('products.show', $relatedProduct) }}" class="product-card">
                            <div class="product-card-img">
                                @if($relatedProduct->image_path)
                                    <img src="/media/{{ $relatedProduct->image_path }}" alt="{{ $relatedProduct->name }}">
                                @else
                                    <span class="product-card-placeholder">&#9881;</span>
                                @endif
                            </div>
                            <div class="product-card-body">
                                <h3>{{ $relatedProduct->name }}</h3>
                                <p class="product-card-meta">
                                    @if($relatedProduct->category)
                                        {{ $relatedProduct->category->name }}
                                        <span class="meta-dot">&middot;</span>
                                    @endif
                                    per {{ $relatedProduct->unit }}
                                </p>
                                <div class="product-card-divider"></div>
                                <div class="product-card-price">R {{ number_format($relatedProduct->price, 2) }} excl VAT</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Reviews Section -->
        <div style="margin-top:4rem;">
            <h2 style="font-size:1.5rem; margin-bottom:1.5rem;">Customer Reviews</h2>

            @if($product->approvedReviews && $product->approvedReviews->count() > 0)
                <div style="margin-bottom:2rem;">
                    @foreach($product->approvedReviews as $review)
                        <div class="card" style="margin-bottom:1rem;">
                            <div class="card-body">
                                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:0.75rem;">
                                    <div>
                                        <div style="font-weight:700; margin-bottom:0.25rem;">{{ $review->customer->user->name ?? 'Anonymous' }}</div>
                                        <div style="color:var(--warning); font-size:0.875rem;">
                                            @for($i = 1; $i <= 5; $i++)
                                                {{ $i <= $review->rating ? '&#9733;' : '&#9734;' }}
                                            @endfor
                                        </div>
                                    </div>
                                    <span class="text-muted" style="font-size:0.8125rem;">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                @if($review->comment)
                                    <p style="color:var(--text-light); margin:0;">{{ $review->comment }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">
                    <p>No reviews yet. Be the first to review this product!</p>
                </div>
            @endif

            @auth
                @if(auth()->user()->isCustomer())
                    <div class="card">
                        <div class="card-header">Write a Review</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('customer.reviews.store') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <div class="form-group">
                                    <label class="form-label">Rating</label>
                                    <div style="display:flex; gap:0.5rem; font-size:1.5rem;">
                                        @for($i = 1; $i <= 5; $i++)
                                            <label style="cursor:pointer; color:var(--text-muted); transition:color 0.2s;">
                                                <input type="radio" name="rating" value="{{ $i }}" style="display:none;" required>
                                                <span class="star-rating">&#9734;</span>
                                            </label>
                                        @endfor
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Your Review (optional)</label>
                                    <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience with this product..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">Submit Review</button>
                            </form>
                        </div>
                    </div>
                @endif
            @else
                <div style="text-align:center; padding:2rem; background:rgba(255,255,255,0.03); border-radius:var(--radius); border:1px solid var(--glass-border);">
                    <p class="text-muted" style="margin-bottom:1rem;">Please log in to write a review</p>
                    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                </div>
            @endauth
        </div>
    </div>
</div>

@push('styles')
<style>
@@media (min-width: 992px) {
    .product-detail { grid-template-columns: 1fr 1fr; }
    .product-grid { grid-template-columns: repeat(3, 1fr); }
}
.star-rating { transition: color 0.2s; }
input[type="radio"]:checked ~ .star-rating,
label:hover .star-rating { color: var(--warning) !important; }
</style>
@endpush

@push('scripts')
<script>
// Star rating interaction
document.querySelectorAll('label:has(input[name="rating"])').forEach((label, index) => {
    label.addEventListener('mouseover', () => {
        document.querySelectorAll('.star-rating').forEach((star, i) => {
            star.textContent = i <= index ? '★' : '☆';
            star.style.color = i <= index ? 'var(--warning)' : 'var(--text-muted)';
        });
    });

    label.addEventListener('click', () => {
        document.querySelectorAll('.star-rating').forEach((star, i) => {
            star.textContent = i <= index ? '★' : '☆';
            star.style.color = i <= index ? 'var(--warning)' : 'var(--text-muted)';
        });
    });
});

// Reset stars on mouse leave
document.querySelector('div:has(input[name="rating"])')?.addEventListener('mouseleave', () => {
    const checked = document.querySelector('input[name="rating"]:checked');
    const checkedIndex = checked ? parseInt(checked.value) - 1 : -1;
    document.querySelectorAll('.star-rating').forEach((star, i) => {
        star.textContent = i <= checkedIndex ? '★' : '☆';
        star.style.color = i <= checkedIndex ? 'var(--warning)' : 'var(--text-muted)';
    });
});
</script>
@endpush
@endsection
