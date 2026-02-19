@extends('layouts.admin')
@section('title', 'Delivery Areas')

@section('content')
<div class="page-header">
    <h1>Delivery Areas</h1>
    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('new-area-form').style.display = document.getElementById('new-area-form').style.display === 'none' ? 'block' : 'none'">+ Add Area</button>
</div>

{{-- Add New Area Form --}}
<div id="new-area-form" class="card mb-2" style="display:none;">
    <div class="card-header">New Delivery Area</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.delivery-areas.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Area Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Pretoria East" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Delivery Fee (R) *</label>
                    <input type="number" name="fee" class="form-control" step="0.01" min="0" value="0" required>
                </div>
                <div class="form-group" style="flex:2;">
                    <label class="form-label">Postal Codes</label>
                    <input type="text" name="postal_codes" class="form-control" placeholder="e.g. 0001, 0002, 0181">
                    <small class="text-muted">Comma-separated. Matched against customer address at checkout.</small>
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end; gap:0.5rem;">
                    <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer; font-size:0.85rem;">
                        <input type="checkbox" name="is_active" value="1" checked> Active
                    </label>
                    <button type="submit" class="btn btn-primary btn-sm">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary --}}
@if($areas->isNotEmpty())
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Areas</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $areas->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Active</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $areas->where('is_active', true)->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Fee Range</h6><h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($areas->min('fee'), 0) }} – R{{ number_format($areas->max('fee'), 0) }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Avg Fee</h6><h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($areas->where('is_active', true)->avg('fee'), 2) }}</h3></div></div>
</div>
@endif

{{-- Areas Table --}}
<div class="card">
    <div class="card-header"><span>All Areas</span><span class="badge badge-secondary">{{ $areas->count() }}</span></div>
    @if($areas->isEmpty())
        <div class="card-body">
            <div class="empty-state">
                <div class="icon">&#9872;</div>
                <h3>No delivery areas defined</h3>
                <p>Click "+ Add Area" to create your first delivery zone.</p>
            </div>
        </div>
    @else
        <div class="table-responsive"><table><thead><tr><th>Area Name</th><th>Fee</th><th>Postal Codes</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody>
            @foreach($areas as $area)
            <tr style="{{ !$area->is_active ? 'opacity:0.5;' : '' }}">
                <form method="POST" action="{{ route('admin.delivery-areas.update', $area) }}">
                    @csrf @method('PUT')
                    <td><input type="text" name="name" class="form-control" value="{{ $area->name }}" required style="min-width:150px;"></td>
                    <td><div style="display:flex; align-items:center; gap:0.25rem;"><span class="text-muted">R</span><input type="number" name="fee" class="form-control" step="0.01" min="0" value="{{ $area->fee }}" required style="max-width:100px;"></div></td>
                    <td><input type="text" name="postal_codes" class="form-control" value="{{ $area->postal_codes }}" placeholder="e.g. 0001, 0002" style="min-width:200px;"></td>
                    <td>
                        <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer; font-size:0.85rem;">
                            <input type="checkbox" name="is_active" value="1" {{ $area->is_active ? 'checked' : '' }}> Active
                        </label>
                    </td>
                    <td class="text-right" style="white-space:nowrap;">
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                </form>
                        <form method="POST" action="{{ route('admin.delivery-areas.destroy', $area) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete the \'{{ $area->name }}\' delivery area?')">Delete</button>
                        </form>
                    </td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>
@endsection
