@extends('layouts.admin')
@section('title', 'Operations Board')

@section('content')
<div class="page-header">
    <h1>Operations Board</h1>
    <div style="display:flex; gap:0.5rem; align-items:center;">
        @if($totalAlerts > 0)
            <span class="badge badge-danger" style="font-size:0.85rem;">{{ $totalAlerts }} active alerts</span>
        @else
            <span class="badge badge-success" style="font-size:0.85rem;">All clear</span>
        @endif
        <a href="{{ route('admin.tracking.drivers') }}" class="btn btn-outline btn-sm">Track Drivers</a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">All Orders</a>
    </div>
</div>

{{-- Today's Metrics --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Orders Today</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $todayMetrics['orders_placed'] }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Delivered</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $todayMetrics['orders_delivered'] }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">In Transit</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--warning, #e67e22);">{{ $todayMetrics['orders_in_transit'] }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Active Drivers</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $todayMetrics['active_drivers'] }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Revenue Today</h6><h3 class="mb-0" style="margin-top:0.25rem;">R {{ number_format($todayMetrics['revenue_today'], 0) }}</h3></div></div>
</div>

{{-- Unassigned Orders --}}
<div class="card mb-2" @if($unassigned->count() > 0) style="border-left:3px solid var(--danger, #e74c3c);" @endif>
    <div class="card-header">
        <span style="color:var(--danger, #e74c3c); font-weight:700;">Unassigned Orders</span>
        <span class="badge badge-{{ $unassigned->count() > 0 ? 'danger' : 'success' }}">{{ $unassigned->count() }}</span>
    </div>
    @if($unassigned->isEmpty())
        <div class="card-body"><p class="text-muted mb-0">All orders have drivers assigned.</p></div>
    @else
        <div class="table-responsive"><table><thead><tr><th>Order</th><th>Customer</th><th>Created</th><th class="text-right">Total</th><th>Quick Assign</th></tr></thead><tbody>
            @foreach($unassigned as $order)
            <tr>
                <td><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold">{{ $order->order_number }}</a></td>
                <td>{{ $order->customer->user->name }}</td>
                <td><small>{{ $order->created_at->diffForHumans() }}</small></td>
                <td class="text-right font-semibold">R{{ number_format($order->total, 2) }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.orders.assign-driver', $order) }}" style="display:flex;gap:0.25rem;">@csrf
                        <select name="driver_id" class="form-control" style="max-width:140px;font-size:0.8rem;" required><option value="">Driver...</option>@foreach($availableDrivers as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
                        <button type="submit" class="btn btn-sm btn-primary">Assign</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>

{{-- Assigned not loaded --}}
<div class="card mb-2" @if($assignedNotLoaded->count() > 0) style="border-left:3px solid var(--warning, #e67e22);" @endif>
    <div class="card-header">
        <span style="color:var(--warning, #e67e22); font-weight:700;">Assigned > 60 min, Not Loaded</span>
        <span class="badge badge-{{ $assignedNotLoaded->count() > 0 ? 'warning' : 'success' }}">{{ $assignedNotLoaded->count() }}</span>
    </div>
    @if($assignedNotLoaded->isEmpty())
        <div class="card-body"><p class="text-muted mb-0">No stuck assignments.</p></div>
    @else
        <div class="table-responsive"><table><thead><tr><th>Order</th><th>Driver</th><th>Status</th><th>Since</th><th class="text-right">Actions</th></tr></thead><tbody>
            @foreach($assignedNotLoaded as $order)
            <tr>
                <td><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold">{{ $order->order_number }}</a></td>
                <td>{{ $order->driver?->name ?? 'N/A' }}</td>
                <td><span class="badge badge-warning">{{ str_replace('_', ' ', $order->status) }}</span></td>
                <td><small>{{ $order->updated_at->diffForHumans() }}</small></td>
                <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline">Review</a></td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>

{{-- No tracking --}}
<div class="card mb-2" @if($enRouteNoTracking->count() > 0) style="border-left:3px solid var(--warning, #e67e22);" @endif>
    <div class="card-header">
        <span style="color:var(--warning, #e67e22); font-weight:700;">En Route, No Tracking > 10 min</span>
        <span class="badge badge-{{ $enRouteNoTracking->count() > 0 ? 'warning' : 'success' }}">{{ $enRouteNoTracking->count() }}</span>
    </div>
    @if($enRouteNoTracking->isEmpty())
        <div class="card-body"><p class="text-muted mb-0">All en-route orders have recent tracking.</p></div>
    @else
        <div class="table-responsive"><table><thead><tr><th>Order</th><th>Driver</th><th>Last Update</th><th class="text-right">Actions</th></tr></thead><tbody>
            @foreach($enRouteNoTracking as $order)
            <tr>
                <td><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold">{{ $order->order_number }}</a></td>
                <td>{{ $order->driver?->name ?? 'N/A' }}</td>
                <td>@php $last = $order->driverLocations()->orderBy('recorded_at','desc')->first(); @endphp<small>{{ $last ? $last->recorded_at->diffForHumans() : 'Never' }}</small></td>
                <td class="text-right"><a href="{{ route('admin.tracking.order', $order) }}" class="btn btn-sm btn-primary">Track</a></td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>

{{-- Delivered not invoiced --}}
<div class="card mb-2" @if($deliveredNotInvoiced->count() > 0) style="border-left:3px solid var(--danger, #e74c3c);" @endif>
    <div class="card-header">
        <span style="color:var(--danger, #e74c3c); font-weight:700;">Delivered, No Invoice</span>
        <span class="badge badge-{{ $deliveredNotInvoiced->count() > 0 ? 'danger' : 'success' }}">{{ $deliveredNotInvoiced->count() }}</span>
    </div>
    @if($deliveredNotInvoiced->isEmpty())
        <div class="card-body"><p class="text-muted mb-0">All delivered orders have invoices.</p></div>
    @else
        <div class="table-responsive"><table><thead><tr><th>Order</th><th>Customer</th><th>Delivered</th><th class="text-right">Total</th><th class="text-right">Actions</th></tr></thead><tbody>
            @foreach($deliveredNotInvoiced as $order)
            <tr>
                <td><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold">{{ $order->order_number }}</a></td>
                <td>{{ $order->customer->user->name }}</td>
                <td><small>{{ $order->updated_at->diffForHumans() }}</small></td>
                <td class="text-right font-semibold">R{{ number_format($order->total, 2) }}</td>
                <td class="text-right"><form method="POST" action="{{ route('admin.orders.resend-invoice', $order) }}" style="display:inline;">@csrf<button type="submit" class="btn btn-sm btn-primary">Generate</button></form></td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>

{{-- Stale pending --}}
<div class="card mb-2">
    <div class="card-header">
        <span>Pending Payment > 24h</span>
        <span class="badge badge-secondary">{{ $stalePending->count() }}</span>
    </div>
    @if($stalePending->isEmpty())
        <div class="card-body"><p class="text-muted mb-0">No stale pending orders.</p></div>
    @else
        <div class="table-responsive"><table><thead><tr><th>Order</th><th>Customer</th><th>Created</th><th class="text-right">Total</th><th class="text-right">Actions</th></tr></thead><tbody>
            @foreach($stalePending as $order)
            <tr>
                <td><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold">{{ $order->order_number }}</a></td>
                <td>{{ $order->customer->user->name }}</td>
                <td><small>{{ $order->created_at->diffForHumans() }}</small></td>
                <td class="text-right font-semibold">R{{ number_format($order->total, 2) }}</td>
                <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline">Review</a></td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>
@endsection
