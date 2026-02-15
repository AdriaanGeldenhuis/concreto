@extends('layouts.admin')
@section('title', 'Delivery Areas')

@section('content')
<div class="page-header">
    <div class="d-flex justify-between items-center flex-wrap gap-1">
        <h1>Delivery Areas</h1>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('new-area-form').classList.toggle('d-none')">+ Add Area</button>
    </div>
</div>

{{-- Add New Area Form --}}
<div id="new-area-form" class="card d-none">
    <div class="card-header">New Delivery Area</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.delivery-areas.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group" style="flex:2;">
                    <label class="form-label">Area Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Pretoria East" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Delivery Fee (R)</label>
                    <input type="number" name="fee" class="form-control" step="0.01" min="0" value="0" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Postal Codes</label>
                <input type="text" name="postal_codes" class="form-control" placeholder="Comma-separated postal codes, e.g. 0001, 0002, 0181">
                <small class="text-muted">Optional. List postal codes covered by this area.</small>
            </div>
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" checked> Active
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Create Area</button>
        </form>
    </div>
</div>

{{-- Existing Areas --}}
<div class="card">
    <div class="card-header">
        <span>All Areas ({{ $areas->count() }})</span>
    </div>
    @if($areas->isEmpty())
        <div class="card-body">
            <p class="text-muted">No delivery areas defined yet. Click "Add Area" to create one.</p>
        </div>
    @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Area Name</th>
                        <th>Fee</th>
                        <th>Postal Codes</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($areas as $area)
                    <tr>
                        <form method="POST" action="{{ route('admin.delivery-areas.update', $area) }}">
                            @csrf
                            @method('PUT')
                            <td>
                                <input type="text" name="name" class="form-control" value="{{ $area->name }}" required style="min-width:150px;">
                            </td>
                            <td>
                                <input type="number" name="fee" class="form-control" step="0.01" min="0" value="{{ $area->fee }}" required style="max-width:120px;">
                            </td>
                            <td>
                                <input type="text" name="postal_codes" class="form-control" value="{{ $area->postal_codes }}" placeholder="e.g. 0001, 0002" style="min-width:200px;">
                            </td>
                            <td>
                                <label class="form-check" style="font-weight:400;">
                                    <input type="checkbox" name="is_active" value="1" {{ $area->is_active ? 'checked' : '' }}> Active
                                </label>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                        </form>
                                    <form method="POST" action="{{ route('admin.delivery-areas.destroy', $area) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this delivery area?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
