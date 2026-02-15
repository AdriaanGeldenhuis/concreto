@extends('layouts.portal')
@section('title', 'Addresses')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="page-header">
        <h1>My Addresses</h1>
    </div>

    <div class="card">
        <div class="card-header">Add New Address</div>
        <div class="card-body">
            <form method="POST" action="{{ route('customer.addresses.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Label</label>
                        <input type="text" name="label" class="form-control"
                               placeholder="e.g. Site, Office, Warehouse"
                               value="{{ old('label') }}">
                        @error('label')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address Line 1</label>
                        <input type="text" name="line1" class="form-control" required
                               value="{{ old('line1') }}">
                        @error('line1')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" name="line2" class="form-control"
                               value="{{ old('line2') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" required
                               value="{{ old('city') }}">
                        @error('city')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Province</label>
                        <input type="text" name="province" class="form-control" required
                               value="{{ old('province') }}">
                        @error('province')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" class="form-control" required
                               value="{{ old('postal_code') }}">
                        @error('postal_code')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Address</button>
            </form>
        </div>
    </div>

    @if(count($addresses))
        <div class="page-header mt-2">
            <h2 class="text-muted" style="font-size:1.125rem;">Saved Addresses</h2>
        </div>

        @foreach($addresses as $address)
        <div class="card">
            <div class="card-body d-flex justify-between items-center flex-wrap gap-1">
                <div>
                    @if($address->label)
                        <strong>{{ $address->label }}</strong><br>
                    @endif
                    <span class="text-muted">{{ $address->full_address }}</span>
                </div>
                <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Remove this address?')">Remove</button>
                </form>
            </div>
        </div>
        @endforeach
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.account') }}" class="active"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
