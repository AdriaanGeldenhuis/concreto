@extends('layouts.portal')
@section('title', 'Job ' . $order->order_number)
@section('portal-name', 'Driver')
@section('home-url', route('driver.dashboard'))
@section('nav-links')
    <a href="{{ route('driver.dashboard') }}">Dashboard</a>
    <a href="{{ route('driver.jobs.index') }}" class="active">All Jobs</a>
@endsection

@section('content')
    <div class="page-header">
        <div class="breadcrumb"><a href="{{ route('driver.dashboard') }}">Dashboard</a> / <a href="{{ route('driver.jobs.index') }}">Jobs</a> / {{ $order->order_number }}</div>
        <h1>{{ $order->order_number }}</h1>
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

    {{-- Action buttons - prominent at the top for quick access --}}
    <div class="action-buttons">
        @if($order->status === 'ASSIGNED')
            <form method="POST" action="{{ route('driver.jobs.accept', $order) }}">
                @csrf
                <button type="submit" class="btn btn-success btn-block btn-lg">Accept Job</button>
            </form>
        @endif

        @if($order->status === 'ACCEPTED')
            <form method="POST" action="{{ route('driver.jobs.loaded', $order) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-block btn-lg">Mark as Loaded</button>
            </form>
        @endif

        @if($order->status === 'LOADED')
            <form method="POST" action="{{ route('driver.jobs.transit', $order) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-block btn-lg">Start Delivery</button>
            </form>
        @endif

        @if($order->status === 'IN_TRANSIT')
            <form method="POST" action="{{ route('driver.jobs.arrived', $order) }}">
                @csrf
                <button type="submit" class="btn btn-warning btn-block btn-lg">Mark as Arrived</button>
            </form>
        @endif

        @if(in_array($order->status, ['ARRIVED', 'DELIVERED_PENDING_SIGNATURE']))
            <a href="{{ route('driver.jobs.signature', $order) }}" class="btn btn-success btn-block btn-lg">Capture Signature</a>
        @endif

        @if($order->status === 'DELIVERED')
            <div class="alert alert-success">
                <strong>&#10003; Delivery Complete</strong> &mdash;
                Signed by: {{ $order->proofOfDelivery->signer_name ?? 'N/A' }}
            </div>
        @endif
    </div>

    {{-- Customer information --}}
    <div class="card">
        <div class="card-header">
            <span>Customer</span>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-row">
                    <span class="label">Name</span>
                    <span class="value">{{ $order->customer->user->name }}</span>
                </div>
                @if($order->customer->user->phone)
                    <div class="info-row">
                        <span class="label">Phone</span>
                        <span class="value">
                            <a href="tel:{{ $order->customer->user->phone }}" class="btn btn-sm btn-success" style="min-height:44px;">
                                &#128222; Call {{ $order->customer->user->phone }}
                            </a>
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Delivery address --}}
    @if($order->deliveryAddress)
        <div class="card">
            <div class="card-header">
                <span>Delivery Address</span>
            </div>
            <div class="card-body">
                <p class="mb-2">{{ $order->deliveryAddress->full_address }}</p>
                @if($order->deliveryAddress->gps_lat)
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $order->deliveryAddress->gps_lat }},{{ $order->deliveryAddress->gps_lng }}"
                       target="_blank"
                       rel="noopener"
                       class="btn btn-info btn-block btn-lg"
                       style="min-height:56px;">
                        &#128665; Navigate to Address
                    </a>
                @else
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($order->deliveryAddress->full_address) }}"
                       target="_blank"
                       rel="noopener"
                       class="btn btn-info btn-block btn-lg"
                       style="min-height:56px;">
                        &#128665; Navigate to Address
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- Scheduled date --}}
    @if($order->scheduled_date)
        <div class="card">
            <div class="card-header">
                <span>Schedule</span>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-row">
                        <span class="label">Date</span>
                        <span class="value">{{ $order->scheduled_date->format('D, d M Y') }}</span>
                    </div>
                    @if($order->scheduled_time_window)
                        <div class="info-row">
                            <span class="label">Time Window</span>
                            <span class="value">{{ $order->scheduled_time_window }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Items list --}}
    <div class="card">
        <div class="card-header">
            <span>Items ({{ $order->items->count() }})</span>
        </div>
        <div class="card-body">
            @foreach($order->items as $item)
                <div class="info-row">
                    <span class="label">{{ $item->product->name }}</span>
                    <span class="value font-bold">{{ $item->qty }} {{ $item->product->unit }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Order notes --}}
    @if($order->notes)
        <div class="card">
            <div class="card-header">
                <span>Notes</span>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">{{ $order->notes }}</div>
            </div>
        </div>
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('driver.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('driver.jobs.index') }}" class="active"><span class="nav-icon">&#9744;</span>All Jobs</a>
</nav>
@endsection
