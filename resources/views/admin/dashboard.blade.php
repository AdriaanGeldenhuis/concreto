@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
    <div class="page-header"><h1>Dashboard</h1></div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-value">{{ $stats['total_orders'] }}</div><div class="stat-label">Total Orders</div></div>
        <div class="stat-card"><div class="stat-value">{{ $stats['active_orders'] }}</div><div class="stat-label">Active Orders</div></div>
        <div class="stat-card"><div class="stat-value">{{ $stats['today_deliveries'] }}</div><div class="stat-label">Today's Deliveries</div></div>
        <div class="stat-card"><div class="stat-value">{{ $stats['total_customers'] }}</div><div class="stat-label">Customers</div></div>
        <div class="stat-card"><div class="stat-value">{{ $stats['total_drivers'] }}</div><div class="stat-label">Drivers</div></div>
        <div class="stat-card"><div class="stat-value">{{ $stats['pending_payments'] }}</div><div class="stat-label">Pending Payments</div></div>
    </div>

    <div class="card">
        <div class="card-header"><span>Recent Orders</span><a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline">View All</a></div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Order #</th><th>Customer</th><th>Status</th><th>Total</th><th>Driver</th><th>Date</th></tr></thead>
                <tbody>
                @foreach($recentOrders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td>{{ $order->customer->user->name ?? '-' }}</td>
                        <td><span class="badge badge-primary">{{ str_replace('_', ' ', $order->status) }}</span></td>
                        <td>R{{ number_format($order->total, 2) }}</td>
                        <td>{{ $order->driver->name ?? 'Unassigned' }}</td>
                        <td>{{ $order->created_at->format('d M H:i') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
