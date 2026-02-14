@extends('layouts.portal')
@section('title', 'Place Order')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}" class="active">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('customer.orders.index') }}">Orders</a> / New Order
        </div>
        <h1>Place New Order</h1>
    </div>

    <form method="POST" action="{{ route('customer.orders.store') }}">
        @csrf

        <div class="card">
            <div class="card-header">Delivery Details</div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Delivery Address</label>
                    <select name="delivery_address_id" class="form-control" required>
                        <option value="">Select an address...</option>
                        @foreach($addresses as $addr)
                            <option value="{{ $addr->id }}" {{ $addr->id == $customer->default_address_id ? 'selected' : '' }}>
                                {{ $addr->label ? $addr->label . ' - ' : '' }}{{ $addr->full_address }}
                            </option>
                        @endforeach
                    </select>
                    @error('delivery_address_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <p class="text-small text-muted mt-1">
                        <a href="{{ route('customer.addresses.index') }}">+ Add a new address</a>
                    </p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Preferred Delivery Date</label>
                        <input type="date" name="scheduled_date" class="form-control"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               value="{{ old('scheduled_date') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time Window</label>
                        <select name="scheduled_time_window" class="form-control">
                            <option value="">Any time</option>
                            <option value="07:00-09:00" {{ old('scheduled_time_window') == '07:00-09:00' ? 'selected' : '' }}>07:00 - 09:00</option>
                            <option value="09:00-12:00" {{ old('scheduled_time_window') == '09:00-12:00' ? 'selected' : '' }}>09:00 - 12:00</option>
                            <option value="12:00-15:00" {{ old('scheduled_time_window') == '12:00-15:00' ? 'selected' : '' }}>12:00 - 15:00</option>
                            <option value="15:00-17:00" {{ old('scheduled_time_window') == '15:00-17:00' ? 'selected' : '' }}>15:00 - 17:00</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Special Instructions</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Gate codes, site contact, delivery notes...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-between items-center">
                <span>Order Items</span>
                <button type="button" id="add-item-btn" class="btn btn-primary btn-sm">+ Add Item</button>
            </div>
            <div class="card-body">
                <div id="product-select" data-products='@json($products)' class="d-none"></div>
                <div id="order-items">
                    @error('items')
                        <div class="form-error mb-2">{{ $message }}</div>
                    @enderror
                </div>
                <div id="order-total" class="font-bold mt-2 text-primary" style="font-size:1.125rem;"></div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">Place Order</button>
    </form>
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}" class="active"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.addresses.index') }}"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
