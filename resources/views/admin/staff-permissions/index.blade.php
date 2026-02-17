@extends('layouts.admin')
@section('title', 'Staff Permissions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Staff Permissions</h1>
            <small class="text-muted">Control which areas of the admin panel each staff member can access. Admin users always have full access.</small>
        </div>
    </div>

    @if($staffUsers->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <h4 class="text-muted">No Active Staff Users</h4>
                <p class="text-muted mb-0">Create staff users in <a href="{{ route('admin.users.index') }}">User Management</a> to manage their permissions here.</p>
            </div>
        </div>
    @else
        {{-- Summary --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">Staff Members</small>
                        <h5 class="mb-0">{{ $staffUsers->count() }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">Custom Permissions</small>
                        <h5 class="mb-0">{{ $staffUsers->filter(fn($s) => !empty($s->custom_permissions))->count() }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">Using Defaults</small>
                        <h5 class="mb-0">{{ $staffUsers->filter(fn($s) => empty($s->custom_permissions))->count() }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">Permission Groups</small>
                        <h5 class="mb-0">{{ count($permissionGroups) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body py-2">
                <small class="text-muted">
                    Staff members without custom permissions use the default permission set (marked with <span class="text-success">&#10003;</span> below).
                    Use the <strong>all</strong> / <strong>none</strong> links to quickly toggle groups.
                </small>
            </div>
        </div>

        @foreach($staffUsers as $staff)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $staff->name }}</h5>
                        <small class="text-muted">{{ $staff->email }}</small>
                    </div>
                    <div>
                        @if(!empty($staff->custom_permissions))
                            <span class="badge bg-primary">Custom Permissions ({{ count($staff->custom_permissions) }} routes)</span>
                        @else
                            <span class="badge bg-secondary">Using Default Permissions</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.staff-permissions.update', $staff) }}">
                        @csrf
                        @method('PUT')

                        @php
                            $userPerms = !empty($staff->custom_permissions)
                                ? $staff->custom_permissions
                                : $defaultPermissions;
                        @endphp

                        <div class="row">
                            @foreach($permissionGroups as $groupName => $routes)
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3">
                                        <h6 class="fw-bold mb-2">
                                            {{ $groupName }}
                                            <button type="button" class="btn btn-sm btn-link p-0 ms-2 select-all-btn" data-group="{{ $loop->index }}">all</button>
                                            <button type="button" class="btn btn-sm btn-link p-0 ms-1 select-none-btn" data-group="{{ $loop->index }}">none</button>
                                        </h6>
                                        @foreach($routes as $route => $label)
                                            <div class="form-check">
                                                <input class="form-check-input group-{{ $loop->parent->index }}"
                                                       type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $route }}"
                                                       id="perm_{{ $staff->id }}_{{ str_replace('.', '_', $route) }}"
                                                       {{ in_array($route, $userPerms) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_{{ $staff->id }}_{{ str_replace('.', '_', $route) }}">
                                                    {{ $label }}
                                                    @if(in_array($route, $defaultPermissions))
                                                        <span class="text-success" title="Default permission">&#10003;</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">Save Permissions for {{ $staff->name }}</button>
                            @if(!empty($staff->custom_permissions))
                                <button type="submit" name="permissions" value="" class="btn btn-outline-secondary"
                                        onclick="this.form.querySelectorAll('input[name=\'permissions[]\']').forEach(i => i.checked = false); return true;">
                                    Reset to Defaults
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif
</div>

@push('scripts')
<script>
document.querySelectorAll('.select-all-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const group = this.dataset.group;
        document.querySelectorAll('.group-' + group).forEach(cb => cb.checked = true);
    });
});
document.querySelectorAll('.select-none-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const group = this.dataset.group;
        document.querySelectorAll('.group-' + group).forEach(cb => cb.checked = false);
    });
});
</script>
@endpush
@endsection
