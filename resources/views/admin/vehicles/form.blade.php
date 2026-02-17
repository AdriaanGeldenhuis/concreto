@extends('layouts.admin')
@section('title', isset($vehicle) ? 'Edit Vehicle' : 'Add Vehicle')

@section('content')
<div class="container-fluid" style="max-width: 700px;">
    <div class="mb-4">
        <a href="{{ route('admin.vehicles.index') }}" class="text-muted">&larr; Back to Vehicles</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ isset($vehicle) ? 'Edit Vehicle' : 'Add Vehicle' }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ isset($vehicle) ? route('admin.vehicles.update', $vehicle) : route('admin.vehicles.store') }}">
                @csrf
                @if(isset($vehicle)) @method('PUT') @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Registration *</label>
                        <input type="text" name="registration" class="form-control" value="{{ old('registration', $vehicle->registration ?? '') }}" required>
                        @error('registration') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fuel Type *</label>
                        <select name="fuel_type" class="form-select" required>
                            <option value="diesel" {{ old('fuel_type', $vehicle->fuel_type ?? 'diesel') === 'diesel' ? 'selected' : '' }}>Diesel</option>
                            <option value="petrol" {{ old('fuel_type', $vehicle->fuel_type ?? '') === 'petrol' ? 'selected' : '' }}>Petrol</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Make</label>
                        <input type="text" name="make" class="form-control" value="{{ old('make', $vehicle->make ?? '') }}" placeholder="e.g. Toyota">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle->model ?? '') }}" placeholder="e.g. Hilux">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-control" value="{{ old('year', $vehicle->year ?? '') }}" min="1990" max="{{ date('Y') + 1 }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" value="{{ old('color', $vehicle->color ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Odometer (km)</label>
                        <input type="number" name="odometer" class="form-control" value="{{ old('odometer', $vehicle->odometer ?? 0) }}" min="0" step="0.1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tank Capacity (L)</label>
                        <input type="number" name="tank_capacity" class="form-control" value="{{ old('tank_capacity', $vehicle->tank_capacity ?? '') }}" min="0" step="0.1">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">VIN</label>
                    <input type="text" name="vin" class="form-control" value="{{ old('vin', $vehicle->vin ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $vehicle->notes ?? '') }}</textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                            {{ old('is_active', $vehicle->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ isset($vehicle) ? 'Update Vehicle' : 'Add Vehicle' }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
