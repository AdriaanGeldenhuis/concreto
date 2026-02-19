@extends('layouts.portal')
@section('title', 'Dashboard')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))

@section('content')
    <div class="page-header">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <p class="text-muted">
            <span class="type-badge {{ $customer->isCod() ? 'type-badge--cod' : 'type-badge--account' }}">
                {{ $customer->type }}
            </span>
        </p>
    </div>

    {{-- Live Delivery Tracking Banners --}}
    @if(isset($liveOrders) && $liveOrders->count())
        @foreach($liveOrders as $liveOrder)
            <a href="{{ route('customer.orders.show', $liveOrder) }}" style="text-decoration:none; color:inherit; display:block;">
                <div class="card" style="border-left:3px solid var(--primary, #F97316); margin-bottom:0.75rem;">
                    <div class="card-body" style="padding:1rem 1.25rem;">
                        <div class="d-flex justify-between items-center flex-wrap gap-1">
                            <div>
                                <div class="d-flex items-center gap-1 mb-1">
                                    @if(in_array($liveOrder->status, ['IN_TRANSIT', 'ARRIVED']))
                                        <span style="font-size:1.25rem;">&#128666;</span>
                                    @elseif($liveOrder->status === 'LOADED')
                                        <span style="font-size:1.25rem;">&#128230;</span>
                                    @else
                                        <span style="font-size:1.25rem;">&#9737;</span>
                                    @endif
                                    <strong>{{ $liveOrder->order_number }}</strong>
                                    <span class="badge badge-{{ match($liveOrder->status) {
                                        'IN_TRANSIT' => 'warning',
                                        'ARRIVED' => 'success',
                                        'LOADED' => 'info',
                                        default => 'primary'
                                    } }}" style="font-size:0.6875rem;">{{ str_replace('_', ' ', $liveOrder->status) }}</span>
                                </div>
                                <div class="text-small text-muted">
                                    @if($liveOrder->status === 'IN_TRANSIT')
                                        Your delivery is on its way!
                                    @elseif($liveOrder->status === 'ARRIVED')
                                        Driver has arrived at your location
                                    @elseif($liveOrder->status === 'LOADED')
                                        Your order is loaded and ready for dispatch
                                    @else
                                        Your order has been assigned to a driver
                                    @endif
                                    @if($liveOrder->driver)
                                        &middot; Driver: {{ $liveOrder->driver->name }}
                                    @endif
                                </div>
                            </div>
                            <span class="btn btn-primary btn-sm">Track</span>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $activeOrders }}</div>
            <div class="stat-label">Active Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $pendingInvoices }}</div>
            <div class="stat-label">Pending Invoices</div>
        </div>
    </div>

    <div class="quick-actions">
        <a href="{{ route('customer.orders.create') }}" class="quick-action">
            <span class="icon">&#10010;</span>
            Place New Order
        </a>
        <a href="{{ route('customer.orders.index') }}" class="quick-action">
            <span class="icon">&#9744;</span>
            View Orders
        </a>
        <a href="{{ route('customer.quotes.index') }}" class="quick-action">
            <span class="icon">&#9997;</span>
            My Quotes
        </a>
        <a href="{{ route('customer.addresses.index') }}" class="quick-action">
            <span class="icon">&#9873;</span>
            My Addresses
        </a>
    </div>

    <div class="card">
        <div class="card-header">Recent Orders</div>
        <div class="card-body">
            @if($recentOrders->count())
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($recentOrders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('customer.orders.show', $order) }}">
                                    <strong>{{ $order->order_number }}</strong>
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-primary status-{{ $order->status }}">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </td>
                            <td>R{{ number_format($order->total, 2) }}</td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="empty-state">
                    <div class="icon">&#9744;</div>
                    <p>No orders yet.</p>
                    <a href="{{ route('customer.orders.create') }}" class="btn btn-primary">Place your first order</a>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}" class="active"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.account') }}"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
