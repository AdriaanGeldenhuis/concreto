@extends('layouts.admin')
@section('title', 'Customer: ' . $customer->user->name)
@section('content')
    <div class="page-header"><h1>{{ $customer->user->name }}</h1></div>

    <div class="form-row">
        <div>
            <div class="card">
                <div class="card-header">Customer Settings</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                        @csrf @method('PUT')
                        <div class="form-group"><label class="form-label">Type</label><select name="type" class="form-control"><option value="COD" {{ $customer->type == 'COD' ? 'selected' : '' }}>COD</option><option value="ACCOUNT" {{ $customer->type == 'ACCOUNT' ? 'selected' : '' }}>Account</option></select></div>
                        <div class="form-group"><label class="form-label">Credit Limit (R)</label><input type="number" name="credit_limit" class="form-control" step="0.01" value="{{ $customer->credit_limit }}"></div>
                        <div class="form-group"><label class="form-label">Payment Terms</label><input type="text" name="payment_terms" class="form-control" value="{{ $customer->payment_terms }}" placeholder="e.g. 30 days"></div>
                        <div class="form-check"><input type="checkbox" name="pay_before_dispatch" value="1" {{ $customer->pay_before_dispatch ? 'checked' : '' }}><label>Pay before dispatch</label></div>
                        <button type="submit" class="btn btn-primary mt-2">Save</button>
                    </form>
                </div>
            </div>
            <div class="card"><div class="card-body">
                <p><strong>Email:</strong> {{ $customer->user->email }}</p>
                <p><strong>Phone:</strong> {{ $customer->user->phone ?? '-' }}</p>
            </div></div>
        </div>
        <div>
            <div class="card">
                <div class="card-header">Recent Orders</div>
                <div class="table-responsive"><table><thead><tr><th>Order #</th><th>Status</th><th>Total</th></tr></thead><tbody>
                @foreach($customer->orders as $order)
                    <tr><td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td><td><span class="badge badge-primary">{{ $order->status }}</span></td><td>R{{ number_format($order->total, 2) }}</td></tr>
                @endforeach
                </tbody></table></div>
            </div>
            <div class="card">
                <div class="card-header">Addresses</div>
                <div class="card-body">
                    @foreach($customer->addresses as $addr)
                        <p>{{ $addr->label ? $addr->label . ': ' : '' }}{{ $addr->full_address }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
