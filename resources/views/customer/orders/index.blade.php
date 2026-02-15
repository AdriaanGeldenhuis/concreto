@extends('layouts.portal')
@section('title', 'My Orders')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}" class="active">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="page-header d-flex justify-between items-center flex-wrap gap-1">
        <h1>My Orders</h1>
        <a href="{{ route('customer.orders.create') }}" class="btn btn-primary">+ New Order</a>
    </div>

    @if($orders->count())
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>
                            <span class="badge status-{{ $order->status }}
                                @switch($order->status)
                                    @case('DELIVERED') badge-success @break
                                    @case('CANCELLED') badge-danger @break
                                    @case('PENDING_PAYMENT') badge-warning @break
                                    @case('IN_TRANSIT')
                                    @case('ARRIVED') badge-info @break
                                    @default badge-primary
                                @endswitch
                            ">
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                        </td>
                        <td>R{{ number_format($order->total, 2) }}</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination">{!! $orders->links('pagination::simple-default') !!}</div>
    @else
        <div class="empty-state">
            <div class="icon">&#9744;</div>
            <h3>No orders yet</h3>
            <p>Place your first order and it will appear here.</p>
            <a href="{{ route('customer.orders.create') }}" class="btn btn-primary">Place your first order</a>
        </div>
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}" class="active"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.account') }}"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
