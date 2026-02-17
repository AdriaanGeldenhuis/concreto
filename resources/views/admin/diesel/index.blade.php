@extends('layouts.admin')
@section('title', 'Diesel Logs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Diesel / Fuel Logs</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.diesel.export', request()->query()) }}" class="btn btn-secondary">Export CSV</a>
            <a href="{{ route('admin.diesel.create') }}" class="btn btn-primary">+ Log Diesel</a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">This Month</h6>
                    <h3 class="mb-0 text-warning">R {{ number_format($monthTotal, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Filtered Total</h6>
                    <h3 class="mb-0">R {{ number_format($summary->total_cost, 2) }}</h3>
                    <small class="text-muted">{{ $summary->log_count }} fill-ups</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Litres</h6>
                    <h3 class="mb-0">{{ number_format($summary->total_litres, 1) }} L</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Avg Cost/Litre</h6>
                    <h3 class="mb-0">R {{ number_format($summary->avg_cost_per_litre ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.diesel.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Driver</label>
                    <select name="driver_id" class="form-select">
                        <option value="">All Drivers</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>{{ $driver->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vehicle</label>
                    <select name="vehicle_id" class="form-select">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->registration }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.diesel.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th class="text-end">Litres</th>
                            <th class="text-end">Cost/L</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Odometer</th>
                            <th>Station</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->fill_date->format('d M Y') }}</td>
                            <td>{{ $log->driver?->name ?? '-' }}</td>
                            <td>{{ $log->vehicle?->registration ?? '-' }}</td>
                            <td class="text-end">{{ number_format($log->litres, 2) }}</td>
                            <td class="text-end">R {{ number_format($log->cost_per_litre, 2) }}</td>
                            <td class="text-end fw-bold">R {{ number_format($log->total_cost, 2) }}</td>
                            <td class="text-end">{{ $log->odometer ? number_format($log->odometer, 0) . ' km' : '-' }}</td>
                            <td>{{ $log->station ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.diesel.edit', $log) }}" class="btn btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.diesel.destroy', $log) }}" style="display:inline" onsubmit="return confirm('Delete this log?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Del</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No diesel logs yet. <a href="{{ route('admin.diesel.create') }}">Log the first fill-up</a>.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $logs->links() }}</div>
</div>
@endsection
