@extends('layouts.admin')
@section('title', 'Track Drivers')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .tracking-tabs {
        display:flex; gap:0.25rem; margin-bottom:1.5rem;
        background:rgba(255,255,255,0.04); border-radius:var(--radius);
        padding:4px; border:1px solid var(--glass-border); width:fit-content;
    }
    .tracking-tab {
        padding:0.55rem 1.25rem; border-radius:var(--radius-sm);
        font-size:0.8125rem; font-weight:600; cursor:pointer;
        color:var(--text-muted); background:transparent; border:none;
        transition:all var(--transition-fast); display:flex; align-items:center; gap:0.5rem;
    }
    .tracking-tab:hover { color:var(--text); background:rgba(255,255,255,0.05); }
    .tracking-tab.active {
        color:#fff; background:var(--primary);
        box-shadow:0 2px 8px rgba(249,115,22,0.3);
    }
    .tracking-tab svg { width:16px; height:16px; }
    .tab-pane { display:none; }
    .tab-pane.active { display:block; }

    /* Map */
    #drivers-map {
        width:100%; height:520px; border-radius:var(--radius);
        border:1px solid var(--glass-border); background:var(--card);
    }
    .map-legend {
        display:flex; gap:1.25rem; padding:0.75rem 1rem; margin-top:0.75rem;
        background:rgba(255,255,255,0.03); border-radius:var(--radius-sm);
        border:1px solid var(--glass-border); font-size:0.75rem; color:var(--text-muted);
        flex-wrap:wrap;
    }
    .map-legend-item { display:flex; align-items:center; gap:0.4rem; }
    .map-legend-dot {
        width:10px; height:10px; border-radius:50%; flex-shrink:0;
        border:2px solid rgba(255,255,255,0.3);
    }
    .map-legend-dot--active { background:#22c55e; }
    .map-legend-dot--idle { background:#737373; }

    /* Leaflet popup override for dark theme */
    .leaflet-popup-content-wrapper {
        background:var(--card) !important; color:#fff !important;
        border-radius:var(--radius) !important; border:1px solid var(--glass-border) !important;
        box-shadow:var(--shadow-md) !important;
    }
    .leaflet-popup-tip { background:var(--card) !important; }
    .leaflet-popup-content { margin:12px 16px !important; font-size:0.8125rem !important; line-height:1.5 !important; }
    .leaflet-popup-content a { color:var(--primary) !important; text-decoration:none !important; font-weight:600; }
    .leaflet-popup-content a:hover { text-decoration:underline !important; }
    .leaflet-popup-close-button { color:var(--text-muted) !important; }
    .driver-popup-name { font-weight:700; font-size:0.875rem; margin-bottom:4px; }
    .driver-popup-status { display:inline-block; padding:2px 8px; border-radius:100px; font-size:0.6875rem; font-weight:600; margin-bottom:6px; }
    .driver-popup-status--active { background:rgba(34,197,94,0.15); color:#22c55e; }
    .driver-popup-status--idle { background:rgba(255,255,255,0.08); color:#a3a3a3; }
    .driver-popup-meta { color:var(--text-muted); font-size:0.75rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>&#9737; Track Drivers</h1>
    <p class="text-muted">Real-time overview of all driver locations and active deliveries.</p>
</div>

{{-- Tabs --}}
<div class="tracking-tabs">
    <button class="tracking-tab active" data-tab="map">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
        Map
    </button>
    <button class="tracking-tab" data-tab="list">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        List
    </button>
</div>

{{-- Map Tab --}}
<div class="tab-pane active" id="tab-map">
    <div class="card">
        <div class="card-header">
            <span>Driver Locations</span>
            <span class="text-muted" style="font-size:0.75rem;">{{ $activeDrivers->count() }} active &middot; {{ $idleDrivers->count() }} idle</span>
        </div>
        <div class="card-body" style="padding:0.75rem;">
            <div id="drivers-map"></div>
            <div class="map-legend">
                <div class="map-legend-item"><span class="map-legend-dot map-legend-dot--active"></span> Active (on delivery)</div>
                <div class="map-legend-item"><span class="map-legend-dot map-legend-dot--idle"></span> Idle (available)</div>
            </div>
        </div>
    </div>
</div>

{{-- List Tab --}}
<div class="tab-pane" id="tab-list">
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
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    document.querySelectorAll('.tracking-tab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tracking-tab').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.tab-pane').forEach(function(p) { p.classList.remove('active'); });
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
            if (btn.dataset.tab === 'map' && window._driversMap) {
                window._driversMap.invalidateSize();
            }
        });
    });

    // Driver data from server
    var drivers = @json(
        $activeDrivers->merge($idleDrivers)->filter(fn($d) => $d->last_location !== null)->map(fn($d) => [
            'id'        => $d->id,
            'name'      => $d->name,
            'phone'     => $d->phone,
            'lat'       => (float) $d->last_location->lat,
            'lng'       => (float) $d->last_location->lng,
            'speed'     => $d->last_location->speed ? round($d->last_location->speed) : 0,
            'updated'   => $d->last_location->recorded_at->diffForHumans(),
            'active'    => $d->active_order !== null,
            'status'    => $d->active_order ? str_replace('_', ' ', $d->active_order->status) : 'Idle',
            'order'     => $d->active_order ? $d->active_order->order_number : null,
            'orderUrl'  => $d->active_order ? route('admin.orders.show', $d->active_order) : null,
            'detailUrl' => route('admin.tracking.driver-detail', $d),
        ])->values()
    );

    // Default center: South Africa
    var defaultCenter = [-29.0, 25.0];
    var defaultZoom = 6;

    // If we have drivers with GPS, fit to their bounds
    var map = L.map('drivers-map', {
        zoomControl: true,
        attributionControl: false
    }).setView(defaultCenter, defaultZoom);

    window._driversMap = map;

    // Dark tile layer
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        subdomains: 'abcd'
    }).addTo(map);

    if (drivers.length === 0) return;

    // Custom marker icons
    function createIcon(color) {
        return L.divIcon({
            className: '',
            html: '<div style="width:14px;height:14px;border-radius:50%;background:' + color + ';border:3px solid rgba(255,255,255,0.8);box-shadow:0 0 12px ' + color + ', 0 2px 6px rgba(0,0,0,0.4);"></div>',
            iconSize: [14, 14],
            iconAnchor: [7, 7],
            popupAnchor: [0, -12]
        });
    }

    var activeIcon = createIcon('#22c55e');
    var idleIcon = createIcon('#737373');
    var bounds = L.latLngBounds();

    drivers.forEach(function(d) {
        var latlng = L.latLng(d.lat, d.lng);
        bounds.extend(latlng);

        var statusClass = d.active ? 'active' : 'idle';
        var popup = '<div class="driver-popup-name">' + d.name + '</div>' +
            '<span class="driver-popup-status driver-popup-status--' + statusClass + '">' + d.status + '</span><br>' +
            (d.order ? '<div style="margin:4px 0;">Order: <a href="' + d.orderUrl + '">' + d.order + '</a></div>' : '') +
            (d.speed > 0 ? '<div class="driver-popup-meta">' + d.speed + ' km/h</div>' : '') +
            '<div class="driver-popup-meta">Updated ' + d.updated + '</div>' +
            '<div style="margin-top:8px;"><a href="' + d.detailUrl + '">View details &rarr;</a></div>';

        L.marker(latlng, { icon: d.active ? activeIcon : idleIcon })
            .bindPopup(popup)
            .addTo(map);
    });

    // Fit map to show all drivers
    if (bounds.isValid()) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
    }
});
</script>
@endpush
