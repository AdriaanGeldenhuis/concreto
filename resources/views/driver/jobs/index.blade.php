@extends('layouts.portal')
@section('title', 'All Jobs')
@section('portal-name', 'Driver')
@section('home-url', route('driver.dashboard'))
@section('nav-links')
    <a href="{{ route('driver.dashboard') }}">Dashboard</a>
    <a href="{{ route('driver.jobs.index') }}" class="active">All Jobs</a>
@endsection

@section('content')
    <div class="page-header">
        <h1>All Jobs</h1>
    </div>

    @forelse($orders as $order)
        <a href="{{ route('driver.jobs.show', $order) }}" style="text-decoration:none; color:inherit;">
            <div class="card">
                <div class="card-header">
                    <span class="font-bold">{{ $order->order_number }}</span>
                    @php
                        $badgeClass = match($order->status) {
                            'DELIVERED' => 'success',
                            'CANCELLED' => 'danger',
                            'IN_TRANSIT' => 'warning',
                            'ARRIVED', 'DELIVERED_PENDING_SIGNATURE' => 'danger',
                            'ASSIGNED', 'ACCEPTED' => 'info',
                            'LOADED' => 'primary',
                            default => 'secondary',
                        };
                    @endphp
                    <span class="badge badge-{{ $badgeClass }}">
                        {{ ucwords(str_replace('_', ' ', strtolower($order->status))) }}
                    </span>
                </div>
                <div class="card-body">
                    @if($order->deliveryAddress)
                        <div class="d-flex items-start gap-1 mb-1">
                            <span class="text-muted text-small" style="min-width:24px;">&#128205;</span>
                            <span class="text-small">{{ Str::limit($order->deliveryAddress->full_address, 55) }}</span>
                        </div>
                    @endif
                    @if($order->scheduled_date)
                        <div class="d-flex items-center gap-1 mb-1">
                            <span class="text-muted text-small" style="min-width:24px;">&#128197;</span>
                            <span class="text-small">{{ $order->scheduled_date->format('d M Y') }}</span>
                        </div>
                    @endif
                    @if($order->items->count())
                        <div class="d-flex items-center gap-1">
                            <span class="text-muted text-small" style="min-width:24px;">&#128230;</span>
                            <span class="text-small">{{ $order->items->count() }} item(s)</span>
                        </div>
                    @endif
                </div>
                <div class="card-footer d-flex justify-between items-center">
                    <span class="text-small text-muted">
                        @if($order->customer && $order->customer->user)
                            {{ $order->customer->user->name }}
                        @endif
                    </span>
                    <span class="text-primary font-semibold text-small">View &rarr;</span>
                </div>
            </div>
        </a>
    @empty
        <div class="empty-state">
            <div class="icon">&#128466;</div>
            <h3>No Jobs Found</h3>
            <p>You have no assigned jobs yet. Jobs will appear here once they are assigned to you.</p>
        </div>
    @endforelse

    @if($orders->hasPages())
        <div class="pagination">{!! $orders->links('pagination::simple-default') !!}</div>
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('driver.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('driver.jobs.index') }}" class="active"><span class="nav-icon">&#9744;</span>All Jobs</a>
</nav>
@endsection
