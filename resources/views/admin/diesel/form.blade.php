@extends('layouts.admin')
@section('title', isset($diesel) ? 'Edit Diesel Log' : 'Log Diesel')

@section('content')
<div class="page-header">
    <div class="breadcrumb"><a href="{{ route('admin.diesel.index') }}">Diesel Logs</a> / {{ isset($diesel) ? 'Edit' : 'New' }}</div>
    <h1>{{ isset($diesel) ? 'Edit Diesel Log' : 'Log Diesel Fill-up' }}</h1>
</div>

<div class="card" style="max-width:720px;">
    <div class="card-header">{{ isset($diesel) ? 'Edit Diesel Log' : 'Log Diesel Fill-up' }}</div>
    <div class="card-body">
        <form method="POST" action="{{ isset($diesel) ? route('admin.diesel.update', $diesel) : route('admin.diesel.store') }}">
            @csrf
            @if(isset($diesel)) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Driver *</label>
                    <select name="driver_id" class="form-control" required>
                        <option value="">Select Driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ old('driver_id', $diesel->driver_id ?? '') == $driver->id ? 'selected' : '' }}>{{ $driver->name }}</option>
                        @endforeach
                    </select>
                    @error('driver_id') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Vehicle</label>
                    <select name="vehicle_id" class="form-control">
                        <option value="">No Vehicle</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ old('vehicle_id', $diesel->vehicle_id ?? '') == $v->id ? 'selected' : '' }}>{{ $v->registration }} – {{ $v->make }} {{ $v->model }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Fill Date *</label>
                    <input type="date" name="fill_date" class="form-control" value="{{ old('fill_date', isset($diesel) ? $diesel->fill_date->format('Y-m-d') : date('Y-m-d')) }}" required max="{{ date('Y-m-d') }}">
                    @error('fill_date') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Litres *</label>
                    <input type="number" name="litres" class="form-control" value="{{ old('litres', $diesel->litres ?? '') }}" required min="0.01" step="0.01" id="litres" oninput="calcTotal()">
                    @error('litres') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Cost per Litre (R) *</label>
                    <input type="number" name="cost_per_litre" class="form-control" value="{{ old('cost_per_litre', $diesel->cost_per_litre ?? '') }}" required min="0.01" step="0.01" id="cpl" oninput="calcTotal()">
                    @error('cost_per_litre') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
            </div>

            <div style="background:rgba(249,115,22,0.08); border:1px solid rgba(249,115,22,0.2); border-radius:var(--radius-sm, 6px); padding:0.75rem 1rem; margin-bottom:1rem;">
                <strong>Total Cost: R <span id="total-display">{{ isset($diesel) ? number_format($diesel->total_cost, 2) : '0.00' }}</span></strong>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Odometer (km)</label>
                    <input type="number" name="odometer" class="form-control" value="{{ old('odometer', $diesel->odometer ?? '') }}" min="0" step="0.1">
                </div>
                <div class="form-group">
                    <label class="form-label">Station</label>
                    <input type="text" name="station" class="form-control" value="{{ old('station', $diesel->station ?? '') }}" placeholder="e.g. Shell N1">
                </div>
                <div class="form-group">
                    <label class="form-label">Receipt Ref</label>
                    <input type="text" name="receipt_reference" class="form-control" value="{{ old('receipt_reference', $diesel->receipt_reference ?? '') }}">
                </div>
            </div>

            <div class="form-group">
                <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer;">
                    <input type="checkbox" name="full_tank" value="1" {{ old('full_tank', $diesel->full_tank ?? true) ? 'checked' : '' }}> Full tank fill-up
                </label>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $diesel->notes ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">{{ isset($diesel) ? 'Update' : 'Log Diesel' }}</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function calcTotal() {
    var l = parseFloat(document.getElementById('litres').value) || 0;
    var c = parseFloat(document.getElementById('cpl').value) || 0;
    document.getElementById('total-display').textContent = (l * c).toFixed(2);
}
</script>
@endpush
