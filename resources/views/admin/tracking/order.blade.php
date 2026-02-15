@extends('layouts.admin')
@section('title', 'Track Order ' . $order->order_number)

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('admin.tracking.drivers') }}">Track Drivers</a> /
        <a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a> / Tracking
    </div>
    <div class="d-flex justify-between items-center flex-wrap gap-1">
        <h1>&#9737; Track Order {{ $order->order_number }}</h1>
        <span class="badge badge-{{ match($order->status) {
            'DELIVERED' => 'success',
            'CANCELLED' => 'danger',
            'IN_TRANSIT', 'LOADED' => 'warning',
            default => 'info'
        } }}">{{ str_replace('_', ' ', $order->status) }}</span>
    </div>
</div>

<div class="form-row">
    {{-- Order Info --}}
    <div>
        <div class="card">
            <div class="card-header">Order Details</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="label">Customer</span>
                    <span class="value">{{ $order->customer->user->name ?? '-' }}</span>
                </div>
                @if($order->driver)
                <div class="info-row">
                    <span class="label">Driver</span>
                    <span class="value">
                        <a href="{{ route('admin.tracking.driver-detail', $order->driver) }}">{{ $order->driver->name }}</a>
                        @if($order->driver->phone)
                            (<a href="tel:{{ $order->driver->phone }}">{{ $order->driver->phone }}</a>)
                        @endif
                    </span>
                </div>
                @endif
                @if($order->deliveryAddress)
                <div class="info-row">
                    <span class="label">Delivery To</span>
                    <span class="value">{{ $order->deliveryAddress->full_address }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="label">Total</span>
                    <span class="value">R{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Location Summary --}}
    <div>
        <div class="card">
            <div class="card-header">Tracking Summary</div>
            <div class="card-body">
                @if($locations->isNotEmpty())
                    @php $latest = $locations->first(); @endphp
                    <div class="info-row">
                        <span class="label">Last Position</span>
                        <span class="value">{{ number_format($latest->lat, 6) }}, {{ number_format($latest->lng, 6) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Last Updated</span>
                        <span class="value">{{ $latest->recorded_at->diffForHumans() }} ({{ $latest->recorded_at->format('H:i:s') }})</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Speed</span>
                        <span class="value">{{ $latest->speed > 0 ? number_format($latest->speed, 1) . ' km/h' : 'Stationary' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">GPS Points</span>
                        <span class="value">{{ $locations->count() }} recorded</span>
                    </div>
                @else
                    <p class="text-muted">No tracking data available for this order.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Location History --}}
<div class="card">
    <div class="card-header">Location History</div>
    @if($locations->isEmpty())
        <div class="card-body">
            <p class="text-muted">No location data recorded for this order yet.</p>
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
                    </tr>
                </thead>
                <tbody>
                @foreach($locations as $loc)
                    <tr>
                        <td>{{ $loc->recorded_at->format('d M H:i:s') }}</td>
                        <td>{{ number_format($loc->lat, 6) }}</td>
                        <td>{{ number_format($loc->lng, 6) }}</td>
                        <td>{{ $loc->speed > 0 ? number_format($loc->speed, 1) . ' km/h' : 'Stopped' }}</td>
                        <td>{{ $loc->heading ? number_format($loc->heading, 0) . '&deg;' : '-' }}</td>
                        <td>{{ $loc->accuracy ? number_format($loc->accuracy, 0) . 'm' : '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
