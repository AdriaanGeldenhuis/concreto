@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
    <div class="page-header"><h1>Orders</h1></div>

    <form class="filters" method="GET">
        <div class="form-group">
            <select name="status" class="form-control">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ str_replace('_', ' ', $s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <input type="text" name="search" class="form-control" placeholder="Search order/customer..." value="{{ request('search') }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead><tr><th>Order #</th><th>Customer</th><th>Status</th><th>Total</th><th>Driver</th><th>Date</th><th></th></tr></thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td>{{ $order->customer->user->name ?? '-' }}</td>
                        <td><span class="badge badge-primary">{{ str_replace('_', ' ', $order->status) }}</span></td>
                        <td>R{{ number_format($order->total, 2) }}</td>
                        <td>{{ $order->driver->name ?? '-' }}</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline">View</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination">{!! $orders->appends(request()->query())->links('pagination::simple-default') !!}</div>
@endsection
