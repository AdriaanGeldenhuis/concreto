@extends('layouts.admin')
@section('title', 'Track: ' . $driver->name)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #driver-map { width:100%; height:350px; border-radius:var(--radius); border:1px solid var(--glass-border); background:var(--card); }
    .leaflet-popup-content-wrapper { background:var(--card) !important; color:#fff !important; border-radius:var(--radius) !important; border:1px solid var(--glass-border) !important; }
    .leaflet-popup-tip { background:var(--card) !important; }
    .leaflet-popup-content { margin:12px 16px !important; font-size:0.8125rem !important; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="breadcrumb"><a href="{{ route('admin.tracking.drivers') }}">Track Drivers</a> / {{ $driver->name }}</div>
    <h1>{{ $driver->name }}</h1>
    <div style="display:flex; gap:0.5rem; align-items:center;">
        <span class="badge badge-{{ $driver->is_active ? 'success' : 'danger' }}">{{ $driver->is_active ? 'Active' : 'Inactive' }}</span>
        @if($driver->phone)<a href="tel:{{ $driver->phone }}" class="btn btn-sm btn-primary">Call {{ $driver->phone }}</a>@endif
        <a href="{{ route('admin.drivers.shifts', $driver) }}" class="btn btn-sm btn-outline">Shifts & Salary</a>
    </div>
</div>

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Active Jobs</h6><h3 class="mb-0" style="margin-top:0.25rem; {{ $activeOrders->count() > 0 ? 'color:var(--success);' : '' }}">{{ $activeOrders->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Deliveries Today</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $todayDeliveries }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Speed</h6><h3 class="mb-0" style="margin-top:0.25rem;">@if($recentLocations->isNotEmpty())@php $lastLoc=$recentLocations->first(); @endphp{{ $lastLoc->speed > 0 ? number_format($lastLoc->speed, 0).' km/h' : 'Stopped' }}@else - @endif</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Last GPS</h6><h3 class="mb-0" style="margin-top:0.25rem; font-size:1rem;">@if($recentLocations->isNotEmpty()){{ $recentLocations->first()->recorded_at->diffForHumans() }}@else No Data @endif</h3></div></div>
</div>

@if($recentLocations->isNotEmpty())
<div class="card mb-2">
    <div class="card-header"><span>Driver Location & Path</span><small class="text-muted">Last {{ $recentLocations->count() }} points</small></div>
    <div class="card-body" style="padding:0.75rem;"><div id="driver-map"></div></div>
</div>
@endif

@if($activeOrders->isNotEmpty())
<div class="card">
    <div class="card-header"><span>Active Orders</span><span class="badge badge-success">{{ $activeOrders->count() }}</span></div>
    <div class="table-responsive"><table><thead><tr><th>Order</th><th>Status</th><th>Customer</th><th>Delivery Address</th><th class="text-right">Actions</th></tr></thead><tbody>
    @foreach($activeOrders as $order)
        <tr>
            <td><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold">{{ $order->order_number }}</a></td>
            <td><span class="badge badge-{{ match($order->status) { 'IN_TRANSIT' => 'warning', 'ARRIVED' => 'success', 'LOADED' => 'info', default => 'primary' } }}">{{ str_replace('_', ' ', $order->status) }}</span></td>
            <td>{{ $order->customer->user->name ?? '-' }}</td>
            <td><small>{{ $order->deliveryAddress->full_address ?? '-' }}</small></td>
            <td class="text-right"><a href="{{ route('admin.tracking.order', $order) }}" class="btn btn-sm btn-outline">Track</a></td>
        </tr>
    @endforeach
    </tbody></table></div>
</div>
@else
<div class="card"><div class="card-body"><p class="text-muted mb-0">No active delivery jobs right now.</p></div></div>
@endif

<div class="card">
    <div class="card-header"><span>GPS History</span><small class="text-muted">Last 50 updates</small></div>
    @if($recentLocations->isEmpty())
        <div class="card-body"><p class="text-muted mb-0">No location data recorded.</p></div>
    @else
        <div class="table-responsive"><table><thead><tr><th>Time</th><th>Lat</th><th>Lng</th><th>Speed</th><th>Heading</th><th>Accuracy</th><th>Order</th></tr></thead><tbody>
        @foreach($recentLocations as $loc)
            <tr><td>{{ $loc->recorded_at->format('d M H:i:s') }}</td><td>{{ number_format($loc->lat, 6) }}</td><td>{{ number_format($loc->lng, 6) }}</td><td>{{ $loc->speed > 0 ? number_format($loc->speed, 1).' km/h' : 'Stopped' }}</td><td>{!! $loc->heading ? number_format($loc->heading, 0).'&deg;' : '-' !!}</td><td>{{ $loc->accuracy ? number_format($loc->accuracy, 0).'m' : '-' }}</td><td>@if($loc->order)<a href="{{ route('admin.orders.show', $loc->order_id) }}">{{ $loc->order->order_number ?? '#'.$loc->order_id }}</a>@else - @endif</td></tr>
        @endforeach
        </tbody></table></div>
    @endif
</div>
@endsection

@if($recentLocations->isNotEmpty())
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @php
        $mapPoints = $recentLocations->map(fn($l) => ['lat'=>(float)$l->lat,'lng'=>(float)$l->lng,'speed'=>$l->speed,'time'=>$l->recorded_at->format('H:i:s')])->values();
    @endphp
    var locs = @json($mapPoints);
    if (!locs.length) return;
    var latest=locs[0], map=L.map('driver-map',{attributionControl:false}).setView([latest.lat,latest.lng],14);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',{maxZoom:19,subdomains:'abcd'}).addTo(map);
    var path=locs.slice().reverse().map(function(l){return[l.lat,l.lng];});
    L.polyline(path,{color:'#f97316',weight:3,opacity:0.7}).addTo(map);
    var icon=L.divIcon({className:'',html:'<div style="width:16px;height:16px;border-radius:50%;background:#22c55e;border:3px solid rgba(255,255,255,0.9);box-shadow:0 0 16px #22c55e;"></div>',iconSize:[16,16],iconAnchor:[8,8]});
    L.marker([latest.lat,latest.lng],{icon:icon}).bindPopup('<strong>Current</strong><br>'+(latest.speed>0?latest.speed+' km/h':'Stopped')+'<br>'+latest.time).addTo(map);
    if (path.length>1) map.fitBounds(path,{padding:[30,30],maxZoom:16});
});
</script>
@endpush
@endif
