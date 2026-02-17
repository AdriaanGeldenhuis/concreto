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
            <form class="filters" method="GET">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Order number or customer..." value="{{ request('search') }}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['status', 'search']))
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
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
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold">{{ $order->order_number }}</a>
                        </td>
                        <td>{{ $order->customer->user->name ?? '-' }}</td>
                        <td>
                            <span class="badge badge-{{ match($order->status) {
                                'DELIVERED' => 'success',
                                'CANCELLED', 'REFUNDED' => 'danger',
                                'IN_TRANSIT', 'LOADED' => 'warning',
                                'PAID', 'PLACED', 'ASSIGNED', 'ACCEPTED' => 'info',
                                default => 'primary'
                            } }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                        </td>
                        <td class="font-semibold">R{{ number_format($order->total, 2) }}</td>
                        <td>
                            @if($order->driver)
                                <span class="badge badge-secondary">{{ $order->driver->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="icon">&#9744;</div>
                                <h3>No orders found</h3>
                                <p>Try adjusting your filters or search terms.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($orders->hasPages())
        <div class="pagination">{!! $orders->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
@endsection
