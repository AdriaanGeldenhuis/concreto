@extends('layouts.admin')
@section('title', isset($vehicle) ? 'Edit Vehicle' : 'Add Vehicle')

@section('content')
<div class="page-header">
    <div class="breadcrumb"><a href="{{ route('admin.vehicles.index') }}">Vehicles</a> / {{ isset($vehicle) ? $vehicle->registration : 'New' }}</div>
    <h1>{{ isset($vehicle) ? 'Edit Vehicle' : 'Add Vehicle' }}</h1>
</div>

<div class="card" style="max-width:720px;">
    <div class="card-header">{{ isset($vehicle) ? 'Edit Vehicle' : 'Add Vehicle' }}</div>
    <div class="card-body">
        <form method="POST" action="{{ isset($vehicle) ? route('admin.vehicles.update', $vehicle) : route('admin.vehicles.store') }}">
            @csrf
            @if(isset($vehicle)) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Registration *</label>
                    <input type="text" name="registration" class="form-control" value="{{ old('registration', $vehicle->registration ?? '') }}" required>
                    @error('registration') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Fuel Type *</label>
                    <select name="fuel_type" class="form-control" required>
                        <option value="diesel" {{ old('fuel_type', $vehicle->fuel_type ?? 'diesel') === 'diesel' ? 'selected' : '' }}>Diesel</option>
                        <option value="petrol" {{ old('fuel_type', $vehicle->fuel_type ?? '') === 'petrol' ? 'selected' : '' }}>Petrol</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Make</label>
                    <input type="text" name="make" class="form-control" value="{{ old('make', $vehicle->make ?? '') }}" placeholder="e.g. Toyota">
                </div>
                <div class="form-group">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle->model ?? '') }}" placeholder="e.g. Hilux">
                </div>
                <div class="form-group">
                    <label class="form-label">Year</label>
                    <input type="number" name="year" class="form-control" value="{{ old('year', $vehicle->year ?? '') }}" min="1990" max="{{ date('Y') + 1 }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input type="text" name="color" class="form-control" value="{{ old('color', $vehicle->color ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Odometer (km)</label>
                    <input type="number" name="odometer" class="form-control" value="{{ old('odometer', $vehicle->odometer ?? 0) }}" min="0" step="0.1">
                </div>
                <div class="form-group">
                    <label class="form-label">Tank Capacity (L)</label>
                    <input type="number" name="tank_capacity" class="form-control" value="{{ old('tank_capacity', $vehicle->tank_capacity ?? '') }}" min="0" step="0.1">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">VIN</label>
                <input type="text" name="vin" class="form-control" value="{{ old('vin', $vehicle->vin ?? '') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $vehicle->notes ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $vehicle->is_active ?? true) ? 'checked' : '' }}> Active
                </label>
            </div>

            <button type="submit" class="btn btn-primary">{{ isset($vehicle) ? 'Update Vehicle' : 'Add Vehicle' }}</button>
        </form>
    </div>
</div>
@endsection
