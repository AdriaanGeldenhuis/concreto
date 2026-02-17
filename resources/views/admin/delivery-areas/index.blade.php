@extends('layouts.admin')
@section('title', 'Delivery Areas')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Delivery Areas</h1>
            <small class="text-muted">Define delivery zones with postal codes and fees. Areas are matched to customer addresses at checkout.</small>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('new-area-form').classList.toggle('d-none')">+ Add Area</button>
    </div>

    {{-- Add New Area Form --}}
    <div id="new-area-form" class="card d-none mb-4">
        <div class="card-header"><h5 class="mb-0">New Delivery Area</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.delivery-areas.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Area Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Pretoria East" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Delivery Fee (R) *</label>
                        <input type="number" name="fee" class="form-control" step="0.01" min="0" value="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Postal Codes</label>
                        <input type="text" name="postal_codes" class="form-control" placeholder="e.g. 0001, 0002, 0181">
                        <small class="text-muted">Comma-separated. Matched against customer address at checkout.</small>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="newAreaActive" checked>
                            <label class="form-check-label" for="newAreaActive">Active</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Create</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary --}}
    @if($areas->isNotEmpty())
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Total Areas</small>
                    <h5 class="mb-0">{{ $areas->count() }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Active Areas</small>
                    <h5 class="mb-0">{{ $areas->where('is_active', true)->count() }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Fee Range</small>
                    <h5 class="mb-0">R {{ number_format($areas->min('fee'), 2) }} - R {{ number_format($areas->max('fee'), 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Average Fee</small>
                    <h5 class="mb-0">R {{ number_format($areas->where('is_active', true)->avg('fee'), 2) }}</h5>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Existing Areas --}}
    <div class="card">
        <div class="card-header">All Areas ({{ $areas->count() }})</div>
        @if($areas->isEmpty())
            <div class="card-body text-center py-4">
                <p class="text-muted mb-0">No delivery areas defined yet. Click "+ Add Area" to create one.</p>
            </div>
        @else
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Area Name</th>
                                <th>Fee</th>
                                <th>Postal Codes</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($areas as $area)
                            <tr class="{{ !$area->is_active ? 'opacity-50' : '' }}">
                                <form method="POST" action="{{ route('admin.delivery-areas.update', $area) }}">
                                    @csrf
                                    @method('PUT')
                                    <td>
                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $area->name }}" required style="min-width:150px;">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm" style="max-width:140px;">
                                            <span class="input-group-text">R</span>
                                            <input type="number" name="fee" class="form-control" step="0.01" min="0" value="{{ $area->fee }}" required>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="postal_codes" class="form-control form-control-sm" value="{{ $area->postal_codes }}" placeholder="e.g. 0001, 0002" style="min-width:200px;">
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $area->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" style="font-weight:400;">Active</label>
                                        </div>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                </form>
                                        <form method="POST" action="{{ route('admin.delivery-areas.destroy', $area) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete the \'{{ $area->name }}\' delivery area?')">Delete</button>
                                        </form>
                                    </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
