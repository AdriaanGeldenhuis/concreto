@extends('layouts.admin')
@section('title', 'Vehicles')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Vehicles</h1>
        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">+ Add Vehicle</a>
    </div>

    <div class="row mb-4">
        @forelse($vehicles as $vehicle)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card bg-dark text-light h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $vehicle->registration }}</h5>
                    @if($vehicle->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($vehicle->make || $vehicle->model)
                    <div class="mb-3">
                        <h6 class="text-muted">Vehicle Info</h6>
                        <p class="mb-1 small">{{ $vehicle->make }} {{ $vehicle->model }} {{ $vehicle->year ? '('.$vehicle->year.')' : '' }}</p>
                        @if($vehicle->color)
                        <p class="mb-1 small"><strong>Color:</strong> {{ $vehicle->color }}</p>
                        @endif
                        <p class="mb-1 small"><strong>Fuel:</strong> {{ ucfirst($vehicle->fuel_type) }}</p>
                    </div>
                    @endif

                    <div class="mb-3">
                        <h6 class="text-muted">Odometer & Tank</h6>
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1 small"><strong>Odometer:</strong></p>
                                <p class="mb-0">{{ number_format($vehicle->odometer, 0) }} km</p>
                            </div>
                            @if($vehicle->tank_capacity)
                            <div class="col-6">
                                <p class="mb-1 small"><strong>Tank:</strong></p>
                                <p class="mb-0">{{ $vehicle->tank_capacity }} L</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Diesel Stats</h6>
                        <div class="row">
                            <div class="col-4">
                                <p class="mb-1 small"><strong>Fills:</strong></p>
                                <p class="mb-0">{{ $vehicle->diesel_logs_count }}</p>
                            </div>
                            <div class="col-4">
                                <p class="mb-1 small"><strong>Litres:</strong></p>
                                <p class="mb-0">{{ number_format($vehicle->total_litres, 0) }}</p>
                            </div>
                            <div class="col-4">
                                <p class="mb-1 small"><strong>This Month:</strong></p>
                                <p class="mb-0">R {{ number_format($vehicle->month_diesel_cost, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    @if($vehicle->total_diesel_cost > 0)
                    <div class="mb-0">
                        <p class="mb-0 small"><strong>Total Diesel Cost:</strong> <span class="text-warning">R {{ number_format($vehicle->total_diesel_cost, 2) }}</span></p>
                    </div>
                    @endif
                </div>
                <div class="card-footer d-flex gap-2">
                    <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="btn btn-primary btn-sm flex-grow-1">Edit</a>
                    <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Delete this vehicle?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card bg-dark text-light">
                <div class="card-body text-center py-5">
                    <p class="mb-3">No vehicles added yet.</p>
                    <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">Add Your First Vehicle</a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
