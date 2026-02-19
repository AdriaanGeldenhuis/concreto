@extends('layouts.admin')
@section('title', 'Vehicles')

@section('content')
<div class="page-header">
    <h1>Vehicles</h1>
    <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary btn-sm">+ Add Vehicle</a>
</div>

{{-- Stats --}}
@if($vehicles->isNotEmpty())
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Vehicles</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $vehicles->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Active</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $vehicles->where('is_active', true)->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Fills</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $vehicles->sum('diesel_logs_count') }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Month Diesel</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--warning, #e67e22);">R{{ number_format($vehicles->sum('month_diesel_cost'), 0) }}</h3></div></div>
</div>
@endif

{{-- Vehicles Table --}}
<div class="card">
    <div class="card-header"><span>All Vehicles</span><span class="badge badge-secondary">{{ $vehicles->count() }}</span></div>
    @if($vehicles->isEmpty())
        <div class="card-body">
            <div class="empty-state">
                <div class="icon">&#128663;</div>
                <h3>No vehicles added yet</h3>
                <p><a href="{{ route('admin.vehicles.create') }}">Add your first vehicle</a> to start tracking.</p>
            </div>
        </div>
    @else
        <div class="table-responsive"><table><thead><tr>
            <th>Registration</th>
            <th>Vehicle</th>
            <th>Fuel</th>
            <th>Status</th>
            <th class="text-right">Odometer</th>
            <th class="text-right">Tank</th>
            <th class="text-right">Fills</th>
            <th class="text-right">Total Litres</th>
            <th class="text-right">Month Cost</th>
            <th class="text-right">Total Cost</th>
            <th class="text-right">Actions</th>
        </tr></thead><tbody>
            @foreach($vehicles as $vehicle)
            <tr style="{{ !$vehicle->is_active ? 'opacity:0.5;' : '' }}">
                <td class="font-semibold">{{ $vehicle->registration }}</td>
                <td>
                    @if($vehicle->make || $vehicle->model)
                        {{ $vehicle->make }} {{ $vehicle->model }} {{ $vehicle->year ? '('.$vehicle->year.')' : '' }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                    @if($vehicle->color)<br><small class="text-muted">{{ $vehicle->color }}</small>@endif
                </td>
                <td>{{ ucfirst($vehicle->fuel_type) }}</td>
                <td><span class="badge badge-{{ $vehicle->is_active ? 'success' : 'danger' }}">{{ $vehicle->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="text-right">{{ number_format($vehicle->odometer, 0) }} km</td>
                <td class="text-right">{{ $vehicle->tank_capacity ? $vehicle->tank_capacity.' L' : '-' }}</td>
                <td class="text-right">{{ $vehicle->diesel_logs_count }}</td>
                <td class="text-right">{{ number_format($vehicle->total_litres, 0) }} L</td>
                <td class="text-right">R{{ number_format($vehicle->month_diesel_cost, 2) }}</td>
                <td class="text-right font-semibold" style="color:var(--warning, #e67e22);">R{{ number_format($vehicle->total_diesel_cost, 2) }}</td>
                <td class="text-right">
                    <div style="display:flex; gap:0.25rem; justify-content:flex-end;">
                        <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="btn btn-sm btn-outline">Edit</a>
                        <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}" style="display:inline;" onsubmit="return confirm('Delete this vehicle?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>
@endsection
