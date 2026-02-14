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
        <h1>Dashboard</h1>
    </div>

    {{-- Stats overview --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $activeOrders->count() }}</div>
            <div class="stat-label">Active Jobs</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $completedToday }}</div>
            <div class="stat-label">Completed Today</div>
        </div>
    </div>

    {{-- Active jobs --}}
    @if($activeOrders->count())
        <h3 class="mb-2">Active Jobs</h3>

        @foreach($activeOrders as $order)
            <a href="{{ route('driver.jobs.show', $order) }}" style="text-decoration:none; color:inherit;">
                <div class="card">
                    <div class="card-header">
                        <span class="font-bold">{{ $order->order_number }}</span>
                        <span class="badge badge-{{ $order->status === 'IN_TRANSIT' ? 'warning' : ($order->status === 'ARRIVED' ? 'danger' : 'primary') }}">
                            {{ ucwords(str_replace('_', ' ', strtolower($order->status))) }}
                        </span>
                    </div>
                    <div class="card-body">
                        @if($order->deliveryAddress)
                            <div class="d-flex items-start gap-1 mb-1">
                                <span class="text-muted text-small" style="min-width:24px;">&#128205;</span>
                                <span class="text-small">{{ Str::limit($order->deliveryAddress->full_address, 60) }}</span>
                            </div>
                        @endif
                        @if($order->scheduled_date)
                            <div class="d-flex items-center gap-1 mb-1">
                                <span class="text-muted text-small" style="min-width:24px;">&#128197;</span>
                                <span class="text-small">{{ $order->scheduled_date->format('d M Y') }} {{ $order->scheduled_time_window }}</span>
                            </div>
                        @endif
                        <div class="d-flex items-center gap-1">
                            <span class="text-muted text-small" style="min-width:24px;">&#128230;</span>
                            <span class="text-small">{{ $order->items->count() }} item(s)</span>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-between items-center">
                        <span class="text-small text-muted">Tap to view details</span>
                        <span class="text-primary font-semibold text-small">View &rarr;</span>
                    </div>
                </div>
            </a>
        @endforeach
    @else
        <div class="empty-state">
            <div class="icon">&#10003;</div>
            <h3>All Caught Up</h3>
            <p>No active jobs right now. New assignments will appear here automatically.</p>
            <a href="{{ route('driver.jobs.index') }}" class="btn btn-outline">View All Jobs</a>
        </div>
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('driver.dashboard') }}" class="active"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('driver.jobs.index') }}"><span class="nav-icon">&#9744;</span>All Jobs</a>
</nav>
@endsection
