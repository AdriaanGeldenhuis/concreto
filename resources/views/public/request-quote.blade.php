@extends('layouts.app')
@section('title', 'Request a Quote')
@section('content')

<div class="hero">
    <div class="container">
        <h1>Request a Quote</h1>
        <p>Tell us what you need and we will get back to you with a competitive quote tailored to your project.</p>
    </div>
</div>

<div class="section">
    <div class="container" style="max-width: 700px;">
        <div class="card">
            <div class="card-header">
                <span>Quote Request Form</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('request-quote.submit') }}">
                    @csrf

                    <h4 class="mb-2">Your Details</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Your full name" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                            @error('name')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company" class="form-control" placeholder="Company name (optional)" value="{{ old('company') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="you@example.com" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                            @error('email')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="Your phone number" value="{{ old('phone', auth()->user()->phone ?? '') }}" required>
                            @error('phone')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="divider"></div>

                    <h4 class="mb-2">Order Details</h4>

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
                        <label class="form-label">Products / Materials Needed</label>
                        <textarea name="products_needed" id="products-needed-field" class="form-control" rows="4" placeholder="Please list all the products you need, including quantities. For example:&#10;- 10 tons river sand&#10;- 5 tons 19mm stone&#10;- 20 bags cement" required>{{ old('products_needed') }}</textarea>
                        <span class="form-hint">Include product names and estimated quantities for the most accurate quote.</span>
                        @error('products_needed')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Delivery Address</label>
                            <input type="text" name="delivery_address" class="form-control" placeholder="Full delivery address" value="{{ old('delivery_address') }}" required>
                            @error('delivery_address')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Preferred Delivery Date</label>
                            <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any special requirements, site access details, or other information...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="divider"></div>

                    {{-- Honeypot anti-spam --}}
                    <div style="position:absolute;left:-9999px;" aria-hidden="true">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Submit Quote Request</button>
                    <p class="text-center text-muted text-small mt-2">We typically respond within 24 hours on business days.</p>
                </form>
            </div>
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
