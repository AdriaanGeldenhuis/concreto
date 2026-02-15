@extends('layouts.admin')
@section('title', 'Operations Board')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    <h1 style="font-size: 24px; margin-bottom: 24px;">Operations Board</h1>

    {{-- Unassigned Orders --}}
    <div style="background: var(--card, #fff); border: 1px solid var(--border, #e1e8ed); border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <h2 style="font-size: 18px; color: var(--danger, #e74c3c); margin: 0 0 12px;">
            Unassigned Orders ({{ $unassigned->count() }})
        </h2>
        @if($unassigned->isEmpty())
            <p style="color: var(--text-light, #95a5a6);">All orders have drivers assigned.</p>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border, #e1e8ed);">
                        <th style="padding: 8px; text-align: left;">Order</th>
                        <th style="padding: 8px; text-align: left;">Customer</th>
                        <th style="padding: 8px; text-align: left;">Created</th>
                        <th style="padding: 8px; text-align: left;">Total</th>
                        <th style="padding: 8px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unassigned as $order)
                    <tr style="border-bottom: 1px solid var(--border, #f0f3f5);">
                        <td style="padding: 8px;"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td style="padding: 8px;">{{ $order->customer->user->name }}</td>
                        <td style="padding: 8px;">{{ $order->created_at->diffForHumans() }}</td>
                        <td style="padding: 8px;">R{{ number_format($order->total, 2) }}</td>
                        <td style="padding: 8px;"><a href="{{ route('admin.orders.show', $order) }}" style="color: var(--primary, #3498db);">Assign</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Assigned but not loaded --}}
    <div style="background: var(--card, #fff); border: 1px solid var(--border, #e1e8ed); border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <h2 style="font-size: 18px; color: var(--warning, #e67e22); margin: 0 0 12px;">
            Assigned > 60 min, Not Loaded ({{ $assignedNotLoaded->count() }})
        </h2>
        @if($assignedNotLoaded->isEmpty())
            <p style="color: var(--text-light, #95a5a6);">No stuck assignments.</p>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border, #e1e8ed);">
                        <th style="padding: 8px; text-align: left;">Order</th>
                        <th style="padding: 8px; text-align: left;">Driver</th>
                        <th style="padding: 8px; text-align: left;">Status</th>
                        <th style="padding: 8px; text-align: left;">Since</th>
                        <th style="padding: 8px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignedNotLoaded as $order)
                    <tr style="border-bottom: 1px solid var(--border, #f0f3f5);">
                        <td style="padding: 8px;"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td style="padding: 8px;">{{ $order->driver?->name ?? 'N/A' }}</td>
                        <td style="padding: 8px;">{{ $order->status }}</td>
                        <td style="padding: 8px;">{{ $order->updated_at->diffForHumans() }}</td>
                        <td style="padding: 8px;"><a href="{{ route('admin.orders.show', $order) }}" style="color: var(--warning, #e67e22);">Review</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- En route, no tracking --}}
    <div style="background: var(--card, #fff); border: 1px solid var(--border, #e1e8ed); border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <h2 style="font-size: 18px; color: var(--warning, #e67e22); margin: 0 0 12px;">
            En Route, No Tracking > 10 min ({{ $enRouteNoTracking->count() }})
        </h2>
        @if($enRouteNoTracking->isEmpty())
            <p style="color: var(--text-light, #95a5a6);">All en-route orders have recent tracking.</p>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border, #e1e8ed);">
                        <th style="padding: 8px; text-align: left;">Order</th>
                        <th style="padding: 8px; text-align: left;">Driver</th>
                        <th style="padding: 8px; text-align: left;">Last Update</th>
                        <th style="padding: 8px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enRouteNoTracking as $order)
                    <tr style="border-bottom: 1px solid var(--border, #f0f3f5);">
                        <td style="padding: 8px;"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td style="padding: 8px;">{{ $order->driver?->name ?? 'N/A' }}</td>
                        <td style="padding: 8px;">
                            @php $last = $order->driverLocations()->orderBy('recorded_at','desc')->first(); @endphp
                            {{ $last ? $last->recorded_at->diffForHumans() : 'Never' }}
                        </td>
                        <td style="padding: 8px;"><a href="{{ route('admin.orders.show', $order) }}" style="color: var(--warning, #e67e22);">Check</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Delivered not invoiced --}}
    <div style="background: var(--card, #fff); border: 1px solid var(--border, #e1e8ed); border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <h2 style="font-size: 18px; color: var(--danger, #e74c3c); margin: 0 0 12px;">
            Delivered, No Invoice ({{ $deliveredNotInvoiced->count() }})
        </h2>
        @if($deliveredNotInvoiced->isEmpty())
            <p style="color: var(--text-light, #95a5a6);">All delivered orders have invoices.</p>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border, #e1e8ed);">
                        <th style="padding: 8px; text-align: left;">Order</th>
                        <th style="padding: 8px; text-align: left;">Customer</th>
                        <th style="padding: 8px; text-align: left;">Delivered</th>
                        <th style="padding: 8px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliveredNotInvoiced as $order)
                    <tr style="border-bottom: 1px solid var(--border, #f0f3f5);">
                        <td style="padding: 8px;"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td style="padding: 8px;">{{ $order->customer->user->name }}</td>
                        <td style="padding: 8px;">{{ $order->updated_at->diffForHumans() }}</td>
                        <td style="padding: 8px;"><a href="{{ route('admin.orders.show', $order) }}" style="color: var(--danger, #e74c3c);">Generate Invoice</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Stale pending payment --}}
    <div style="background: var(--card, #fff); border: 1px solid var(--border, #e1e8ed); border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <h2 style="font-size: 18px; color: var(--text-light, #95a5a6); margin: 0 0 12px;">
            Pending Payment > 24h ({{ $stalePending->count() }})
        </h2>
        @if($stalePending->isEmpty())
            <p style="color: var(--text-light, #95a5a6);">No stale pending orders.</p>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border, #e1e8ed);">
                        <th style="padding: 8px; text-align: left;">Order</th>
                        <th style="padding: 8px; text-align: left;">Customer</th>
                        <th style="padding: 8px; text-align: left;">Created</th>
                        <th style="padding: 8px; text-align: left;">Total</th>
                        <th style="padding: 8px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stalePending as $order)
                    <tr style="border-bottom: 1px solid var(--border, #f0f3f5);">
                        <td style="padding: 8px;"><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td style="padding: 8px;">{{ $order->customer->user->name }}</td>
                        <td style="padding: 8px;">{{ $order->created_at->diffForHumans() }}</td>
                        <td style="padding: 8px;">R{{ number_format($order->total, 2) }}</td>
                        <td style="padding: 8px;"><a href="{{ route('admin.orders.show', $order) }}" style="color: var(--text-light, #95a5a6);">Review</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
