@extends('layouts.portal')
@section('title', 'My Account')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="page-header">
        <h1>My Account</h1>
    </div>

    {{-- Company Details --}}
    <div class="card">
        <div class="card-header">Company / Business Details</div>
        <div class="card-body">
            <form method="POST" action="{{ route('customer.account.update-company') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Company / Business Name *</label>
                        <input type="text" name="company_name" class="form-control" required
                               value="{{ old('company_name', $customer->company->name ?? '') }}"
                               placeholder="Acme Construction (Pty) Ltd">
                        @error('company_name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trading As</label>
                        <input type="text" name="trading_as" class="form-control"
                               value="{{ old('trading_as', $customer->company->trading_as ?? '') }}"
                               placeholder="If different from company name">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">VAT Number</label>
                        <input type="text" name="vat_number" class="form-control"
                               value="{{ old('vat_number', $customer->company->vat_number ?? '') }}"
                               placeholder="4123456789">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Registration Number</label>
                        <input type="text" name="registration_number" class="form-control"
                               value="{{ old('registration_number', $customer->company->registration_number ?? '') }}"
                               placeholder="2024/123456/07">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Company Email</label>
                        <input type="email" name="company_email" class="form-control"
                               value="{{ old('company_email', $customer->company->email ?? '') }}"
                               placeholder="accounts@company.co.za">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company Phone</label>
                        <input type="tel" name="company_phone" class="form-control"
                               value="{{ old('company_phone', $customer->company->phone ?? '') }}"
                               placeholder="011 123 4567">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control"
                           value="{{ old('contact_person', $customer->company->contact_person ?? '') }}"
                           placeholder="Primary contact name">
                </div>

                <div style="font-size:0.85rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--primary);margin:1rem 0 0.5rem;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:0.25rem;">Company Address</div>

                <div class="form-group">
                    <label class="form-label">Street Address</label>
                    <input type="text" name="address_line1" class="form-control"
                           value="{{ old('address_line1', $customer->company->address_line1 ?? '') }}"
                           placeholder="123 Main Road">
                </div>
                <div class="form-group">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" name="address_line2" class="form-control"
                           value="{{ old('address_line2', $customer->company->address_line2 ?? '') }}"
                           placeholder="Unit, Suite, etc.">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control"
                               value="{{ old('city', $customer->company->city ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Province</label>
                        <select name="province" class="form-control">
                            <option value="">Select</option>
                            @foreach(['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape','Free State','Limpopo','Mpumalanga','North West','Northern Cape'] as $prov)
                                <option value="{{ $prov }}" {{ old('province', $customer->company->province ?? '') == $prov ? 'selected' : '' }}>{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" class="form-control" maxlength="10"
                               value="{{ old('postal_code', $customer->company->postal_code ?? '') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Company Details</button>
            </form>
        </div>
    </div>

    {{-- Delivery Addresses --}}
    <div class="card">
        <div class="card-header">
            <span>Delivery Addresses</span>
            <span class="badge badge-secondary">{{ count($addresses) }}</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('customer.addresses.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Label</label>
                        <input type="text" name="label" class="form-control"
                               placeholder="e.g. Site, Office, Warehouse"
                               value="{{ old('label') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address Line 1 *</label>
                        <input type="text" name="line1" class="form-control" required
                               value="{{ old('line1') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" name="line2" class="form-control"
                               value="{{ old('line2') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">City *</label>
                        <input type="text" name="city" class="form-control" required
                               value="{{ old('city') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Province *</label>
                        <select name="province" class="form-control" required>
                            <option value="">Select</option>
                            @foreach(['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape','Free State','Limpopo','Mpumalanga','North West','Northern Cape'] as $prov)
                                <option value="{{ $prov }}" {{ old('province') == $prov ? 'selected' : '' }}>{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Postal Code *</label>
                        <input type="text" name="postal_code" class="form-control" required maxlength="10"
                               value="{{ old('postal_code') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Add Address</button>
            </form>
        </div>
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
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.account') }}" class="active"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
