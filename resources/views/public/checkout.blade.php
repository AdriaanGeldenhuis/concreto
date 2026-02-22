@extends('layouts.app')
@section('title', 'Checkout')
@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem; max-width: 800px;">
    <div class="page-header">
        <div class="breadcrumb"><a href="{{ route('cart.index') }}">Order</a> / Checkout</div>
        <h1>Checkout</h1>
    </div>

    {{-- Separate forms (outside the main order form) --}}
    <form id="address-form" method="POST" action="{{ route('checkout.add-address') }}" style="display:none;">@csrf</form>
    <form id="company-form" method="POST" action="{{ route('checkout.save-company') }}" style="display:none;">@csrf</form>

    <form method="POST" action="{{ route('checkout.place') }}">
        @csrf

        {{-- Company Details (show if no company linked yet) --}}
        @if(!$customer->company)
        <div class="card" style="border-left:3px solid var(--warning, #f0ad4e);">
            <div class="card-header">Company / Business Details</div>
            <div class="card-body">
                <p class="text-muted text-small" style="margin-bottom:0.75rem;">Your company details will appear on invoices and quotes.</p>
                <div class="form-group">
                    <label class="form-label">Company / Business Name *</label>
                    <input type="text" form="company-form" name="company_name" class="form-control" required
                           value="{{ old('company_name') }}" placeholder="Acme Construction (Pty) Ltd">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">VAT Number</label>
                        <input type="text" form="company-form" name="vat_number" class="form-control"
                               value="{{ old('vat_number') }}" placeholder="4123456789">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Registration Number</label>
                        <input type="text" form="company-form" name="registration_number" class="form-control"
                               value="{{ old('registration_number') }}" placeholder="2024/123456/07">
                    </div>
                </div>
                <button type="submit" form="company-form" class="btn btn-primary btn-sm">Save Company Details</button>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-header">Company</div>
            <div class="card-body" style="padding:0.75rem 1rem;">
                <strong>{{ $customer->company->display_name }}</strong>
                @if($customer->company->vat_number)
                    <span class="text-muted text-small" style="margin-left:0.5rem;">VAT: {{ $customer->company->vat_number }}</span>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">Delivery Address</div>
            <div class="card-body">
                @if($addresses->count())
                    <div class="form-group">
                        <select name="delivery_address_id" class="form-control" required>
                            <option value="">Select delivery address</option>
                            @foreach($addresses as $addr)
                                <option value="{{ $addr->id }}" {{ $addr->id == $customer->default_address_id ? 'selected' : '' }}>
                                    {{ $addr->label ? $addr->label . ' - ' : '' }}{{ $addr->full_address }}
                                </option>
                            @endforeach
                        </select>
                        @error('delivery_address_id')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('new-address-section').classList.toggle('d-none')">+ Add New Address</button>
                @else
                    <div class="alert alert-warning" style="margin-bottom:1rem;">
                        You need to add a delivery address before you can place an order.
                    </div>
                @endif

                {{-- Inline address form --}}
                <div id="new-address-section" class="{{ $addresses->count() ? 'd-none' : '' }}" style="margin-top:1rem;">
                    <div style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:var(--radius, 8px); padding:1rem;">
                        <h4 style="margin-bottom:0.75rem;">Add Delivery Address</h4>
                        <div class="form-group">
                            <label class="form-label">Search Address</label>
                            <input type="text" id="checkout-address-search" class="form-control" placeholder="Start typing an address..." autocomplete="off">
                            <small class="text-muted">Type to search. Fields below will auto-fill.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Label (optional)</label>
                            <input type="text" form="address-form" name="label" class="form-control" placeholder="e.g. Site, Home, Office">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Street Address *</label>
                            <input type="text" form="address-form" name="line1" id="checkout-line1" class="form-control" placeholder="123 Main Road" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" form="address-form" name="line2" id="checkout-line2" class="form-control" placeholder="Unit, Suite, etc.">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">City *</label>
                                <input type="text" form="address-form" name="city" id="checkout-city" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Province *</label>
                                <select form="address-form" name="province" id="checkout-province" class="form-control" required>
                                    <option value="">Select</option>
                                    <option value="Gauteng">Gauteng</option>
                                    <option value="Western Cape">Western Cape</option>
                                    <option value="KwaZulu-Natal">KwaZulu-Natal</option>
                                    <option value="Eastern Cape">Eastern Cape</option>
                                    <option value="Free State">Free State</option>
                                    <option value="Limpopo">Limpopo</option>
                                    <option value="Mpumalanga">Mpumalanga</option>
                                    <option value="North West">North West</option>
                                    <option value="Northern Cape">Northern Cape</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Postal Code *</label>
                            <input type="text" form="address-form" name="postal_code" id="checkout-postal" class="form-control" style="max-width:150px;" maxlength="10" required>
                        </div>
                        <input type="hidden" form="address-form" name="gps_lat" id="checkout-lat">
                        <input type="hidden" form="address-form" name="gps_lng" id="checkout-lng">
                        <button type="submit" form="address-form" class="btn btn-primary btn-sm">Save Address</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Delivery Schedule</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Preferred Date</label>
                        <input type="date" name="scheduled_date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time Window</label>
                        <select name="scheduled_time_window" class="form-control">
                            <option value="">Any time</option>
                            <option value="07:00-09:00">07:00 - 09:00</option>
                            <option value="09:00-12:00">09:00 - 12:00</option>
                            <option value="12:00-15:00">12:00 - 15:00</option>
                            <option value="15:00-17:00">15:00 - 17:00</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Special delivery instructions..."></textarea>
                </div>
            </div>
        </div>

        {{-- Promo Code --}}
        <div class="card">
            <div class="card-header">Promo Code</div>
            <div class="card-body">
                @if(!empty($promoCode))
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                        <div>
                            <span class="badge badge-success" style="font-size:0.9rem;">{{ $promoCode->code }}</span>
                            <span class="text-muted" style="margin-left:0.5rem;">
                                -R{{ number_format($discount, 2) }} discount applied
                                @if($promoCode->type === 'percentage')
                                    ({{ $promoCode->value }}% off)
                                @endif
                            </span>
                        </div>
                        <form method="POST" action="{{ route('checkout.remove-promo') }}" style="margin:0;">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm">Remove</button>
                        </form>
                    </div>
                @else
                    <div style="display:flex; gap:0.5rem;">
                        <input type="text" id="promo-input" class="form-control" placeholder="Enter promo code" style="flex:1;">
                        <button type="button" class="btn btn-outline" onclick="applyPromo()">Apply</button>
                    </div>
                    @if(!empty($promoError))
                        <div class="form-error" style="margin-top:0.5rem;">{{ $promoError }}</div>
                    @endif
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">Order Summary</div>
            <div class="card-body" style="padding:0;">
                @foreach($items as $item)
                <div class="checkout-item">
                    <div class="checkout-item-name">
                        {{ $item['product']->name }}
                        <span class="text-muted text-small">&times; {{ $item['qty'] }} {{ $item['product']->unit }}</span>
                    </div>
                    <div class="checkout-item-price">R{{ number_format($item['line_total'], 2) }}</div>
                </div>
                @endforeach
            </div>
            <div class="card-footer">
                <div class="cart-totals">
                    <div class="cart-totals-row">
                        <span>Subtotal (excl VAT)</span>
                        <span>R{{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if($discount > 0)
                    <div class="cart-totals-row" style="color: var(--success);">
                        <span>Discount</span>
                        <span>-R{{ number_format($discount, 2) }}</span>
                    </div>
                    @endif
                    <div class="cart-totals-row">
                        <span>VAT (15%)</span>
                        <span>R{{ number_format($vat, 2) }}</span>
                    </div>
                    <div class="cart-totals-row cart-totals-total">
                        <span>Total (incl VAT)</span>
                        <span>R{{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block" {{ $addresses->count() ? '' : 'disabled' }} id="place-order-btn">Place Order</button>
        <p class="text-center text-muted text-small mt-2">By placing your order you agree to our <a href="{{ route('terms') }}">Terms & Conditions</a></p>
    </form>
</div>

@include('partials.address-autocomplete')
@push('scripts')
<script>
function applyPromo() {
    var code = document.getElementById('promo-input').value.trim();
    if (!code) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("checkout.apply-promo") }}';
    var csrf = document.createElement('input');
    csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
    var input = document.createElement('input');
    input.type = 'hidden'; input.name = 'promo_code'; input.value = code;
    form.appendChild(csrf); form.appendChild(input);
    document.body.appendChild(form); form.submit();
}
document.addEventListener('DOMContentLoaded', function() {
    var promoInput = document.getElementById('promo-input');
    if (promoInput) {
        promoInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); applyPromo(); }
        });
    }

    // Address autocomplete for checkout
    if (typeof initMapboxAutocomplete === 'function') {
        initMapboxAutocomplete('checkout-address-search', {
            line1: 'checkout-line1',
            line2: 'checkout-line2',
            city: 'checkout-city',
            province: 'checkout-province',
            postal: 'checkout-postal',
            lat: 'checkout-lat',
            lng: 'checkout-lng'
        });
    }

    // Prevent double-submit on place order
    var orderForm = document.querySelector('form[action="{{ route("checkout.place") }}"]');
    if (orderForm) {
        orderForm.addEventListener('submit', function() {
            var btn = document.getElementById('place-order-btn');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Placing Order...';
            }
        });
    }
});
</script>
@endpush
@endsection
