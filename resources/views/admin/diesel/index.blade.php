@extends('layouts.admin')
@section('title', 'Diesel Logs')

@section('content')
<div class="page-header">
    <h1>Diesel / Fuel Logs</h1>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.diesel.export', request()->query()) }}" class="btn btn-outline btn-sm">Export CSV</a>
        <a href="{{ route('admin.diesel.create') }}" class="btn btn-primary btn-sm">+ Log Diesel</a>
    </div>
</div>

{{-- Summary Stats --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">This Month</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--warning, #e67e22);">R{{ number_format($monthTotal, 0) }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Filtered Total</h6><h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($summary->total_cost, 0) }}</h3><small class="text-muted">{{ $summary->log_count }} fill-ups</small></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Litres</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ number_format($summary->total_litres, 0) }} L</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Avg Cost/Litre</h6><h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($summary->avg_cost_per_litre ?? 0, 2) }}</h3></div></div>
</div>

{{-- Filters --}}
<div class="card mb-2">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.diesel.index') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Driver</label>
                <select name="driver_id" class="form-control">
                    <option value="">All Drivers</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>{{ $driver->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Vehicle</label>
                <select name="vehicle_id" class="form-control">
                    <option value="">All Vehicles</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->registration }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="form-group">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['driver_id', 'vehicle_id', 'from', 'to']))
                    <a href="{{ route('admin.diesel.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Logs Table --}}
<div class="card">
    <div class="card-header"><span>Fuel Logs</span></div>
    @if($logs->isEmpty())
        <div class="card-body">
            <div class="empty-state">
                <div class="icon">&#9981;</div>
                <h3>No diesel logs yet</h3>
                <p><a href="{{ route('admin.diesel.create') }}">Log the first fill-up</a> to start tracking fuel costs.</p>
            </div>
        </div>
    @else
        <div class="table-responsive"><table><thead><tr>
            <th>Date</th>
            <th>Driver</th>
            <th>Vehicle</th>
            <th class="text-right">Litres</th>
            <th class="text-right">Cost/L</th>
            <th class="text-right">Total</th>
            <th class="text-right">Odometer</th>
            <th>Station</th>
            <th class="text-right">Actions</th>
        </tr></thead><tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->fill_date->format('d M Y') }}</td>
                <td>{{ $log->driver?->name ?? '-' }}</td>
                <td>{{ $log->vehicle?->registration ?? '-' }}</td>
                <td class="text-right">{{ number_format($log->litres, 2) }}</td>
                <td class="text-right">R{{ number_format($log->cost_per_litre, 2) }}</td>
                <td class="text-right font-semibold">R{{ number_format($log->total_cost, 2) }}</td>
                <td class="text-right">{{ $log->odometer ? number_format($log->odometer, 0).' km' : '-' }}</td>
                <td><small>{{ $log->station ?? '-' }}</small></td>
                <td class="text-right">
                    <div style="display:flex; gap:0.25rem; justify-content:flex-end;">
                        <a href="{{ route('admin.diesel.edit', $log) }}" class="btn btn-sm btn-outline">Edit</a>
                        <form method="POST" action="{{ route('admin.diesel.destroy', $log) }}" style="display:inline;" onsubmit="return confirm('Delete this log?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Del</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>

@if($logs->hasPages())
    <div class="pagination">{!! $logs->appends(request()->query())->links('pagination::simple-default') !!}</div>
@endif
@endsection
