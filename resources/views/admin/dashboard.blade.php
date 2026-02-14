@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
    <div class="page-header">
        <h1>Dashboard</h1>
        <span class="text-muted text-small">{{ now()->format('l, d F Y') }}</span>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_orders'] }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['active_orders'] }}</div>
            <div class="stat-label">Active Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['today_deliveries'] }}</div>
            <div class="stat-label">Today's Deliveries</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_customers'] }}</div>
            <div class="stat-label">Customers</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_drivers'] }}</div>
            <div class="stat-label">Drivers</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['pending_payments'] }}</div>
            <div class="stat-label">Pending Payments</div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <span>Quick Actions</span>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="{{ route('admin.orders.index') }}" class="quick-action">
                    <span class="icon">&#9744;</span>
                    View Orders
                </a>
                <a href="{{ route('admin.products.create') }}" class="quick-action">
                    <span class="icon">&#9733;</span>
                    Add Product
                </a>
                <a href="{{ route('admin.quotes.create') }}" class="quick-action">
                    <span class="icon">&#9997;</span>
                    Create Quote
                </a>
                <a href="{{ route('admin.customers.index') }}" class="quick-action">
                    <span class="icon">&#9786;</span>
                    Customers
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Recent Orders</span>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline">View All</a>
        </div>
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
                    </tr>
                </thead>
                <tbody>
                @forelse($recentOrders as $order)
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
                            } }}">{{ str_replace('_', ' ', $order->status) }}</span>
                        </td>
                        <td class="font-semibold">R{{ number_format($order->total, 2) }}</td>
                        <td>
                            @if($order->driver)
                                <span class="badge badge-secondary">{{ $order->driver->name }}</span>
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $order->created_at->format('d M H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-3">No recent orders found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
