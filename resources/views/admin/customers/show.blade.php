@extends('layouts.admin')
@section('title', 'Customer: ' . $customer->user->name)
@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('admin.customers.index') }}">Customers</a> / {{ $customer->user->name }}
        </div>
        <h1>{{ $customer->user->name }}</h1>
        <span class="badge badge-{{ $customer->type == 'COD' ? 'warning' : 'info' }}">{{ $customer->type }}</span>
    </div>

    <div class="form-row">
        {{-- Left Column --}}
        <div>
            {{-- Contact Info --}}
            <div class="card">
                <div class="card-header">Contact Information</div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="label">Email</span>
                            <span class="value">{{ $customer->user->email }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Phone</span>
                            <span class="value">{{ $customer->user->phone ?? '-' }}</span>
                        </div>
                    </div>
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

            {{-- Addresses --}}
            <div class="card">
                <div class="card-header">
                    <span>Addresses</span>
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
