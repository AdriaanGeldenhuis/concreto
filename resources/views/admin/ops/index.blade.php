@extends('layouts.admin')
@section('title', 'Operations Board')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <h1 style="font-size: 24px; margin-bottom:4px;">Operations Board</h1>
            @if($totalAlerts > 0)
                <span class="badge badge-danger" style="font-size:0.9rem;">{{ $totalAlerts }} active alerts</span>
            @else
                <span class="badge badge-success" style="font-size:0.9rem;">All clear</span>
            @endif
        </div>
    </div>

    {{-- Today's Metrics --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:1rem; margin-bottom:24px;">
        <div style="background:var(--card,#fff); border:1px solid var(--border,#e1e8ed); border-radius:8px; padding:16px; text-align:center;">
            <div style="color:var(--text-light,#95a5a6); font-size:0.75rem;">Orders Today</div>
            <div style="font-size:1.5rem; font-weight:700;">{{ $todayMetrics['orders_placed'] }}</div>
        </div>
        <div style="background:var(--card,#fff); border:1px solid var(--border,#e1e8ed); border-radius:8px; padding:16px; text-align:center;">
            <div style="color:var(--text-light,#95a5a6); font-size:0.75rem;">Delivered</div>
            <div style="font-size:1.5rem; font-weight:700; color:var(--success,#27ae60);">{{ $todayMetrics['orders_delivered'] }}</div>
        </div>
        <div style="background:var(--card,#fff); border:1px solid var(--border,#e1e8ed); border-radius:8px; padding:16px; text-align:center;">
            <div style="color:var(--text-light,#95a5a6); font-size:0.75rem;">In Transit</div>
            <div style="font-size:1.5rem; font-weight:700; color:var(--warning,#e67e22);">{{ $todayMetrics['orders_in_transit'] }}</div>
        </div>
        <div style="background:var(--card,#fff); border:1px solid var(--border,#e1e8ed); border-radius:8px; padding:16px; text-align:center;">
            <div style="color:var(--text-light,#95a5a6); font-size:0.75rem;">Active Drivers</div>
            <div style="font-size:1.5rem; font-weight:700;">{{ $todayMetrics['active_drivers'] }}</div>
        </div>
        <div style="background:var(--card,#fff); border:1px solid var(--border,#e1e8ed); border-radius:8px; padding:16px; text-align:center;">
            <div style="color:var(--text-light,#95a5a6); font-size:0.75rem;">Revenue Today</div>
            <div style="font-size:1.5rem; font-weight:700;">R {{ number_format($todayMetrics['revenue_today'], 0) }}</div>
        </div>
    </div>

    {{-- Unassigned --}}
    <div style="background:var(--card,#fff); border:1px solid var(--border,#e1e8ed); border-radius:8px; padding:20px; margin-bottom:20px; {{ $unassigned->count() > 0 ? 'border-left:4px solid var(--danger,#e74c3c);' : '' }}">
        <h2 style="font-size:18px; color:var(--danger,#e74c3c); margin:0 0 12px;">Unassigned Orders ({{ $unassigned->count() }})</h2>
        @if($unassigned->isEmpty())
            <p style="color:var(--text-light,#95a5a6);">All orders have drivers assigned.</p>
        @else
            <table style="width:100%; border-collapse:collapse;">
                <thead><tr style="border-bottom:2px solid var(--border,#e1e8ed);"><th style="padding:8px; text-align:left;">Order</th><th style="padding:8px; text-align:left;">Customer</th><th style="padding:8px; text-align:left;">Created</th><th style="padding:8px; text-align:left;">Total</th><th style="padding:8px;">Quick Assign</th></tr></thead>
                <tbody>
                    @foreach($unassigned as $order)
                    <tr style="border-bottom:1px solid var(--border,#f0f3f5);">
                        <td style="padding:8px;"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td style="padding:8px;">{{ $order->customer->user->name }}</td>
                        <td style="padding:8px;">{{ $order->created_at->diffForHumans() }}</td>
                        <td style="padding:8px;">R{{ number_format($order->total, 2) }}</td>
                        <td style="padding:8px;">
                            <form method="POST" action="{{ route('admin.orders.assign-driver', $order) }}" style="display:flex;gap:4px;">@csrf
                                <select name="driver_id" class="form-control" style="max-width:140px;font-size:0.85rem;" required><option value="">Driver...</option>@foreach($availableDrivers as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
                                <button type="submit" class="btn btn-sm btn-primary">Assign</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Assigned not loaded --}}
    <div style="background:var(--card,#fff); border:1px solid var(--border,#e1e8ed); border-radius:8px; padding:20px; margin-bottom:20px; {{ $assignedNotLoaded->count() > 0 ? 'border-left:4px solid var(--warning,#e67e22);' : '' }}">
        <h2 style="font-size:18px; color:var(--warning,#e67e22); margin:0 0 12px;">Assigned > 60 min, Not Loaded ({{ $assignedNotLoaded->count() }})</h2>
        @if($assignedNotLoaded->isEmpty())
            <p style="color:var(--text-light,#95a5a6);">No stuck assignments.</p>
        @else
            <table style="width:100%; border-collapse:collapse;">
                <thead><tr style="border-bottom:2px solid var(--border,#e1e8ed);"><th style="padding:8px;">Order</th><th style="padding:8px;">Driver</th><th style="padding:8px;">Status</th><th style="padding:8px;">Since</th><th style="padding:8px;"></th></tr></thead>
                <tbody>
                    @foreach($assignedNotLoaded as $order)
                    <tr style="border-bottom:1px solid var(--border,#f0f3f5);"><td style="padding:8px;"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td><td style="padding:8px;">{{ $order->driver?->name ?? 'N/A' }}</td><td style="padding:8px;">{{ $order->status }}</td><td style="padding:8px;">{{ $order->updated_at->diffForHumans() }}</td><td style="padding:8px;"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline">Review</a></td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- No tracking --}}
    <div style="background:var(--card,#fff); border:1px solid var(--border,#e1e8ed); border-radius:8px; padding:20px; margin-bottom:20px; {{ $enRouteNoTracking->count() > 0 ? 'border-left:4px solid var(--warning,#e67e22);' : '' }}">
        <h2 style="font-size:18px; color:var(--warning,#e67e22); margin:0 0 12px;">En Route, No Tracking > 10 min ({{ $enRouteNoTracking->count() }})</h2>
        @if($enRouteNoTracking->isEmpty())
            <p style="color:var(--text-light,#95a5a6);">All en-route orders have recent tracking.</p>
        @else
            <table style="width:100%; border-collapse:collapse;">
                <thead><tr style="border-bottom:2px solid var(--border,#e1e8ed);"><th style="padding:8px;">Order</th><th style="padding:8px;">Driver</th><th style="padding:8px;">Last Update</th><th style="padding:8px;"></th></tr></thead>
                <tbody>
                    @foreach($enRouteNoTracking as $order)
                    <tr style="border-bottom:1px solid var(--border,#f0f3f5);"><td style="padding:8px;"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td><td style="padding:8px;">{{ $order->driver?->name ?? 'N/A' }}</td><td style="padding:8px;">@php $last = $order->driverLocations()->orderBy('recorded_at','desc')->first(); @endphp{{ $last ? $last->recorded_at->diffForHumans() : 'Never' }}</td><td style="padding:8px;"><a href="{{ route('admin.tracking.order', $order) }}" class="btn btn-sm btn-outline">Track</a></td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Delivered not invoiced --}}
    <div style="background:var(--card,#fff); border:1px solid var(--border,#e1e8ed); border-radius:8px; padding:20px; margin-bottom:20px; {{ $deliveredNotInvoiced->count() > 0 ? 'border-left:4px solid var(--danger,#e74c3c);' : '' }}">
        <h2 style="font-size:18px; color:var(--danger,#e74c3c); margin:0 0 12px;">Delivered, No Invoice ({{ $deliveredNotInvoiced->count() }})</h2>
        @if($deliveredNotInvoiced->isEmpty())
            <p style="color:var(--text-light,#95a5a6);">All delivered orders have invoices.</p>
        @else
            <table style="width:100%; border-collapse:collapse;">
                <thead><tr style="border-bottom:2px solid var(--border,#e1e8ed);"><th style="padding:8px;">Order</th><th style="padding:8px;">Customer</th><th style="padding:8px;">Delivered</th><th style="padding:8px;">Total</th><th style="padding:8px;"></th></tr></thead>
                <tbody>
                    @foreach($deliveredNotInvoiced as $order)
                    <tr style="border-bottom:1px solid var(--border,#f0f3f5);"><td style="padding:8px;"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td><td style="padding:8px;">{{ $order->customer->user->name }}</td><td style="padding:8px;">{{ $order->updated_at->diffForHumans() }}</td><td style="padding:8px;">R{{ number_format($order->total, 2) }}</td><td style="padding:8px;"><form method="POST" action="{{ route('admin.orders.resend-invoice', $order) }}" style="display:inline;">@csrf<button type="submit" class="btn btn-sm btn-primary">Generate</button></form></td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Stale pending --}}
    <div style="background:var(--card,#fff); border:1px solid var(--border,#e1e8ed); border-radius:8px; padding:20px; margin-bottom:20px;">
        <h2 style="font-size:18px; color:var(--text-light,#95a5a6); margin:0 0 12px;">Pending Payment > 24h ({{ $stalePending->count() }})</h2>
        @if($stalePending->isEmpty())
            <p style="color:var(--text-light,#95a5a6);">No stale pending orders.</p>
        @else
            <table style="width:100%; border-collapse:collapse;">
                <thead><tr style="border-bottom:2px solid var(--border,#e1e8ed);"><th style="padding:8px;">Order</th><th style="padding:8px;">Customer</th><th style="padding:8px;">Created</th><th style="padding:8px;">Total</th><th style="padding:8px;"></th></tr></thead>
                <tbody>
                    @foreach($stalePending as $order)
                    <tr style="border-bottom:1px solid var(--border,#f0f3f5);"><td style="padding:8px;"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td><td style="padding:8px;">{{ $order->customer->user->name }}</td><td style="padding:8px;">{{ $order->created_at->diffForHumans() }}</td><td style="padding:8px;">R{{ number_format($order->total, 2) }}</td><td style="padding:8px;"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline">Review</a></td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
