@extends('layouts.admin')
@section('title', 'Tracking')

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet" />
<style>
    #tracking-map { width:100%; height:550px; border-radius:var(--radius); border:1px solid var(--glass-border); background:var(--card); }
    .map-legend { display:flex; gap:1.25rem; padding:0.75rem 1rem; margin-top:0.75rem; background:rgba(255,255,255,0.03); border-radius:var(--radius-sm); border:1px solid var(--glass-border); font-size:0.75rem; color:var(--text-muted); flex-wrap:wrap; }
    .map-legend-item { display:flex; align-items:center; gap:0.4rem; }
    .driver-avatar-marker { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px; color:#fff; border:3px solid rgba(255,255,255,0.9); box-shadow:0 0 16px var(--marker-color), 0 2px 8px rgba(0,0,0,0.5); position:relative; text-transform:uppercase; font-family:system-ui,-apple-system,sans-serif; letter-spacing:-0.5px; }
    .driver-avatar-marker.idle { opacity:0.6; border-color:rgba(255,255,255,0.5); }
    .driver-avatar-pulse { position:absolute; top:-6px; left:-6px; right:-6px; bottom:-6px; border-radius:50%; border:2px solid; opacity:0.4; animation:driver-pulse 2s ease-out infinite; }
    @keyframes driver-pulse { 0% { transform:scale(1); opacity:0.4; } 100% { transform:scale(1.5); opacity:0; } }
    .map-legend-avatar { width:22px; height:22px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:10px; color:#fff; border:2px solid rgba(255,255,255,0.7); flex-shrink:0; }
    .driver-list-avatar { width:30px; height:30px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:13px; color:#fff; border:2px solid rgba(255,255,255,0.6); flex-shrink:0; background:#737373; }
    .mapboxgl-popup-content { background:var(--card) !important; color:#fff !important; border-radius:var(--radius) !important; border:1px solid var(--glass-border) !important; box-shadow:var(--shadow-md) !important; padding:12px 16px !important; font-size:0.8125rem !important; line-height:1.5 !important; }
    .mapboxgl-popup-tip { border-top-color:var(--card) !important; }
    .mapboxgl-popup-close-button { color:var(--text-muted) !important; font-size:1.2rem; padding:4px 8px; }
    .mapboxgl-popup-content a { color:var(--primary) !important; text-decoration:none !important; font-weight:600; }
    .mapboxgl-popup-content a:hover { text-decoration:underline !important; }
    .driver-popup-name { font-weight:700; font-size:0.875rem; margin-bottom:4px; }
    .driver-popup-status { display:inline-block; padding:2px 8px; border-radius:100px; font-size:0.6875rem; font-weight:600; margin-bottom:6px; }
    .driver-popup-status--active { background:rgba(34,197,94,0.15); color:#22c55e; }
    .driver-popup-status--idle { background:rgba(255,255,255,0.08); color:#a3a3a3; }
    .driver-popup-meta { color:var(--text-muted); font-size:0.75rem; }
    .client-marker { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:11px; color:#000; border:3px solid rgba(255,255,255,0.9); box-shadow:0 0 12px var(--marker-glow), 0 2px 8px rgba(0,0,0,0.5); text-transform:uppercase; font-family:system-ui,-apple-system,sans-serif; cursor:pointer; transition:transform 0.15s; }
    .client-marker:hover { transform:scale(1.15); }
    .client-popup-distance { display:inline-block; padding:2px 8px; border-radius:100px; font-size:0.6875rem; font-weight:700; margin:4px 0; }
    .map-controls { position:absolute; top:10px; right:10px; z-index:2; display:flex; gap:4px; background:rgba(0,0,0,0.7); border-radius:var(--radius-sm); padding:4px; backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.1); }
    .map-controls-left { position:absolute; top:10px; left:10px; z-index:2; display:flex; gap:4px; background:rgba(0,0,0,0.7); border-radius:var(--radius-sm); padding:4px; backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.1); }
    .map-ctrl-btn { padding:6px 12px; border:none; border-radius:var(--radius-sm); font-size:0.7rem; font-weight:600; cursor:pointer; color:rgba(255,255,255,0.7); background:transparent; transition:all 0.2s; text-transform:uppercase; letter-spacing:0.5px; }
    .map-ctrl-btn:hover { color:#fff; background:rgba(255,255,255,0.1); }
    .map-ctrl-btn.active { color:#fff; background:var(--primary); box-shadow:0 2px 8px rgba(249,115,22,0.3); }
    .layer-btn { padding:6px 12px; border:none; border-radius:var(--radius-sm); font-size:0.7rem; font-weight:600; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:4px; }
    .layer-btn.on { color:#fff; background:rgba(255,255,255,0.15); }
    .layer-btn.off { color:rgba(255,255,255,0.35); background:transparent; text-decoration:line-through; }
    .layer-dot { width:8px; height:8px; border-radius:50%; }
    .tab-pane { display:none; }
    .tab-pane.active { display:block; }
    .tracking-tabs { display:flex; gap:0.25rem; margin-bottom:1rem; background:rgba(255,255,255,0.04); border-radius:var(--radius); padding:4px; border:1px solid var(--glass-border); width:fit-content; }
    .tracking-tab { padding:0.55rem 1.25rem; border-radius:var(--radius-sm); font-size:0.8125rem; font-weight:600; cursor:pointer; color:var(--text-muted); background:transparent; border:none; transition:all var(--transition-fast); display:flex; align-items:center; gap:0.5rem; }
    .tracking-tab:hover { color:var(--text); background:rgba(255,255,255,0.05); }
    .tracking-tab.active { color:#fff; background:var(--primary); box-shadow:0 2px 8px rgba(249,115,22,0.3); }
    .tracking-tab svg { width:16px; height:16px; }
    .distance-band-bar { display:flex; align-items:center; gap:0.5rem; margin-bottom:0.4rem; }
    .distance-band-color { width:14px; height:14px; border-radius:3px; flex-shrink:0; }
    .distance-band-label { font-size:0.75rem; color:var(--text-muted); min-width:70px; }
    .distance-band-count { font-size:0.8rem; font-weight:700; min-width:28px; text-align:right; }
    .distance-band-fill { height:18px; border-radius:3px; min-width:2px; transition:width 0.4s ease; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Tracking</h1>
    <div style="display:flex; gap:0.5rem; align-items:center;">
        <small class="text-muted" id="refresh-status">Auto-refresh: 15s</small>
        <a href="{{ route('admin.drivers.index') }}" class="btn btn-outline btn-sm">Manage Drivers</a>
        <a href="{{ route('admin.ops.index') }}" class="btn btn-outline btn-sm">Ops Board</a>
    </div>
</div>

{{-- Summary --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Drivers</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $activeDrivers->count() + $idleDrivers->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">On Delivery</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $activeDrivers->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Idle</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $idleDrivers->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Clients</h6><h3 class="mb-0" style="margin-top:0.25rem; color:#FFC000;">{{ $mapCustomers->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Vendors</h6><h3 class="mb-0" style="margin-top:0.25rem; color:#ef4444;">{{ $mapVendors->count() }}</h3></div></div>
</div>

<div class="tracking-tabs">
    <button class="tracking-tab active" data-tab="map"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg> Map</button>
    <button class="tracking-tab" data-tab="list"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg> List</button>
</div>

<div class="tab-pane active" id="tab-map">
    <div class="form-row" style="align-items:flex-start;">
        <div style="flex:3;">
            <div class="card">
                <div class="card-body" style="padding:0.75rem; position:relative;">
                    {{-- Map style toggle (top-right) --}}
                    <div class="map-controls">
                        <button class="map-ctrl-btn active" data-style="streets">Streets</button>
                        <button class="map-ctrl-btn" data-style="satellite">Satellite</button>
                        <button class="map-ctrl-btn" data-style="terrain">Terrain</button>
                    </div>
                    {{-- Layer toggles (top-left) --}}
                    <div class="map-controls-left">
                        <button class="layer-btn on" id="toggle-drivers"><span class="layer-dot" style="background:#f97316;"></span> Drivers</button>
                        <button class="layer-btn on" id="toggle-clients"><span class="layer-dot" style="background:#FFC000;"></span> Clients</button>
                        <button class="layer-btn on" id="toggle-vendors"><span class="layer-dot" style="background:#ef4444;"></span> Vendors</button>
                        <button class="layer-btn off" id="toggle-zones"><span class="layer-dot" style="background:#FF7A00;"></span> Zones</button>
                    </div>
                    <div id="tracking-map"></div>
                    <div class="map-legend" id="map-legend">
                        <div class="map-legend-item"><span class="map-legend-avatar" style="background:#ef4444; font-size:9px;">V</span> Vendor</div>
                        <div class="map-legend-item"><span class="layer-dot" style="background:#FFD600;"></span> 0-10 km</div>
                        <div class="map-legend-item"><span class="layer-dot" style="background:#FFC000;"></span> 10-20</div>
                        <div class="map-legend-item"><span class="layer-dot" style="background:#FFA000;"></span> 20-30</div>
                        <div class="map-legend-item"><span class="layer-dot" style="background:#FF7A00;"></span> 30-40</div>
                        <div class="map-legend-item"><span class="layer-dot" style="background:#FF5200;"></span> 40-50</div>
                        <div class="map-legend-item"><span class="layer-dot" style="background:#E63900;"></span> 50+</div>
                    </div>
                </div>
            </div>
        </div>
        <div style="flex:1; min-width:220px;">
            <div class="card">
                <div class="card-header">Client Distance</div>
                <div class="card-body" style="padding:1rem;">
                    @php
                        $bandColors = ['#FFD600', '#FFC000', '#FFA000', '#FF7A00', '#FF5200', '#E63900'];
                        $maxCount = max(array_column($distanceBands, 'count')) ?: 1;
                    @endphp
                    @foreach($distanceBands as $i => $band)
                        <div class="distance-band-bar">
                            <span class="distance-band-color" style="background:{{ $bandColors[$i] }};"></span>
                            <span class="distance-band-label">{{ $band['label'] }}</span>
                            <span class="distance-band-count">{{ $band['count'] }}</span>
                            <div style="flex:1;">
                                <div class="distance-band-fill" style="background:{{ $bandColors[$i] }}; width:{{ ($band['count'] / $maxCount) * 100 }}%; opacity:0.7;"></div>
                            </div>
                        </div>
                    @endforeach
                    <hr style="border-color:rgba(255,255,255,0.06); margin:0.75rem 0;">
                    <div style="font-size:0.7rem; color:var(--text-muted);">Distance from nearest vendor/depot.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane" id="tab-list">
    <div class="card">
        <div class="card-header"><span>Active Drivers ({{ $activeDrivers->count() }})</span><span class="badge badge-success">On Delivery</span></div>
        @if($activeDrivers->isEmpty())
            <div class="card-body"><p class="text-muted mb-0">No drivers currently on active deliveries.</p></div>
        @else
            <div class="table-responsive"><table><thead><tr><th>Driver</th><th>Order</th><th>Status</th><th>Customer</th><th>Delivery To</th><th>Last GPS</th><th>Speed</th><th class="text-right">Actions</th></tr></thead><tbody>
            @foreach($activeDrivers as $driver)
                <tr>
                    <td><div style="display:flex; align-items:center; gap:0.5rem;"><span class="driver-list-avatar" data-driver-id="{{ $driver->id }}">{{ strtoupper(substr($driver->name, 0, 1)) }}</span><div><div class="font-semibold">{{ $driver->name }}</div>@if($driver->phone)<small><a href="tel:{{ $driver->phone }}">{{ $driver->phone }}</a></small>@endif</div></div></td>
                    <td><a href="{{ route('admin.orders.show', $driver->active_order) }}" class="font-semibold">{{ $driver->active_order->order_number }}</a></td>
                    <td><span class="badge badge-{{ match($driver->active_order->status) { 'IN_TRANSIT' => 'warning', 'ARRIVED' => 'success', 'LOADED' => 'info', default => 'primary' } }}">{{ str_replace('_', ' ', $driver->active_order->status) }}</span></td>
                    <td>{{ $driver->active_order->customer->user->name ?? '-' }}</td>
                    <td><small>{{ $driver->active_order->deliveryAddress->full_address ?? '-' }}</small></td>
                    <td>@if($driver->last_location)<span title="{{ $driver->last_location->recorded_at->format('d M Y H:i:s') }}">{{ $driver->last_location->recorded_at->diffForHumans() }}</span>@else<span class="text-muted">No GPS</span>@endif</td>
                    <td>@if($driver->last_location && $driver->last_location->speed > 0){{ number_format($driver->last_location->speed, 0) }} km/h @else<span class="text-muted">Stopped</span>@endif</td>
                    <td class="text-right"><div style="display:flex; gap:0.25rem; justify-content:flex-end;"><a href="{{ route('admin.tracking.order', $driver->active_order) }}" class="btn btn-sm btn-outline">Order</a><a href="{{ route('admin.tracking.driver-detail', $driver) }}" class="btn btn-sm btn-primary">Track</a></div></td>
                </tr>
            @endforeach
            </tbody></table></div>
        @endif
    </div>
    <div class="card">
        <div class="card-header"><span>Idle Drivers ({{ $idleDrivers->count() }})</span><span class="badge badge-secondary">Available</span></div>
        @if($idleDrivers->isEmpty())
            <div class="card-body"><p class="text-muted mb-0">No idle drivers.</p></div>
        @else
            <div class="table-responsive"><table><thead><tr><th>Driver</th><th>Phone</th><th>Last Known Location</th><th>Last Active</th><th class="text-right">Actions</th></tr></thead><tbody>
            @foreach($idleDrivers as $driver)
                <tr>
                    <td><div style="display:flex; align-items:center; gap:0.5rem;"><span class="driver-list-avatar" data-driver-id="{{ $driver->id }}">{{ strtoupper(substr($driver->name, 0, 1)) }}</span><span class="font-semibold">{{ $driver->name }}</span></div></td>
                    <td>@if($driver->phone)<a href="tel:{{ $driver->phone }}">{{ $driver->phone }}</a>@else<span class="text-muted">-</span>@endif</td>
                    <td>@if($driver->last_location)<small>{{ number_format($driver->last_location->lat, 5) }}, {{ number_format($driver->last_location->lng, 5) }}</small>@else<span class="text-muted">Unknown</span>@endif</td>
                    <td>@if($driver->last_location){{ $driver->last_location->recorded_at->diffForHumans() }}@else<span class="text-muted">Never</span>@endif</td>
                    <td class="text-right"><a href="{{ route('admin.tracking.driver-detail', $driver) }}" class="btn btn-sm btn-outline">Details</a></td>
                </tr>
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
    document.querySelectorAll('.tracking-tab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tracking-tab').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.tab-pane').forEach(function(p) { p.classList.remove('active'); });
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
            if (btn.dataset.tab === 'map' && window._trackingMap) setTimeout(function() { window._trackingMap.resize(); }, 50);
        });
    });

    var drivers = @json($mapDrivers);
    var customers = @json($mapCustomers);
    var vendors = @json($mapVendors);
    mapboxgl.accessToken = @json(config('services.mapbox.token'));

    var mapStyles = { streets: 'mapbox://styles/mapbox/dark-v11', satellite: 'mapbox://styles/mapbox/satellite-streets-v12', terrain: 'mapbox://styles/mapbox/outdoors-v12' };
    var map = new mapboxgl.Map({ container: 'tracking-map', style: mapStyles.streets, center: [25.0, -29.0], zoom: 6, attributionControl: false });
    window._trackingMap = map;
    map.addControl(new mapboxgl.NavigationControl(), 'bottom-right');

    // Map style toggle
    document.querySelectorAll('.map-ctrl-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.map-ctrl-btn').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            map.setStyle(mapStyles[btn.dataset.style]);
            map.once('style.load', function() { if (layerState.zones) addZoneLayers(); });
        });
    });

    // Layer visibility state
    var layerState = { drivers: true, clients: true, vendors: true, zones: false };

    // Driver colors
    var driverColors = ['#f97316','#3b82f6','#22c55e','#ef4444','#a855f7','#eab308','#ec4899','#06b6d4','#f43f5e','#84cc16','#8b5cf6','#14b8a6','#f59e0b','#6366f1','#10b981','#e11d48','#0ea5e9','#d946ef','#65a30d','#0891b2'];
    var driverColorMap = {}, colorIndex = 0;
    function getDriverColor(id) { if (!driverColorMap[id]) { driverColorMap[id] = driverColors[colorIndex % driverColors.length]; colorIndex++; } return driverColorMap[id]; }

    // Distance band colors
    function getBandColor(d) { if (d===null) return '#E63900'; if (d<10) return '#FFD600'; if (d<20) return '#FFC000'; if (d<30) return '#FFA000'; if (d<40) return '#FF7A00'; if (d<50) return '#FF5200'; return '#E63900'; }
    function getBandLabel(d) { if (d===null) return 'Unknown'; if (d<10) return '0-10 km'; if (d<20) return '10-20 km'; if (d<30) return '20-30 km'; if (d<40) return '30-40 km'; if (d<50) return '40-50 km'; return '50+ km'; }

    function getInitials(name) { var p = (name||'?').trim().split(/\s+/); return p.length >= 2 ? (p[0][0]+p[1][0]).toUpperCase() : p[0][0].toUpperCase(); }

    var bounds = new mapboxgl.LngLatBounds();
    var driverMarkers = {}, clientMarkers = [], vendorMarkers = [];
    var legendEl = document.getElementById('map-legend');

    // --- VENDOR MARKERS ---
    vendors.forEach(function(v) {
        bounds.extend([v.lng, v.lat]);
        var ini = getInitials(v.name);
        var el = document.createElement('div');
        el.innerHTML = '<div class="driver-avatar-marker" style="background:#ef4444; --marker-color:#ef4444; width:34px; height:34px; font-size:'+(ini.length>1?'11':'14')+'px;">'+ini+'</div>';
        el.style.cursor = 'pointer';
        var popup = new mapboxgl.Popup({offset:20,closeButton:true}).setHTML('<div class="driver-popup-name" style="color:#ef4444;">&#128666; '+v.name+'</div><div class="driver-popup-meta">'+v.address+'</div><div class="driver-popup-meta" style="margin-top:4px;font-weight:600;">Depot / Vendor</div>');
        var m = new mapboxgl.Marker({element:el,anchor:'center'}).setLngLat([v.lng,v.lat]).setPopup(popup).addTo(map);
        vendorMarkers.push(m);
    });

    // --- CLIENT MARKERS (distance-colored) ---
    customers.forEach(function(c) {
        bounds.extend([c.lng, c.lat]);
        var color = getBandColor(c.distance);
        var ini = getInitials(c.name);
        var el = document.createElement('div');
        el.innerHTML = '<div class="client-marker" style="background:'+color+'; --marker-glow:'+color+';">'+ini+'</div>';
        var distLabel = c.distance !== null ? c.distance.toFixed(1)+' km' : 'Unknown';
        var popup = new mapboxgl.Popup({offset:20,closeButton:true}).setHTML(
            '<div class="driver-popup-name">'+c.name+'</div>'+
            (c.label ? '<div class="driver-popup-meta" style="margin-bottom:4px;"><strong>'+c.label+'</strong></div>' : '')+
            '<div class="client-popup-distance" style="background:'+color+';color:#000;">'+distLabel+' &middot; '+getBandLabel(c.distance)+'</div>'+
            '<div class="driver-popup-meta">'+c.address+'</div>'+
            '<div style="margin-top:8px;"><a href="'+c.customerUrl+'">View customer &rarr;</a></div>'
        );
        var m = new mapboxgl.Marker({element:el,anchor:'center'}).setLngLat([c.lng,c.lat]).setPopup(popup).addTo(map);
        clientMarkers.push(m);
    });

    // --- DRIVER MARKERS ---
    function renderDriverMarkers(list) {
        Object.keys(driverMarkers).forEach(function(id) { driverMarkers[id].remove(); delete driverMarkers[id]; });
        document.querySelectorAll('.map-legend-driver').forEach(function(el) { el.remove(); });

        list.forEach(function(d) {
            bounds.extend([d.lng, d.lat]);
            var color = getDriverColor(d.id);
            var letter = (d.name||'?')[0].toUpperCase();
            var idle = d.active ? '' : ' idle';
            var pulse = d.active ? '<div class="driver-avatar-pulse" style="border-color:'+color+';"></div>' : '';
            var el = document.createElement('div');
            el.innerHTML = '<div class="driver-avatar-marker'+idle+'" style="background:'+color+'; --marker-color:'+color+';">'+pulse+letter+'</div>';
            el.style.cursor = 'pointer';
            if (!layerState.drivers) el.style.display = 'none';

            var html = '<div class="driver-popup-name" style="color:'+color+';">'+d.name+'</div>'+
                '<span class="driver-popup-status driver-popup-status--'+(d.active?'active':'idle')+'">'+d.status+'</span><br>'+
                (d.order ? '<div style="margin:4px 0;">Order: <a href="'+d.orderUrl+'">'+d.order+'</a></div>' : '')+
                (d.speed > 0 ? '<div class="driver-popup-meta">'+d.speed+' km/h</div>' : '')+
                '<div class="driver-popup-meta">Updated '+d.updated+'</div>'+
                '<div style="margin-top:8px;"><a href="'+d.detailUrl+'">View details &rarr;</a></div>';

            var popup = new mapboxgl.Popup({offset:25,closeButton:true}).setHTML(html);
            var marker = new mapboxgl.Marker({element:el,anchor:'center'}).setLngLat([d.lng,d.lat]).setPopup(popup).addTo(map);
            driverMarkers[d.id] = marker;

            if (legendEl) {
                var item = document.createElement('div');
                item.className = 'map-legend-item map-legend-driver';
                item.innerHTML = '<span class="map-legend-avatar" style="background:'+color+';">'+letter+'</span> '+d.name+(d.active?'':' <small style="opacity:0.5;">(idle)</small>');
                item.style.cursor = 'pointer';
                (function(m) { item.addEventListener('click', function() { map.flyTo({center:m.getLngLat(),zoom:15}); m.togglePopup(); }); })(marker);
                legendEl.appendChild(item);
            }
        });
    }

    renderDriverMarkers(drivers);
    if (drivers.length || customers.length || vendors.length) map.fitBounds(bounds, {padding:40, maxZoom:14});

    // Color list avatars
    document.querySelectorAll('.driver-list-avatar').forEach(function(el) {
        var id = el.dataset.driverId;
        if (id && driverColorMap[id]) { el.style.background = driverColorMap[id]; el.style.borderColor = 'rgba(255,255,255,0.8)'; el.style.boxShadow = '0 0 8px '+driverColorMap[id]; }
    });

    // --- DISTANCE ZONE CIRCLES ---
    function createCircle(center, km, pts) {
        pts = pts || 64; var ret = [];
        var dx = km / (111.32 * Math.cos(center[1] * Math.PI / 180)), dy = km / 110.574;
        for (var i = 0; i < pts; i++) { var t = (i/pts)*(2*Math.PI); ret.push([center[0]+dx*Math.cos(t), center[1]+dy*Math.sin(t)]); }
        ret.push(ret[0]);
        return {type:'Feature',geometry:{type:'Polygon',coordinates:[ret]}};
    }
    function addZoneLayers() {
        var radii = [50,40,30,20,10], colors = ['#E63900','#FF5200','#FF7A00','#FFA000','#FFC000'];
        vendors.forEach(function(v, vi) {
            radii.forEach(function(r, ri) {
                var id = 'zone-'+vi+'-'+r, data = createCircle([v.lng,v.lat], r);
                if (map.getSource(id)) { map.getSource(id).setData(data); } else {
                    map.addSource(id, {type:'geojson',data:data});
                    map.addLayer({id:id+'-fill',type:'fill',source:id,paint:{'fill-color':colors[ri],'fill-opacity':0.06}});
                    map.addLayer({id:id+'-line',type:'line',source:id,paint:{'line-color':colors[ri],'line-width':1.5,'line-opacity':0.35,'line-dasharray':[3,2]}});
                }
            });
        });
    }
    function removeZoneLayers() {
        vendors.forEach(function(v, vi) {
            [50,40,30,20,10].forEach(function(r) {
                var id = 'zone-'+vi+'-'+r;
                if (map.getLayer(id+'-fill')) map.removeLayer(id+'-fill');
                if (map.getLayer(id+'-line')) map.removeLayer(id+'-line');
                if (map.getSource(id)) map.removeSource(id);
            });
        });
    }

    // --- LAYER TOGGLE BUTTONS ---
    function toggleLayer(btn, key, markers) {
        layerState[key] = !layerState[key];
        btn.className = 'layer-btn ' + (layerState[key] ? 'on' : 'off');
        if (Array.isArray(markers)) {
            markers.forEach(function(m) { m.getElement().style.display = layerState[key] ? '' : 'none'; });
        } else if (markers && typeof markers === 'object') {
            Object.keys(markers).forEach(function(id) { markers[id].getElement().style.display = layerState[key] ? '' : 'none'; });
        }
    }

    document.getElementById('toggle-drivers').addEventListener('click', function() { toggleLayer(this, 'drivers', driverMarkers); });
    document.getElementById('toggle-clients').addEventListener('click', function() { toggleLayer(this, 'clients', clientMarkers); });
    document.getElementById('toggle-vendors').addEventListener('click', function() { toggleLayer(this, 'vendors', vendorMarkers); });
    document.getElementById('toggle-zones').addEventListener('click', function() {
        layerState.zones = !layerState.zones;
        this.className = 'layer-btn ' + (layerState.zones ? 'on' : 'off');
        if (layerState.zones) addZoneLayers(); else removeZoneLayers();
    });

    // --- LIVE REFRESH ---
    var refreshUrl = "{{ route('admin.tracking.drivers.json') }}";
    var refreshStatusEl = document.getElementById('refresh-status');
    var refreshCount = 15;
    setInterval(function() {
        refreshCount--;
        if (refreshCount <= 0) {
            refreshCount = 15;
            fetch(refreshUrl, {headers:{'Accept':'application/json'}})
                .then(function(r) { return r.json(); })
                .then(function(data) { renderDriverMarkers(data); if (refreshStatusEl) refreshStatusEl.textContent = 'Updated just now'; })
                .catch(function() { if (refreshStatusEl) refreshStatusEl.textContent = 'Refresh failed'; });
        }
        if (refreshStatusEl) refreshStatusEl.textContent = 'Next refresh: '+refreshCount+'s';
    }, 1000);
});
</script>
@endpush
