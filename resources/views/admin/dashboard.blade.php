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
            <div class="stat-value">R{{ number_format($stats['today_revenue'], 2) }}</div>
            <div class="stat-label">Today's Revenue</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">R{{ number_format($stats['month_revenue'], 2) }}</div>
            <div class="stat-label">Month Revenue</div>
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
        <div class="stat-card">
            <div class="stat-value">{{ $stats['low_stock_count'] }}</div>
            <div class="stat-label">Low Stock Items</div>
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

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <span>Weekly Revenue</span>
            </div>
            <div class="card-body">
                <div style="display: flex; align-items: flex-end; justify-content: space-around; height: 200px; gap: 0.5rem; padding: 1rem 0;">
                    @php
                        $maxRevenue = $weeklyRevenue->max('revenue') ?: 1;
                    @endphp
                    @foreach($weeklyRevenue as $day)
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                R{{ number_format($day['revenue'], 0) }}
                            </div>
                            <div style="width: 100%; background: linear-gradient(180deg, var(--primary), var(--primary-dark)); border-radius: 4px 4px 0 0; height: {{ $maxRevenue > 0 ? ($day['revenue'] / $maxRevenue * 100) : 0 }}%; min-height: 4px;"></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                {{ $day['date'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span>Active Drivers</span>
            </div>
            <div class="card-body">
                <div style="max-height: 250px; overflow-y: auto;">
                    @forelse($activeDrivers as $driver)
                        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div class="font-semibold">{{ $driver->name }}</div>
                                @if($driver->active_order)
                                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">
                                        Order #{{ $driver->active_order->order_number }}
                                        <span class="badge badge-{{ match($driver->active_order->status) {
                                            'DELIVERED' => 'success',
                                            'CANCELLED', 'REFUNDED' => 'danger',
                                            'IN_TRANSIT', 'LOADED' => 'warning',
                                            default => 'info'
                                        } }}" style="margin-left: 0.5rem;">{{ str_replace('_', ' ', $driver->active_order->status) }}</span>
                                    </div>
                                @else
                                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">
                                        Available
                                    </div>
                                @endif
                            </div>
                            <div>
                                <span class="badge badge-success">Active</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted p-3">No active drivers.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if($lowStockProducts->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header">
            <span>Low Stock Alerts</span>
            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline">View All Products</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Current Stock</th>
                        <th>Low Stock Threshold</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($lowStockProducts as $product)
                    <tr>
                        <td>
                            <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold">{{ $product->name }}</a>
                        </td>
                        <td class="text-muted">{{ $product->unit }}</td>
                        <td>
                            <span class="badge badge-{{ $product->stock_qty <= 0 ? 'danger' : 'warning' }}">
                                {{ $product->stock_qty }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $product->low_stock_threshold }}</td>
                        <td>
                            @if($product->stock_qty <= 0)
                                <span class="badge badge-danger">Out of Stock</span>
                            @else
                                <span class="badge badge-warning">Low Stock</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-primary">Restock</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

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
