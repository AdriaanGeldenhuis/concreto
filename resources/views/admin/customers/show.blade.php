@extends('layouts.admin')
@section('title', 'Customer: ' . $customer->user->name)
@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('admin.customers.index') }}">Customers</a> / {{ $customer->user->name }}
        </div>
        <h1>{{ $customer->company->display_name ?? $customer->user->name }}</h1>
        <span class="badge badge-{{ $customer->type == 'COD' ? 'warning' : 'info' }}">{{ $customer->type }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-2">{{ session('success') }}</div>
    @endif

    <div class="form-row">
        {{-- Left Column --}}
        <div>
            {{-- Company / Business Details --}}
            <div class="card">
                <div class="card-header">Company / Business Details</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.customers.update-company', $customer) }}">
                        @csrf @method('PUT')
                        <div class="form-row">
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Company Name *</label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name', $customer->company->name ?? '') }}">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Trading As</label>
                                <input type="text" name="trading_as" class="form-control" value="{{ old('trading_as', $customer->company->trading_as ?? '') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Registration Number</label>
                                <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $customer->company->registration_number ?? '') }}">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label class="form-label">VAT Number</label>
                                <input type="text" name="vat_number" class="form-control" value="{{ old('vat_number', $customer->company->vat_number ?? '') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Company Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $customer->company->email ?? '') }}">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Company Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->company->phone ?? '') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $customer->company->contact_person ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address_line1" class="form-control" value="{{ old('address_line1', $customer->company->address_line1 ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2', $customer->company->address_line2 ?? '') }}">
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="flex:1">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $customer->company->city ?? '') }}">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Province</label>
                                <select name="province" class="form-control">
                                    <option value="">Select Province</option>
                                    @foreach(['Eastern Cape','Free State','Gauteng','KwaZulu-Natal','Limpopo','Mpumalanga','North West','Northern Cape','Western Cape'] as $prov)
                                        <option value="{{ $prov }}" {{ old('province', $customer->company->province ?? '') == $prov ? 'selected' : '' }}>{{ $prov }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="flex:0.5">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $customer->company->postal_code ?? '') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-1">Save Company Details</button>
                    </form>
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="card">
                <div class="card-header">Contact Information</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.customers.update-contact', $customer) }}">
                        @csrf @method('PUT')
                        <div class="form-row">
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name', $customer->user->name) }}">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required value="{{ old('email', $customer->user->email) }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->user->phone ?? '') }}">
                        </div>
                        <button type="submit" class="btn btn-primary mt-1">Save Contact Info</button>
                    </form>
                </div>
            </div>

            {{-- Customer Settings --}}
            <div class="card">
                <div class="card-header">Customer Settings</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label class="form-label">Account Type</label>
                            <select name="type" class="form-control">
                                <option value="COD" {{ $customer->type == 'COD' ? 'selected' : '' }}>COD</option>
                                <option value="ACCOUNT" {{ $customer->type == 'ACCOUNT' ? 'selected' : '' }}>Account</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Credit Limit (R)</label>
                            <input type="number" name="credit_limit" class="form-control" step="0.01" value="{{ $customer->credit_limit }}">
                            <div class="form-hint">Set to 0 for no credit limit.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Terms</label>
                            <input type="text" name="payment_terms" class="form-control" value="{{ $customer->payment_terms }}" placeholder="e.g. 30 days">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="pay_before_dispatch" value="1" id="pay_before_dispatch" {{ $customer->pay_before_dispatch ? 'checked' : '' }}>
                            <label for="pay_before_dispatch">Pay before dispatch</label>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Save Settings</button>
                    </form>
                </div>
            </div>

            {{-- Delivery Addresses --}}
            <div class="card">
                <div class="card-header">
                    <span>Delivery Addresses</span>
                    <span class="badge badge-secondary">{{ $customer->addresses->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($customer->addresses as $addr)
                        <div class="info-row">
                            <span class="label">{{ $addr->label ?? 'Address' }}</span>
                            <span class="value">{{ $addr->full_address }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No addresses on file.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div>
            {{-- Recent Orders --}}
            <div class="card">
                <div class="card-header">
                    <span>Recent Orders</span>
                    <span class="badge badge-secondary">{{ $customer->orders->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Status</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($customer->orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold">{{ $order->order_number }}</a>
                                </td>
                                <td>
                                    <span class="badge badge-{{ match($order->status) {
                                        'DELIVERED' => 'success',
                                        'CANCELLED', 'REFUNDED' => 'danger',
                                        'IN_TRANSIT', 'LOADED' => 'warning',
                                        'PAID', 'PLACED', 'ASSIGNED', 'ACCEPTED' => 'info',
                                        default => 'primary'
                                    } }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                                </td>
                                <td class="text-right font-semibold">R{{ number_format($order->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted p-3">No orders yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
