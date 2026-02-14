@extends('layouts.app')
@section('title', 'Request a Quote')
@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem; max-width: 600px;">
    <div class="page-header">
        <h1>Request a Quote</h1>
        <p class="text-muted">Tell us what you need and we'll get back to you with a competitive quote.</p>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('request-quote.submit') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone ?? '') }}" required>
                        @error('phone')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                    @error('email')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                @if($products->count())
                <div class="form-group">
                    <label class="form-label">Select Products (optional - or describe below)</label>
                    <div class="quote-product-list">
                        @foreach($products as $product)
                        <label class="quote-product-item">
                            <input type="checkbox" class="quote-product-check" data-name="{{ $product->name }}" data-unit="{{ $product->unit }}">
                            <span class="quote-product-name">{{ $product->name }}</span>
                            <span class="text-muted text-small">R{{ number_format($product->price, 2) }}/{{ $product->unit }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label">Products & Quantities Needed</label>
                    <textarea name="products_needed" id="products-needed-field" class="form-control" rows="4" placeholder="E.g. 10 tons river sand, 5 tons 19mm stone..." required>{{ old('products_needed') }}</textarea>
                    @error('products_needed')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Delivery Address</label>
                    <input type="text" name="delivery_address" class="form-control" value="{{ old('delivery_address') }}" placeholder="Full delivery address or area" required>
                    @error('delivery_address')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Submit Quote Request</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // When product checkboxes are toggled, update the textarea
    document.querySelectorAll('.quote-product-check').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var field = document.getElementById('products-needed-field');
            var current = field.value.trim();
            var name = cb.dataset.name;
            var unit = cb.dataset.unit;
            if (cb.checked) {
                var entry = name + ' - [qty] ' + unit;
                field.value = current ? current + '\n' + entry : entry;
            }
        });
    });
});
</script>
@endpush
