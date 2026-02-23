@extends('layouts.admin')
@section('title', 'Track Clients')

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet" />
<style>
    .tracking-tabs { display:flex; gap:0.25rem; margin-bottom:1.5rem; background:rgba(255,255,255,0.04); border-radius:var(--radius); padding:4px; border:1px solid var(--glass-border); width:fit-content; }
    .tracking-tab { padding:0.55rem 1.25rem; border-radius:var(--radius-sm); font-size:0.8125rem; font-weight:600; cursor:pointer; color:var(--text-muted); background:transparent; border:none; transition:all var(--transition-fast); display:flex; align-items:center; gap:0.5rem; text-decoration:none; }
    .tracking-tab:hover { color:var(--text); background:rgba(255,255,255,0.05); }
    .tracking-tab.active { color:#fff; background:var(--primary); box-shadow:0 2px 8px rgba(249,115,22,0.3); }
    .tracking-tab svg { width:16px; height:16px; }
    #clients-map { width:100%; height:520px; border-radius:var(--radius); border:1px solid var(--glass-border); background:var(--card); }
    .map-legend { display:flex; gap:1.25rem; padding:0.75rem 1rem; margin-top:0.75rem; background:rgba(255,255,255,0.03); border-radius:var(--radius-sm); border:1px solid var(--glass-border); font-size:0.75rem; color:var(--text-muted); flex-wrap:wrap; }
    .map-legend-item { display:flex; align-items:center; gap:0.4rem; }
    .map-legend-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; border:2px solid rgba(255,255,255,0.3); }
    .client-marker { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:11px; color:#000; border:3px solid rgba(255,255,255,0.9); box-shadow:0 0 12px var(--marker-glow), 0 2px 8px rgba(0,0,0,0.5); position:relative; text-transform:uppercase; font-family:system-ui,-apple-system,sans-serif; letter-spacing:-0.5px; cursor:pointer; transition:transform 0.15s; }
    .client-marker:hover { transform:scale(1.15); }
    .vendor-marker { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; color:#fff; border:3px solid rgba(255,255,255,0.9); box-shadow:0 0 16px #ef4444, 0 2px 8px rgba(0,0,0,0.5); background:#ef4444; cursor:pointer; }
    .mapboxgl-popup-content { background:var(--card) !important; color:#fff !important; border-radius:var(--radius) !important; border:1px solid var(--glass-border) !important; box-shadow:var(--shadow-md) !important; padding:12px 16px !important; font-size:0.8125rem !important; line-height:1.5 !important; }
    .mapboxgl-popup-tip { border-top-color:var(--card) !important; }
    .mapboxgl-popup-close-button { color:var(--text-muted) !important; font-size:1.2rem; padding:4px 8px; }
    .mapboxgl-popup-content a { color:var(--primary) !important; text-decoration:none !important; font-weight:600; }
    .mapboxgl-popup-content a:hover { text-decoration:underline !important; }
    .client-popup-name { font-weight:700; font-size:0.875rem; margin-bottom:4px; }
    .client-popup-meta { color:var(--text-muted); font-size:0.75rem; }
    .client-popup-distance { display:inline-block; padding:2px 8px; border-radius:100px; font-size:0.6875rem; font-weight:700; margin:4px 0; }
    .map-style-toggle { position:absolute; top:10px; right:10px; z-index:2; display:flex; gap:4px; background:rgba(0,0,0,0.7); border-radius:var(--radius-sm); padding:4px; backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.1); }
    .map-style-btn { padding:6px 12px; border:none; border-radius:var(--radius-sm); font-size:0.7rem; font-weight:600; cursor:pointer; color:rgba(255,255,255,0.7); background:transparent; transition:all 0.2s; text-transform:uppercase; letter-spacing:0.5px; }
    .map-style-btn:hover { color:#fff; background:rgba(255,255,255,0.1); }
    .map-style-btn.active { color:#fff; background:var(--primary); box-shadow:0 2px 8px rgba(249,115,22,0.3); }
    .zone-toggle { position:absolute; top:10px; left:10px; z-index:2; display:flex; gap:4px; background:rgba(0,0,0,0.7); border-radius:var(--radius-sm); padding:4px; backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.1); }
    .zone-toggle-btn { padding:6px 12px; border:none; border-radius:var(--radius-sm); font-size:0.7rem; font-weight:600; cursor:pointer; color:rgba(255,255,255,0.7); background:transparent; transition:all 0.2s; }
    .zone-toggle-btn:hover { color:#fff; background:rgba(255,255,255,0.1); }
    .zone-toggle-btn.active { color:#fff; background:rgba(255,255,255,0.15); }
    .distance-band-bar { display:flex; align-items:center; gap:0.5rem; margin-bottom:0.4rem; }
    .distance-band-color { width:14px; height:14px; border-radius:3px; flex-shrink:0; }
    .distance-band-label { font-size:0.75rem; color:var(--text-muted); min-width:70px; }
    .distance-band-count { font-size:0.8rem; font-weight:700; min-width:28px; text-align:right; }
    .distance-band-fill { height:18px; border-radius:3px; min-width:2px; transition:width 0.4s ease; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Track Clients</h1>
    <div style="display:flex; gap:0.5rem; align-items:center;">
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline btn-sm">Manage Customers</a>
    </div>
</div>

{{-- Summary --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Addresses</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $totalWithGps + $totalWithoutGps }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">With GPS</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $totalWithGps }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">No GPS</h6><h3 class="mb-0" style="margin-top:0.25rem; {{ $totalWithoutGps > 0 ? 'color:var(--danger, #e74c3c);' : '' }}">{{ $totalWithoutGps }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Depots</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $mapVendors->count() }}</h3></div></div>
</div>

<div class="tracking-tabs">
    <a href="{{ route('admin.tracking.drivers') }}" class="tracking-tab"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg> Drivers</a>
    <button class="tracking-tab active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Clients</button>
</div>

<div class="form-row" style="align-items:flex-start;">
    <div style="flex:3;">
        <div class="card">
            <div class="card-header"><span>Client Locations by Distance</span><span class="text-muted" style="font-size:0.75rem;">{{ $totalWithGps }} clients mapped</span></div>
            <div class="card-body" style="padding:0.75rem; position:relative;">
                <div class="map-style-toggle">
                    <button class="map-style-btn active" data-style="streets">Streets</button>
                    <button class="map-style-btn" data-style="satellite">Satellite</button>
                    <button class="map-style-btn" data-style="terrain">Terrain</button>
                </div>
                <div class="zone-toggle">
                    <button class="zone-toggle-btn active" id="toggle-zones">Distance Zones</button>
                </div>
                <div id="clients-map"></div>
                <div class="map-legend">
                    <div class="map-legend-item"><span class="map-legend-dot" style="background:#FFD600;"></span> 0-10 km</div>
                    <div class="map-legend-item"><span class="map-legend-dot" style="background:#FFC000;"></span> 10-20 km</div>
                    <div class="map-legend-item"><span class="map-legend-dot" style="background:#FFA000;"></span> 20-30 km</div>
                    <div class="map-legend-item"><span class="map-legend-dot" style="background:#FF7A00;"></span> 30-40 km</div>
                    <div class="map-legend-item"><span class="map-legend-dot" style="background:#FF5200;"></span> 40-50 km</div>
                    <div class="map-legend-item"><span class="map-legend-dot" style="background:#E63900;"></span> 50+ km</div>
                    <div class="map-legend-item"><span class="map-legend-dot" style="background:#ef4444;"></span> Depot</div>
                </div>
            </div>
        </div>
    </div>
    <div style="flex:1; min-width:220px;">
        <div class="card">
            <div class="card-header">Distance Breakdown</div>
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
                <div style="font-size:0.75rem; color:var(--text-muted);">
                    Distance is calculated from the nearest depot/vendor to each client delivery address.
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Filter by Distance</div>
            <div class="card-body" style="padding:0.75rem;">
                <div style="display:flex; flex-direction:column; gap:0.35rem;">
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.8rem; cursor:pointer;">
                        <input type="checkbox" class="distance-filter" value="0-10" checked> <span class="distance-band-color" style="background:#FFD600; display:inline-block;"></span> 0-10 km
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.8rem; cursor:pointer;">
                        <input type="checkbox" class="distance-filter" value="10-20" checked> <span class="distance-band-color" style="background:#FFC000; display:inline-block;"></span> 10-20 km
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.8rem; cursor:pointer;">
                        <input type="checkbox" class="distance-filter" value="20-30" checked> <span class="distance-band-color" style="background:#FFA000; display:inline-block;"></span> 20-30 km
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.8rem; cursor:pointer;">
                        <input type="checkbox" class="distance-filter" value="30-40" checked> <span class="distance-band-color" style="background:#FF7A00; display:inline-block;"></span> 30-40 km
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.8rem; cursor:pointer;">
                        <input type="checkbox" class="distance-filter" value="40-50" checked> <span class="distance-band-color" style="background:#FF5200; display:inline-block;"></span> 40-50 km
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.8rem; cursor:pointer;">
                        <input type="checkbox" class="distance-filter" value="50+" checked> <span class="distance-band-color" style="background:#E63900; display:inline-block;"></span> 50+ km
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var clients = @json($mapClients);
    var vendors = @json($mapVendors);
    var mapboxToken = @json(config('services.mapbox.token'));

    mapboxgl.accessToken = mapboxToken;

    var mapStyles = {
        streets: 'mapbox://styles/mapbox/dark-v11',
        satellite: 'mapbox://styles/mapbox/satellite-streets-v12',
        terrain: 'mapbox://styles/mapbox/outdoors-v12'
    };

    var map = new mapboxgl.Map({
        container: 'clients-map',
        style: mapStyles.streets,
        center: [25.0, -29.0],
        zoom: 6,
        attributionControl: false
    });

    map.addControl(new mapboxgl.NavigationControl(), 'bottom-right');

    // Style toggle
    document.querySelectorAll('.map-style-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.map-style-btn').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            map.setStyle(mapStyles[btn.dataset.style]);
            map.once('style.load', function() { addZoneLayers(); });
        });
    });

    // Distance band color mapping
    var distanceBands = [
        { min: 0,  max: 10, color: '#FFD600', label: '0-10 km' },
        { min: 10, max: 20, color: '#FFC000', label: '10-20 km' },
        { min: 20, max: 30, color: '#FFA000', label: '20-30 km' },
        { min: 30, max: 40, color: '#FF7A00', label: '30-40 km' },
        { min: 40, max: 50, color: '#FF5200', label: '40-50 km' },
        { min: 50, max: 99999, color: '#E63900', label: '50+ km' }
    ];

    function getDistanceBand(distance) {
        if (distance === null) return distanceBands[distanceBands.length - 1];
        for (var i = 0; i < distanceBands.length; i++) {
            if (distance >= distanceBands[i].min && distance < distanceBands[i].max) {
                return distanceBands[i];
            }
        }
        return distanceBands[distanceBands.length - 1];
    }

    function getFilterKey(distance) {
        if (distance === null) return '50+';
        if (distance < 10) return '0-10';
        if (distance < 20) return '10-20';
        if (distance < 30) return '20-30';
        if (distance < 40) return '30-40';
        if (distance < 50) return '40-50';
        return '50+';
    }

    function getInitials(name) {
        var parts = (name || '?').trim().split(/\s+/);
        if (parts.length >= 2) return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
        return parts[0].charAt(0).toUpperCase();
    }

    // Store markers for filtering
    var clientMarkers = [];
    var vendorMarkers = [];
    var bounds = new mapboxgl.LngLatBounds();
    var zonesVisible = true;

    // Vendor markers (red depot markers)
    vendors.forEach(function(v) {
        bounds.extend([v.lng, v.lat]);
        var initials = getInitials(v.name);
        var el = document.createElement('div');
        el.innerHTML = '<div class="vendor-marker">' + initials + '</div>';
        var popup = new mapboxgl.Popup({ offset: 20, closeButton: true })
            .setHTML('<div class="client-popup-name" style="color:#ef4444;">&#128666; ' + v.name + '</div>' +
                '<div class="client-popup-meta">' + v.address + '</div>' +
                '<div class="client-popup-meta" style="margin-top:4px; font-weight:600;">Depot / Vendor</div>');
        var marker = new mapboxgl.Marker({ element: el, anchor: 'center' }).setLngLat([v.lng, v.lat]).setPopup(popup).addTo(map);
        vendorMarkers.push(marker);
    });

    // Client markers with distance-based colors
    clients.forEach(function(c) {
        bounds.extend([c.lng, c.lat]);
        var band = getDistanceBand(c.distance);
        var initials = getInitials(c.name);
        var el = document.createElement('div');
        el.innerHTML = '<div class="client-marker" style="background:' + band.color + '; --marker-glow:' + band.color + ';">' + initials + '</div>';

        var distLabel = c.distance !== null ? c.distance.toFixed(1) + ' km' : 'Unknown';
        var popup = new mapboxgl.Popup({ offset: 20, closeButton: true })
            .setHTML(
                '<div class="client-popup-name">' + c.name + '</div>' +
                (c.label ? '<div class="client-popup-meta" style="margin-bottom:4px;"><strong>' + c.label + '</strong></div>' : '') +
                '<div class="client-popup-distance" style="background:' + band.color + '; color:#000;">' + distLabel + ' &middot; ' + band.label + '</div>' +
                '<div class="client-popup-meta">' + c.address + '</div>' +
                '<div style="margin-top:8px;"><a href="' + c.customerUrl + '">View customer &rarr;</a></div>'
            );

        var marker = new mapboxgl.Marker({ element: el, anchor: 'center' }).setLngLat([c.lng, c.lat]).setPopup(popup).addTo(map);
        marker._distanceFilter = getFilterKey(c.distance);
        clientMarkers.push(marker);
    });

    if (clients.length || vendors.length) {
        map.fitBounds(bounds, { padding: 40, maxZoom: 14 });
    }

    // Distance zone circles around vendors
    function addZoneLayers() {
        if (!zonesVisible) return;
        var zoneRadii = [50, 40, 30, 20, 10]; // draw outer first so inner overlays
        var zoneColors = ['#E63900', '#FF5200', '#FF7A00', '#FFA000', '#FFC000', '#FFD600'];

        vendors.forEach(function(v, vi) {
            zoneRadii.forEach(function(radius, ri) {
                var circleId = 'zone-' + vi + '-' + radius;
                var circleData = createGeoJSONCircle([v.lng, v.lat], radius);

                if (map.getSource(circleId)) {
                    map.getSource(circleId).setData(circleData);
                } else {
                    map.addSource(circleId, { type: 'geojson', data: circleData });
                    map.addLayer({
                        id: circleId + '-fill',
                        type: 'fill',
                        source: circleId,
                        paint: {
                            'fill-color': zoneColors[ri],
                            'fill-opacity': 0.06
                        }
                    });
                    map.addLayer({
                        id: circleId + '-line',
                        type: 'line',
                        source: circleId,
                        paint: {
                            'line-color': zoneColors[ri],
                            'line-width': 1.5,
                            'line-opacity': 0.35,
                            'line-dasharray': [3, 2]
                        }
                    });
                }
            });
        });
    }

    function removeZoneLayers() {
        vendors.forEach(function(v, vi) {
            [50, 40, 30, 20, 10].forEach(function(radius) {
                var circleId = 'zone-' + vi + '-' + radius;
                if (map.getLayer(circleId + '-fill')) map.removeLayer(circleId + '-fill');
                if (map.getLayer(circleId + '-line')) map.removeLayer(circleId + '-line');
                if (map.getSource(circleId)) map.removeSource(circleId);
            });
        });
    }

    // Create a GeoJSON circle polygon (approximation)
    function createGeoJSONCircle(center, radiusKm, points) {
        points = points || 64;
        var coords = { latitude: center[1], longitude: center[0] };
        var ret = [];
        var distanceX = radiusKm / (111.32 * Math.cos(coords.latitude * Math.PI / 180));
        var distanceY = radiusKm / 110.574;

        for (var i = 0; i < points; i++) {
            var theta = (i / points) * (2 * Math.PI);
            var x = distanceX * Math.cos(theta);
            var y = distanceY * Math.sin(theta);
            ret.push([coords.longitude + x, coords.latitude + y]);
        }
        ret.push(ret[0]); // close the polygon

        return {
            type: 'Feature',
            geometry: { type: 'Polygon', coordinates: [ret] }
        };
    }

    map.on('load', function() {
        addZoneLayers();
    });

    // Toggle distance zones
    document.getElementById('toggle-zones').addEventListener('click', function() {
        this.classList.toggle('active');
        zonesVisible = !zonesVisible;
        if (zonesVisible) {
            addZoneLayers();
        } else {
            removeZoneLayers();
        }
    });

    // Filter by distance band checkboxes
    document.querySelectorAll('.distance-filter').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var activeFilters = [];
            document.querySelectorAll('.distance-filter:checked').forEach(function(el) {
                activeFilters.push(el.value);
            });

            clientMarkers.forEach(function(marker) {
                var el = marker.getElement();
                if (activeFilters.indexOf(marker._distanceFilter) !== -1) {
                    el.style.display = '';
                } else {
                    el.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endpush
