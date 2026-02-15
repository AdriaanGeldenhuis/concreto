@extends('layouts.admin')
@section('title', 'Track: ' . $driver->name)

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('admin.tracking.drivers') }}">Track Drivers</a> / {{ $driver->name }}
    </div>
    <div class="d-flex justify-between items-center flex-wrap gap-1">
        <h1>&#9737; {{ $driver->name }}</h1>
        @if($driver->phone)
            <a href="tel:{{ $driver->phone }}" class="btn btn-sm btn-primary">&#9742; Call {{ $driver->phone }}</a>
        @endif
    </div>
</div>

{{-- Driver Stats --}}
<div class="form-row">
    <div class="card" style="flex:1;">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:2rem; font-weight:700;">{{ $activeOrders->count() }}</div>
            <div class="text-muted">Active Jobs</div>
        </div>
    </div>
    <div class="card" style="flex:1;">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:2rem; font-weight:700;">{{ $todayDeliveries }}</div>
            <div class="text-muted">Deliveries Today</div>
        </div>
    </div>
    <div class="card" style="flex:1;">
        <div class="card-body" style="text-align:center;">
            @if($recentLocations->isNotEmpty())
                @php $lastLoc = $recentLocations->first(); @endphp
                <div style="font-size:2rem; font-weight:700;">
                    {{ $lastLoc->speed > 0 ? number_format($lastLoc->speed, 0) . ' km/h' : 'Stopped' }}
                </div>
                <div class="text-muted">{{ $lastLoc->recorded_at->diffForHumans() }}</div>
            @else
                <div style="font-size:2rem; font-weight:700;">-</div>
                <div class="text-muted">No GPS Data</div>
            @endif
        </div>
    </div>
</div>

{{-- Active Orders --}}
@if($activeOrders->isNotEmpty())
<div class="card">
    <div class="card-header">Active Orders</div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Customer</th>
                    <th>Delivery Address</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($activeOrders as $order)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                    <td>
                        <span class="badge badge-{{ match($order->status) {
                            'IN_TRANSIT' => 'warning',
                            'ARRIVED' => 'success',
                            'LOADED' => 'info',
                            default => 'primary'
                        } }}">{{ str_replace('_', ' ', $order->status) }}</span>
                    </td>
                    <td>{{ $order->customer->user->name ?? '-' }}</td>
                    <td>{{ $order->deliveryAddress->full_address ?? '-' }}</td>
                    <td><a href="{{ route('admin.tracking.order', $order) }}" class="btn btn-sm btn-outline">Track Order</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Recent Location History --}}
<div class="card">
    <div class="card-header">Recent GPS History (Last 50 updates)</div>
    @if($recentLocations->isEmpty())
        <div class="card-body">
            <p class="text-muted">No location data recorded for this driver.</p>
        </div>
    @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Speed</th>
                        <th>Heading</th>
                        <th>Accuracy</th>
                        <th>Order</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentLocations as $loc)
                    <tr>
                        <td>{{ $loc->recorded_at->format('d M H:i:s') }}</td>
                        <td>{{ number_format($loc->lat, 6) }}</td>
                        <td>{{ number_format($loc->lng, 6) }}</td>
                        <td>{{ $loc->speed > 0 ? number_format($loc->speed, 1) . ' km/h' : 'Stopped' }}</td>
                        <td>{{ $loc->heading ? number_format($loc->heading, 0) . '&deg;' : '-' }}</td>
                        <td>{{ $loc->accuracy ? number_format($loc->accuracy, 0) . 'm' : '-' }}</td>
                        <td>
                            @if($loc->order)
                                <a href="{{ route('admin.orders.show', $loc->order_id) }}">{{ $loc->order->order_number ?? '#' . $loc->order_id }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
