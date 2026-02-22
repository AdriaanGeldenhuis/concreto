@extends('layouts.admin')
@section('title', 'Track Order ' . $order->order_number)

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet" />
<style>
    #order-map { width:100%; height:400px; border-radius:var(--radius); border:1px solid var(--glass-border); background:var(--card); }
    .mapboxgl-popup-content { background:var(--card) !important; color:#fff !important; border-radius:var(--radius) !important; border:1px solid var(--glass-border) !important; padding:12px 16px !important; font-size:0.8125rem !important; }
    .mapboxgl-popup-tip { border-top-color:var(--card) !important; }
    .mapboxgl-popup-close-button { color:var(--text-muted) !important; font-size:1.2rem; padding:4px 8px; }
    .order-driver-avatar { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px; color:#fff; border:3px solid rgba(255,255,255,0.9); position:relative; text-transform:uppercase; font-family:system-ui,-apple-system,sans-serif; }
    .order-driver-pulse { position:absolute; top:-6px; left:-6px; right:-6px; bottom:-6px; border-radius:50%; border:2px solid; opacity:0.4; animation:order-pulse 2s ease-out infinite; }
    @keyframes order-pulse { 0% { transform:scale(1); opacity:0.4; } 100% { transform:scale(1.5); opacity:0; } }
    .timeline-step { display:flex; gap:0.75rem; align-items:flex-start; padding:0.4rem 0; }
    .timeline-dot { width:12px; height:12px; border-radius:50%; margin-top:4px; flex-shrink:0; border:2px solid rgba(255,255,255,0.3); }
    .timeline-dot--done { background:var(--success, #27ae60); border-color:var(--success, #27ae60); }
    .timeline-dot--current { background:var(--primary, #f97316); border-color:var(--primary, #f97316); box-shadow:0 0 8px var(--primary, #f97316); }
    .timeline-dot--pending { background:transparent; }
    .map-style-toggle { position:absolute; top:10px; right:10px; z-index:2; display:flex; gap:4px; background:rgba(0,0,0,0.7); border-radius:var(--radius-sm); padding:4px; backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.1); }
    .map-style-btn { padding:6px 12px; border:none; border-radius:var(--radius-sm); font-size:0.7rem; font-weight:600; cursor:pointer; color:rgba(255,255,255,0.7); background:transparent; transition:all 0.2s; text-transform:uppercase; letter-spacing:0.5px; }
    .map-style-btn:hover { color:#fff; background:rgba(255,255,255,0.1); }
    .map-style-btn.active { color:#fff; background:var(--primary); box-shadow:0 2px 8px rgba(249,115,22,0.3); }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="breadcrumb"><a href="{{ route('admin.tracking.drivers') }}">Track Drivers</a> / <a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a> / Tracking</div>
    <h1>Track Order {{ $order->order_number }}</h1>
    <span class="badge badge-{{ match($order->status) { 'DELIVERED'=>'success', 'CANCELLED'=>'danger', 'IN_TRANSIT','LOADED'=>'warning', default=>'info' } }}" style="font-size:0.85rem;">{{ str_replace('_', ' ', $order->status) }}</span>
</div>

@if($locations->isNotEmpty())
<div class="card mb-2">
    <div class="card-header"><span>Delivery Route</span><small class="text-muted">{{ $locations->count() }} GPS points</small></div>
    <div class="card-body" style="padding:0.75rem; position:relative;">
        <div class="map-style-toggle">
            <button class="map-style-btn active" data-style="streets">Streets</button>
            <button class="map-style-btn" data-style="satellite">Satellite</button>
            <button class="map-style-btn" data-style="terrain">Terrain</button>
        </div>
        <div id="order-map"></div>
    </div>
</div>
@endif

<div class="form-row">
    <div>
        <div class="card">
            <div class="card-header">Order Details</div>
            <div class="card-body">
                <div class="info-row"><span class="label">Customer</span><span class="value">{{ $order->customer->user->name ?? '-' }}</span></div>
                @if($order->driver)<div class="info-row"><span class="label">Driver</span><span class="value"><a href="{{ route('admin.tracking.driver-detail', $order->driver) }}">{{ $order->driver->name }}</a>@if($order->driver->phone) &middot; <a href="tel:{{ $order->driver->phone }}">{{ $order->driver->phone }}</a>@endif</span></div>@endif
                @if($order->deliveryAddress)<div class="info-row"><span class="label">Delivery To</span><span class="value">{{ $order->deliveryAddress->full_address }}</span></div>@endif
                <div class="info-row"><span class="label">Total</span><span class="value font-semibold">R{{ number_format($order->total, 2) }}</span></div>
                @if($order->scheduled_date)<div class="info-row"><span class="label">Scheduled</span><span class="value">{{ $order->scheduled_date->format('d M Y') }} {{ $order->scheduled_time_window }}</span></div>@endif
            </div>
        </div>
        <div class="card">
            <div class="card-header">Status Timeline</div>
            <div class="card-body">
                @php $flow=['PLACED','ASSIGNED','ACCEPTED','LOADED','IN_TRANSIT','ARRIVED','DELIVERED']; $idx=array_search($order->status,$flow); if($idx===false)$idx=-1; @endphp
                @foreach($flow as $i => $step)
                    <div class="timeline-step"><span class="timeline-dot {{ $i<$idx?'timeline-dot--done':($i==$idx?'timeline-dot--current':'timeline-dot--pending') }}"></span><div style="{{ $i==$idx?'font-weight:700;':($i<$idx?'':'opacity:0.4;') }}">{{ ucwords(str_replace('_',' ',$step)) }}</div></div>
                @endforeach
                @if($order->status==='CANCELLED')<div class="timeline-step"><span class="timeline-dot" style="background:var(--danger);border-color:var(--danger);"></span><div style="font-weight:700;color:var(--danger);">Cancelled</div></div>@endif
            </div>
        </div>
    </div>
    <div>
        <div class="card">
            <div class="card-header">Tracking Summary</div>
            <div class="card-body">
                @if($locations->isNotEmpty())
                    @php $latest=$locations->first(); @endphp
                    <div class="info-row"><span class="label">Last Position</span><span class="value">{{ number_format($latest->lat,6) }}, {{ number_format($latest->lng,6) }}</span></div>
                    <div class="info-row"><span class="label">Last Updated</span><span class="value">{{ $latest->recorded_at->diffForHumans() }} ({{ $latest->recorded_at->format('H:i:s') }})</span></div>
                    <div class="info-row"><span class="label">Speed</span><span class="value">{{ $latest->speed > 0 ? number_format($latest->speed,1).' km/h' : 'Stationary' }}</span></div>
                    <div class="info-row"><span class="label">GPS Points</span><span class="value">{{ $locations->count() }} recorded</span></div>
                @else
                    <p class="text-muted mb-0">No tracking data available.</p>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header">Location History</div>
            @if($locations->isEmpty())
                <div class="card-body"><p class="text-muted mb-0">No location data yet.</p></div>
            @else
                <div class="table-responsive"><table><thead><tr><th>Time</th><th>Coordinates</th><th>Speed</th><th>Accuracy</th></tr></thead><tbody>
                @foreach($locations as $loc)
                    <tr><td><small>{{ $loc->recorded_at->format('d M H:i:s') }}</small></td><td><small>{{ number_format($loc->lat,5) }}, {{ number_format($loc->lng,5) }}</small></td><td>{{ $loc->speed > 0 ? number_format($loc->speed,1).' km/h' : 'Stopped' }}</td><td>{{ $loc->accuracy ? number_format($loc->accuracy,0).'m' : '-' }}</td></tr>
                @endforeach
                </tbody></table></div>
            @endif
        </div>
    </div>
</div>
@endsection

@if($locations->isNotEmpty())
@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var locs = @json($locations->map(fn($l)=>['lat'=>(float)$l->lat,'lng'=>(float)$l->lng,'speed'=>$l->speed,'time'=>$l->recorded_at->format('H:i:s')])->values());
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
        container: 'order-map',
        style: mapStyles.streets,
        center: [latest.lng, latest.lat],
        zoom: 14,
        attributionControl: false
    });

    map.addControl(new mapboxgl.NavigationControl(), 'bottom-right');

    // Style toggle
    document.querySelectorAll('.map-style-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.map-style-btn').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            map.setStyle(mapStyles[btn.dataset.style]);
            map.once('style.load', function() { addRouteData(); });
        });
    });

    var driverName = @json($order->driver->name ?? 'Driver');
    var driverColors = ['#f97316','#3b82f6','#22c55e','#ef4444','#a855f7','#eab308','#ec4899','#06b6d4','#f43f5e','#84cc16','#8b5cf6','#14b8a6','#f59e0b','#6366f1','#10b981','#e11d48','#0ea5e9','#d946ef','#65a30d','#0891b2'];
    var driverColor = driverColors[{{ $order->driver_id ?? 0 }} % driverColors.length];
    var letter = (driverName || '?').charAt(0).toUpperCase();

    var path = locs.slice().reverse().map(function(l) { return [l.lng, l.lat]; });

    function addRouteData() {
        map.addSource('order-route', {
            type: 'geojson',
            data: { type: 'Feature', geometry: { type: 'LineString', coordinates: path } }
        });
        map.addLayer({
            id: 'order-route-line',
            type: 'line',
            source: 'order-route',
            paint: {
                'line-color': driverColor,
                'line-width': 3,
                'line-opacity': 0.7,
                'line-dasharray': [2, 1]
            }
        });
    }

    map.on('load', function() {
        addRouteData();

        // Fit bounds
        if (locs.length > 1) {
            var bounds = new mapboxgl.LngLatBounds();
            locs.forEach(function(l) { bounds.extend([l.lng, l.lat]); });
            map.fitBounds(bounds, { padding: 50, maxZoom: 16 });
        }
    });

    // Driver avatar marker at latest position
    var avatarEl = document.createElement('div');
    avatarEl.innerHTML = '<div class="order-driver-avatar" style="background:'+driverColor+';box-shadow:0 0 16px '+driverColor+';">' +
        '<div class="order-driver-pulse" style="border-color:'+driverColor+';"></div>' + letter + '</div>';

    var driverPopup = new mapboxgl.Popup({ offset: 25, closeButton: true })
        .setHTML('<strong style="color:'+driverColor+';">'+driverName+'</strong><br>'+latest.time+'<br>'+(latest.speed>0?latest.speed+' km/h':'Stopped'));

    new mapboxgl.Marker({ element: avatarEl, anchor: 'center' })
        .setLngLat([latest.lng, latest.lat])
        .setPopup(driverPopup)
        .addTo(map);

    // Start point marker
    if (locs.length > 1) {
        var startLoc = locs[locs.length - 1];
        var startEl = document.createElement('div');
        startEl.style.cssText = 'width:10px;height:10px;border-radius:50%;background:#737373;border:2px solid rgba(255,255,255,0.6);';

        var startPopup = new mapboxgl.Popup({ offset: 8, closeButton: true })
            .setHTML('<strong>Start</strong><br>' + startLoc.time);

        new mapboxgl.Marker({ element: startEl, anchor: 'center' })
            .setLngLat([startLoc.lng, startLoc.lat])
            .setPopup(startPopup)
            .addTo(map);
    }
});
</script>
@endpush
@endif
