@extends('layouts.admin')
@section('title', 'Track Order ' . $order->order_number)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #order-map { width:100%; height:350px; border-radius:var(--radius); border:1px solid var(--glass-border); background:var(--card); }
    .leaflet-popup-content-wrapper { background:var(--card) !important; color:#fff !important; border-radius:var(--radius) !important; border:1px solid var(--glass-border) !important; }
    .leaflet-popup-tip { background:var(--card) !important; }
    .leaflet-popup-content { margin:12px 16px !important; font-size:0.8125rem !important; }
    .timeline-step { display:flex; gap:0.75rem; align-items:flex-start; padding:0.4rem 0; }
    .timeline-dot { width:12px; height:12px; border-radius:50%; margin-top:4px; flex-shrink:0; border:2px solid rgba(255,255,255,0.3); }
    .timeline-dot--done { background:var(--success, #27ae60); border-color:var(--success, #27ae60); }
    .timeline-dot--current { background:var(--primary, #f97316); border-color:var(--primary, #f97316); box-shadow:0 0 8px var(--primary, #f97316); }
    .timeline-dot--pending { background:transparent; }
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
    <div class="card-body" style="padding:0.75rem;"><div id="order-map"></div></div>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var locs=@json($locations->map(fn($l)=>['lat'=>(float)$l->lat,'lng'=>(float)$l->lng,'speed'=>$l->speed,'time'=>$l->recorded_at->format('H:i:s')])->values());
    if(!locs.length)return;
    var latest=locs[0],map=L.map('order-map',{attributionControl:false}).setView([latest.lat,latest.lng],14);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',{maxZoom:19,subdomains:'abcd'}).addTo(map);
    var path=locs.slice().reverse().map(function(l){return[l.lat,l.lng];});
    L.polyline(path,{color:'#f97316',weight:3,opacity:0.7}).addTo(map);
    var ci=L.divIcon({className:'',html:'<div style="width:16px;height:16px;border-radius:50%;background:#22c55e;border:3px solid rgba(255,255,255,0.9);box-shadow:0 0 16px #22c55e;"></div>',iconSize:[16,16],iconAnchor:[8,8]});
    L.marker([latest.lat,latest.lng],{icon:ci}).bindPopup('<strong>Latest</strong><br>'+latest.time+'<br>'+(latest.speed>0?latest.speed+' km/h':'Stopped')).addTo(map);
    if(locs.length>1){var s=locs[locs.length-1];var si=L.divIcon({className:'',html:'<div style="width:10px;height:10px;border-radius:50%;background:#737373;border:2px solid rgba(255,255,255,0.6);"></div>',iconSize:[10,10],iconAnchor:[5,5]});L.marker([s.lat,s.lng],{icon:si}).bindPopup('<strong>Start</strong><br>'+s.time).addTo(map);}
    if(path.length>1)map.fitBounds(path,{padding:[30,30],maxZoom:16});
});
</script>
@endpush
@endif
