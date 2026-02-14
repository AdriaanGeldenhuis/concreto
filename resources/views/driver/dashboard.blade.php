@extends('layouts.portal')
@section('title', 'Driver Dashboard')
@section('portal-name', 'Driver')
@section('home-url', route('driver.dashboard'))
@section('nav-links')
    <a href="{{ route('driver.dashboard') }}" class="active">Dashboard</a>
    <a href="{{ route('driver.jobs.index') }}">All Jobs</a>
@endsection

@section('content')
    <div class="page-header">
        <h1>My Jobs</h1>
        <p class="text-muted">{{ $completedToday }} deliveries completed today</p>
    </div>

    @if($activeOrders->count())
        @foreach($activeOrders as $order)
        <div class="card">
            <div class="card-header">
                <span>{{ $order->order_number }}</span>
                <span class="badge badge-primary">{{ str_replace('_', ' ', $order->status) }}</span>
            </div>
            <div class="card-body">
                @if($order->deliveryAddress)
                    <p><strong>Deliver to:</strong> {{ $order->deliveryAddress->full_address }}</p>
                @endif
                @if($order->scheduled_date)
                    <p><strong>Date:</strong> {{ $order->scheduled_date->format('d M Y') }} {{ $order->scheduled_time_window }}</p>
                @endif
                <p><strong>Items:</strong> {{ $order->items->count() }} item(s)</p>
                <a href="{{ route('driver.jobs.show', $order) }}" class="btn btn-primary btn-block mt-1">View Job</a>
            </div>
        </div>
        @endforeach
    @else
        <div class="empty-state">
            <div class="icon">&#10003;</div>
            <p>No active jobs. You're all caught up!</p>
        </div>
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('driver.dashboard') }}" class="active"><span class="nav-icon">&#9632;</span>Home</a>
    <a href="{{ route('driver.jobs.index') }}"><span class="nav-icon">&#9744;</span>All Jobs</a>
</nav>
@endsection
