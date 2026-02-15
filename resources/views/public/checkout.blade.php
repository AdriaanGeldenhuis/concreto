@extends('layouts.app')
@section('title', 'Checkout')
@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem; max-width: 800px;">
    <div class="page-header">
        <div class="breadcrumb"><a href="{{ route('cart.index') }}">Order</a> / Checkout</div>
        <h1>Checkout</h1>
    </div>

    <form method="POST" action="{{ route('checkout.place') }}">
        @csrf

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
                    <p class="text-small text-muted"><a href="{{ route('customer.addresses.index') }}">Manage addresses</a></p>
                @else
                    <div class="alert alert-warning">
                        You need to add a delivery address before you can place an order.
                        <a href="{{ route('customer.addresses.index') }}" class="font-bold">Add Address</a>
                    </div>
                @endif
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
                        <span>Subtotal</span>
                        <span>R{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="cart-totals-row">
                        <span>VAT (15%)</span>
                        <span>R{{ number_format($vat, 2) }}</span>
                    </div>
                    <div class="cart-totals-row cart-totals-total">
                        <span>Total</span>
                        <span>R{{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block" {{ $addresses->count() ? '' : 'disabled' }}>Place Order</button>
        <p class="text-center text-muted text-small mt-2">By placing your order you agree to our <a href="{{ route('terms') }}">Terms & Conditions</a></p>
    </form>
</div>
@endsection
