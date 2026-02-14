@extends('layouts.portal')
@section('title', 'Dashboard')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}" class="active">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="page-header">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <p class="text-muted">
            <span class="type-badge {{ $customer->isCod() ? 'type-badge--cod' : 'type-badge--account' }}">
                {{ $customer->type }}
            </span>
        </p>
    </div>

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
    <a href="{{ route('customer.addresses.index') }}"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
