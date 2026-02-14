@extends('layouts.portal')
@section('title', 'Job ' . $order->order_number)
@section('portal-name', 'Driver')
@section('home-url', route('driver.dashboard'))

@section('content')
    <div class="page-header">
        <div class="breadcrumb"><a href="{{ route('driver.dashboard') }}">Dashboard</a> / {{ $order->order_number }}</div>
        <h1>{{ $order->order_number }}</h1>
        <span class="badge badge-primary">{{ str_replace('_', ' ', $order->status) }}</span>
    </div>

    {{-- Tracking active when in transit --}}
    @if(in_array($order->status, ['ACCEPTED', 'LOADED', 'IN_TRANSIT', 'ARRIVED']))
        <div id="driver-tracking" data-order-id="{{ $order->id }}" data-url="{{ route('driver.jobs.location', $order) }}"></div>
    @endif

    <div class="card">
        <div class="card-header">Customer</div>
        <div class="card-body">
            <p><strong>{{ $order->customer->user->name }}</strong></p>
            <p>{{ $order->customer->user->phone }}</p>
            @if($order->deliveryAddress)
                <p><strong>Delivery:</strong> {{ $order->deliveryAddress->full_address }}</p>
                @if($order->deliveryAddress->gps_lat)
                    <button class="btn btn-outline btn-sm mt-1" onclick="openNavigation({{ $order->deliveryAddress->gps_lat }}, {{ $order->deliveryAddress->gps_lng }}, '{{ addslashes($order->deliveryAddress->full_address) }}')">
                        Navigate
                    </button>
                @endif
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">Items</div>
        <div class="card-body">
            @foreach($order->items as $item)
                <div class="d-flex justify-between mb-1">
                    <span>{{ $item->product->name }}</span>
                    <span class="font-bold">{{ $item->qty }} {{ $item->product->unit }}</span>
                </div>
            @endforeach
            @if($order->notes)
                <div class="alert alert-info mt-2"><strong>Notes:</strong> {{ $order->notes }}</div>
            @endif
        </div>
    </div>

    <div class="action-buttons">
        @if($order->status === 'ASSIGNED')
            <form method="POST" action="{{ route('driver.jobs.accept', $order) }}">@csrf
                <button type="submit" class="btn btn-success btn-block btn-lg">Accept Job</button>
            </form>
        @endif

        @if($order->status === 'ACCEPTED')
            <form method="POST" action="{{ route('driver.jobs.loaded', $order) }}">@csrf
                <button type="submit" class="btn btn-primary btn-block btn-lg">Mark as Loaded</button>
            </form>
        @endif

        @if($order->status === 'LOADED')
            <form method="POST" action="{{ route('driver.jobs.transit', $order) }}">@csrf
                <button type="submit" class="btn btn-primary btn-block btn-lg">Start Delivery</button>
            </form>
        @endif

        @if($order->status === 'IN_TRANSIT')
            <form method="POST" action="{{ route('driver.jobs.arrived', $order) }}">@csrf
                <button type="submit" class="btn btn-warning btn-block btn-lg">Mark as Arrived</button>
            </form>
        @endif

        @if(in_array($order->status, ['ARRIVED', 'DELIVERED_PENDING_SIGNATURE']))
            <a href="{{ route('driver.jobs.signature', $order) }}" class="btn btn-success btn-block btn-lg">Capture Signature</a>
        @endif

        @if($order->status === 'DELIVERED')
            <div class="alert alert-success text-center">
                <strong>Delivery Complete</strong><br>
                Signed by: {{ $order->proofOfDelivery->signer_name ?? 'N/A' }}
            </div>
        @endif
    </div>
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('driver.dashboard') }}"><span class="nav-icon">&#9632;</span>Home</a>
    <a href="{{ route('driver.jobs.index') }}" class="active"><span class="nav-icon">&#9744;</span>All Jobs</a>
</nav>
@endsection
