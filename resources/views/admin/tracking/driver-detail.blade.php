@extends('layouts.admin')
@section('title', 'Track: ' . $driver->name)

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet" />
<style>
    #driver-map { width:100%; border-radius:var(--radius); border:1px solid var(--glass-border); background:var(--card); }
    .mapboxgl-popup-content { background:var(--card) !important; color:#fff !important; border-radius:var(--radius) !important; border:1px solid var(--glass-border) !important; padding:12px 16px !important; font-size:0.8125rem !important; }
    .mapboxgl-popup-tip { border-top-color:var(--card) !important; }
    .mapboxgl-popup-close-button { color:var(--text-muted) !important; font-size:1.2rem; padding:4px 8px; }
    .driver-detail-avatar { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px; color:#fff; border:3px solid rgba(255,255,255,0.9); position:relative; text-transform:uppercase; font-family:system-ui,-apple-system,sans-serif; }
    .driver-detail-pulse { position:absolute; top:-6px; left:-6px; right:-6px; bottom:-6px; border-radius:50%; border:2px solid; opacity:0.4; animation:detail-pulse 2s ease-out infinite; }
    @keyframes detail-pulse { 0% { transform:scale(1); opacity:0.4; } 100% { transform:scale(1.5); opacity:0; } }
    .map-style-toggle { position:absolute; top:10px; right:10px; z-index:2; display:flex; gap:4px; background:rgba(0,0,0,0.7); border-radius:var(--radius-sm); padding:4px; backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.1); }
    .map-style-btn { padding:6px 12px; border:none; border-radius:var(--radius-sm); font-size:0.7rem; font-weight:600; cursor:pointer; color:rgba(255,255,255,0.7); background:transparent; transition:all 0.2s; text-transform:uppercase; letter-spacing:0.5px; }
    .map-style-btn:hover { color:#fff; background:rgba(255,255,255,0.1); }
    .map-style-btn.active { color:#fff; background:var(--primary); box-shadow:0 2px 8px rgba(249,115,22,0.3); }
    .detail-tabs { display:flex; gap:0.25rem; background:rgba(255,255,255,0.04); border-radius:var(--radius); padding:4px; border:1px solid var(--glass-border); width:fit-content; }
    .detail-tab { padding:0.5rem 1rem; border-radius:var(--radius-sm); font-size:0.8rem; font-weight:600; cursor:pointer; color:var(--text-muted); background:transparent; border:none; transition:all 0.15s; display:flex; align-items:center; gap:0.4rem; }
    .detail-tab:hover { color:var(--text); background:rgba(255,255,255,0.05); }
    .detail-tab.active { color:#fff; background:var(--primary); box-shadow:0 2px 8px rgba(249,115,22,0.3); }
    .detail-tab-pane { display:none; }
    .detail-tab-pane.active { display:block; }
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

@php
    $lastLoc = $recentLocations->first();
    $speedDisplay = $lastLoc && $lastLoc->speed > 0 ? number_format($lastLoc->speed, 0) . ' km/h' : ($lastLoc ? 'Stopped' : '-');
    $lastGpsDisplay = $lastLoc ? $lastLoc->recorded_at->diffForHumans() : 'No Data';
@endphp

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card">
        <div class="card-body" style="padding:0.75rem; text-align:center;">
            <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Active Jobs</h6>
            <h3 class="mb-0" style="margin-top:0.25rem; {{ $activeOrders->count() > 0 ? 'color:var(--success);' : '' }}">{{ $activeOrders->count() }}</h3>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding:0.75rem; text-align:center;">
            <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Deliveries Today</h6>
            <h3 class="mb-0" style="margin-top:0.25rem;">{{ $todayDeliveries }}</h3>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding:0.75rem; text-align:center;">
            <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Speed</h6>
            <h3 class="mb-0" style="margin-top:0.25rem;">{{ $speedDisplay }}</h3>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="padding:0.75rem; text-align:center;">
            <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Last GPS</h6>
            <h3 class="mb-0" style="margin-top:0.25rem; font-size:1rem;">{{ $lastGpsDisplay }}</h3>
        </div>
    </div>
</div>

@if($activeOrders->isNotEmpty())
<div class="card mb-2">
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
<div class="card mb-2"><div class="card-body"><p class="text-muted mb-0">No active delivery jobs right now.</p></div></div>
@endif

{{-- Tabs: Map / GPS History --}}
<div class="detail-tabs" style="margin-bottom:0.75rem;">
    <button class="detail-tab active" data-tab="map-tab">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/></svg>
        Map
    </button>
    <button class="detail-tab" data-tab="gps-tab">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        GPS History
    </button>
</div>

<div class="detail-tab-pane active" id="map-tab">
    @if($recentLocations->isNotEmpty())
    <div class="card" style="margin-bottom:0;">
        <div class="card-header"><span>Driver Location & Path</span><small class="text-muted">Last {{ $recentLocations->count() }} points</small></div>
        <div class="card-body" style="padding:0.75rem; position:relative;">
            <div class="map-style-toggle">
                <button class="map-style-btn active" data-style="streets">Streets</button>
                <button class="map-style-btn" data-style="satellite">Satellite</button>
                <button class="map-style-btn" data-style="terrain">Terrain</button>
            </div>
            <div id="driver-map" style="height:calc(100vh - 520px); min-height:350px;"></div>
        </div>
    </div>
    @else
    <div class="card"><div class="card-body"><p class="text-muted mb-0">No location data recorded for this driver.</p></div></div>
    @endif
</div>

<div class="detail-tab-pane" id="gps-tab">
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
</div>
@endsection

@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    document.querySelectorAll('.detail-tab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.detail-tab').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.detail-tab-pane').forEach(function(p) { p.classList.remove('active'); });
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).classList.add('active');
            if (btn.dataset.tab === 'map-tab' && window._driverMap) {
                setTimeout(function() { window._driverMap.resize(); }, 50);
            }
        });
    });

    @if($recentLocations->isNotEmpty())
    @php
        $mapPoints = $recentLocations->map(fn($l) => ['lat'=>(float)$l->lat,'lng'=>(float)$l->lng,'speed'=>$l->speed,'time'=>$l->recorded_at->format('H:i:s')])->values();
    @endphp
    var locs = @json($mapPoints);
    if (!locs.length) return;

    var mapboxToken = @json(config('services.mapbox.token'));
    mapboxgl.accessToken = mapboxToken;

    var latest = locs[0];
    var mapStyles = {
        streets: 'mapbox://styles/mapbox/dark-v11',
        satellite: 'mapbox://styles/mapbox/satellite-streets-v12',
        terrain: 'mapbox://styles/mapbox/outdoors-v12'
    };

    var map = new mapboxgl.Map({
        container: 'driver-map',
        style: mapStyles.streets,
        center: [latest.lng, latest.lat],
        zoom: 14,
        attributionControl: false
    });
    window._driverMap = map;

    map.addControl(new mapboxgl.NavigationControl(), 'bottom-right');

    var driverName = @json($driver->name);
    var driverColors = ['#f97316','#3b82f6','#22c55e','#ef4444','#a855f7','#eab308','#ec4899','#06b6d4','#f43f5e','#84cc16','#8b5cf6','#14b8a6','#f59e0b','#6366f1','#10b981','#e11d48','#0ea5e9','#d946ef','#65a30d','#0891b2'];
    var driverColor = driverColors[{{ $driver->id }} % driverColors.length];
    var letter = (driverName || '?').charAt(0).toUpperCase();

    // Style toggle buttons
    document.querySelectorAll('.map-style-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.map-style-btn').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var styleName = btn.dataset.style;
            map.setStyle(mapStyles[styleName]);
            // Re-add sources and layers after style change
            map.once('style.load', function() { addMapData(); });
        });
    });

    function addMapData() {
        // Path line
        var path = locs.slice().reverse().map(function(l) { return [l.lng, l.lat]; });
        map.addSource('driver-path', {
            type: 'geojson',
            data: { type: 'Feature', geometry: { type: 'LineString', coordinates: path } }
        });
        map.addLayer({
            id: 'driver-path-line',
            type: 'line',
            source: 'driver-path',
            paint: {
                'line-color': driverColor,
                'line-width': 3,
                'line-opacity': 0.7,
                'line-dasharray': [2, 1]
            }
        });
    }

    map.on('load', function() {
        addMapData();

        // Fit bounds to path
        if (locs.length > 1) {
            var bounds = new mapboxgl.LngLatBounds();
            locs.forEach(function(l) { bounds.extend([l.lng, l.lat]); });
            map.fitBounds(bounds, { padding: 50, maxZoom: 16 });
        }
    });

    // Driver avatar marker
    var avatarEl = document.createElement('div');
    avatarEl.innerHTML = '<div class="driver-detail-avatar" style="background:'+driverColor+';box-shadow:0 0 16px '+driverColor+';">' +
        '<div class="driver-detail-pulse" style="border-color:'+driverColor+';"></div>' + letter + '</div>';

    var popup = new mapboxgl.Popup({ offset: 25, closeButton: true })
        .setHTML('<strong style="color:'+driverColor+';">'+driverName+'</strong><br>'+(latest.speed>0?latest.speed+' km/h':'Stopped')+'<br>'+latest.time);

    new mapboxgl.Marker({ element: avatarEl, anchor: 'center' })
        .setLngLat([latest.lng, latest.lat])
        .setPopup(popup)
        .addTo(map);
    @endif
});
</script>
@endpush
