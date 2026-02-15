@extends('layouts.admin')
@section('title', 'Track Drivers')

@section('content')
<div class="page-header">
    <h1>&#9737; Track Drivers</h1>
    <p class="text-muted">Real-time overview of all driver locations and active deliveries.</p>
</div>

{{-- Active Drivers --}}
<div class="card">
    <div class="card-header">
        <span>Active Drivers ({{ $activeDrivers->count() }})</span>
        <span class="badge badge-success">On Delivery</span>
    </div>
    @if($activeDrivers->isEmpty())
        <div class="card-body">
            <p class="text-muted">No drivers are currently on active deliveries.</p>
        </div>
    @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Phone</th>
                        <th>Current Order</th>
                        <th>Status</th>
                        <th>Customer</th>
                        <th>Last GPS Update</th>
                        <th>Speed</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($activeDrivers as $driver)
                    <tr>
                        <td class="font-semibold">{{ $driver->name }}</td>
                        <td>
                            @if($driver->phone)
                                <a href="tel:{{ $driver->phone }}">{{ $driver->phone }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $driver->active_order) }}">
                                {{ $driver->active_order->order_number }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-{{ match($driver->active_order->status) {
                                'IN_TRANSIT' => 'warning',
                                'ARRIVED' => 'success',
                                'LOADED' => 'info',
                                default => 'primary'
                            } }}">
                                {{ str_replace('_', ' ', $driver->active_order->status) }}
                            </span>
                        </td>
                        <td>{{ $driver->active_order->customer->user->name ?? '-' }}</td>
                        <td>
                            @if($driver->last_location)
                                <span title="{{ $driver->last_location->recorded_at->format('d M Y H:i:s') }}">
                                    {{ $driver->last_location->recorded_at->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-muted">No GPS data</span>
                            @endif
                        </td>
                        <td>
                            @if($driver->last_location && $driver->last_location->speed > 0)
                                {{ number_format($driver->last_location->speed, 0) }} km/h
                            @else
                                <span class="text-muted">Stationary</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.tracking.driver-detail', $driver) }}" class="btn btn-sm btn-primary">Track</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Idle Drivers --}}
<div class="card">
    <div class="card-header">
        <span>Idle Drivers ({{ $idleDrivers->count() }})</span>
        <span class="badge badge-secondary" style="background:rgba(255,255,255,0.1); color:rgba(255,255,255,0.5);">Available</span>
    </div>
    @if($idleDrivers->isEmpty())
        <div class="card-body">
            <p class="text-muted">No idle drivers.</p>
        </div>
    @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Phone</th>
                        <th>Last Known Location</th>
                        <th>Last Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($idleDrivers as $driver)
                    <tr>
                        <td class="font-semibold">{{ $driver->name }}</td>
                        <td>
                            @if($driver->phone)
                                <a href="tel:{{ $driver->phone }}">{{ $driver->phone }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($driver->last_location)
                                {{ number_format($driver->last_location->lat, 5) }}, {{ number_format($driver->last_location->lng, 5) }}
                            @else
                                <span class="text-muted">Unknown</span>
                            @endif
                        </td>
                        <td>
                            @if($driver->last_location)
                                {{ $driver->last_location->recorded_at->diffForHumans() }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.tracking.driver-detail', $driver) }}" class="btn btn-sm btn-outline">Details</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
