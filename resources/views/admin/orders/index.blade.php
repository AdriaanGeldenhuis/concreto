@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
    <div class="page-header">
        <h1>Orders</h1>
        <span class="badge badge-secondary">{{ $orders->total() }} total</span>
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm" style="margin-left: auto;">+ Create Order</a>
    </div>

    <div class="card mb-2">
        <div class="card-body">
            <form class="filters" method="GET" style="flex-wrap:wrap;">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Order # or customer..." value="{{ request('search') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Driver</label>
                    <select name="driver_id" class="form-control">
                        <option value="">All Drivers</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}" {{ request('driver_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment</label>
                    <select name="payment_status" class="form-control">
                        <option value="">All</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['status', 'search', 'driver_id', 'payment_status', 'from', 'to']))
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Actions Bar --}}
    <div class="card mb-2" id="bulk-actions" style="display:none;">
        <div class="card-body" style="padding:0.75rem;">
            <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                <span id="selected-count" class="font-semibold">0 selected</span>
                <form method="POST" action="{{ route('admin.orders.bulk-assign-driver') }}" style="display:inline-flex; gap:0.5rem; align-items:center;" id="bulk-assign-form">
                    @csrf
                    <div id="bulk-assign-ids"></div>
                    <select name="driver_id" class="form-control" style="max-width:150px;" required>
                        <option value="">Assign to...</option>
                        @foreach($drivers as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Assign</button>
                </form>
                <form method="POST" action="{{ route('admin.orders.bulk-update-status') }}" style="display:inline-flex; gap:0.5rem; align-items:center;" id="bulk-status-form">
                    @csrf
                    <div id="bulk-status-ids"></div>
                    <select name="status" class="form-control" style="max-width:150px;" required>
                        <option value="">Status...</option>
                        @foreach($statuses as $s)<option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>@endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-warning">Update</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Driver</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><input type="checkbox" class="order-cb" value="{{ $order->id }}"></td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold">{{ $order->order_number }}</a></td>
                        <td>{{ $order->customer->user->name ?? '-' }}</td>
                        <td><span class="badge badge-{{ match($order->status) { 'DELIVERED' => 'success', 'CANCELLED', 'REFUNDED' => 'danger', 'IN_TRANSIT', 'LOADED' => 'warning', 'PAID', 'PLACED', 'ASSIGNED', 'ACCEPTED' => 'info', default => 'primary' } }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span></td>
                        <td class="font-semibold">R{{ number_format($order->total, 2) }}</td>
                        <td>@if($order->driver)<span class="badge badge-secondary">{{ $order->driver->name }}</span>@else<span class="text-muted">-</span>@endif</td>
                        <td class="text-muted">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state"><div class="icon">&#9744;</div><h3>No orders found</h3><p>Try adjusting your filters.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($orders->hasPages())
        <div class="pagination">{!! $orders->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sa = document.getElementById('select-all');
    const cbs = document.querySelectorAll('.order-cb');
    const bar = document.getElementById('bulk-actions');
    const cnt = document.getElementById('selected-count');

    function update() {
        const checked = document.querySelectorAll('.order-cb:checked');
        bar.style.display = checked.length > 0 ? 'block' : 'none';
        cnt.textContent = checked.length + ' selected';
        ['bulk-assign-ids','bulk-status-ids'].forEach(id => {
            const c = document.getElementById(id);
            c.innerHTML = '';
            checked.forEach(cb => { const i = document.createElement('input'); i.type='hidden'; i.name='order_ids[]'; i.value=cb.value; c.appendChild(i); });
        });
    }
    sa.addEventListener('change', function() { cbs.forEach(cb => cb.checked = this.checked); update(); });
    cbs.forEach(cb => cb.addEventListener('change', update));
});
</script>
@endpush
@endsection
