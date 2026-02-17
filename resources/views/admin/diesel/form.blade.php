@extends('layouts.admin')
@section('title', isset($diesel) ? 'Edit Diesel Log' : 'Log Diesel')

@section('content')
<div class="container-fluid" style="max-width: 700px;">
    <div class="mb-4">
        <a href="{{ route('admin.diesel.index') }}" class="text-muted">&larr; Back to Diesel Logs</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ isset($diesel) ? 'Edit Diesel Log' : 'Log Diesel Fill-up' }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ isset($diesel) ? route('admin.diesel.update', $diesel) : route('admin.diesel.store') }}">
                @csrf
                @if(isset($diesel)) @method('PUT') @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Driver *</label>
                        <select name="driver_id" class="form-select" required>
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_id', $diesel->driver_id ?? '') == $driver->id ? 'selected' : '' }}>{{ $driver->name }}</option>
                            @endforeach
                        </select>
                        @error('driver_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-select">
                            <option value="">No Vehicle</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}" {{ old('vehicle_id', $diesel->vehicle_id ?? '') == $v->id ? 'selected' : '' }}>{{ $v->registration }} - {{ $v->make }} {{ $v->model }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Fill Date *</label>
                        <input type="date" name="fill_date" class="form-control" value="{{ old('fill_date', isset($diesel) ? $diesel->fill_date->format('Y-m-d') : date('Y-m-d')) }}" required max="{{ date('Y-m-d') }}">
                        @error('fill_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Litres *</label>
                        <input type="number" name="litres" class="form-control" value="{{ old('litres', $diesel->litres ?? '') }}" required min="0.01" step="0.01" id="litres" oninput="calcTotal()">
                        @error('litres') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cost per Litre (R) *</label>
                        <input type="number" name="cost_per_litre" class="form-control" value="{{ old('cost_per_litre', $diesel->cost_per_litre ?? '') }}" required min="0.01" step="0.01" id="cpl" oninput="calcTotal()">
                        @error('cost_per_litre') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="mb-3 p-3" style="background: rgba(255,193,7,0.1); border-radius: 8px;">
                    <strong>Total Cost: R <span id="total-display">{{ isset($diesel) ? number_format($diesel->total_cost, 2) : '0.00' }}</span></strong>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Odometer (km)</label>
                        <input type="number" name="odometer" class="form-control" value="{{ old('odometer', $diesel->odometer ?? '') }}" min="0" step="0.1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Station</label>
                        <input type="text" name="station" class="form-control" value="{{ old('station', $diesel->station ?? '') }}" placeholder="e.g. Shell N1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Receipt Ref</label>
                        <input type="text" name="receipt_reference" class="form-control" value="{{ old('receipt_reference', $diesel->receipt_reference ?? '') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="full_tank" value="1" id="full_tank"
                            {{ old('full_tank', $diesel->full_tank ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="full_tank">Full tank fill-up</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $diesel->notes ?? '') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">{{ isset($diesel) ? 'Update' : 'Log Diesel' }}</button>
            </form>
        </div>
    </div>
</div>

<script>
function calcTotal() {
    const l = parseFloat(document.getElementById('litres').value) || 0;
    const c = parseFloat(document.getElementById('cpl').value) || 0;
    document.getElementById('total-display').textContent = (l * c).toFixed(2);
}
</script>
@endsection
