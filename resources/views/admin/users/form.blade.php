@extends('layouts.admin')
@section('title', isset($user) ? 'Edit User' : 'New User')
@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('admin.users.index') }}">Users</a> / {{ isset($user) ? 'Edit' : 'New' }}
        </div>
        <h1>{{ isset($user) ? 'Edit' : 'New' }} User</h1>
    </div>

    @if(isset($user))
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
            <div class="card">
                <div class="card-body" style="padding:0.75rem;">
                    <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Role</h6>
                    <span class="badge badge-{{ match($user->role) { 'admin' => 'danger', 'staff' => 'info', 'driver' => 'warning', default => 'secondary' } }}" style="margin-top:0.25rem;">{{ ucfirst($user->role) }}</span>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="padding:0.75rem;">
                    <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Status</h6>
                    <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}" style="margin-top:0.25rem;">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="padding:0.75rem;">
                    <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">2FA</h6>
                    <span class="badge badge-{{ $user->two_factor_enabled ? 'success' : 'secondary' }}" style="margin-top:0.25rem;">{{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}</span>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="padding:0.75rem;">
                    <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Joined</h6>
                    <div style="margin-top:0.25rem; font-size:0.85rem;">{{ $user->created_at->format('d M Y') }}</div>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        <div class="card">
            <div class="card-header">Account Details</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
                        @error('email')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}" placeholder="e.g. 082 123 4567">
                        @error('phone')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Password {{ isset($user) ? '' : '*' }}
                            @if(isset($user))
                                <span class="text-muted font-normal">(leave blank to keep current)</span>
                            @endif
                        </label>
                        <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
                        @error('password')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Role & Permissions</div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select name="role" class="form-control" required>
                        <option value="customer" {{ old('role', $user->role ?? '') == 'customer' ? 'selected' : '' }}>Customer</option>
                        <option value="driver" {{ old('role', $user->role ?? '') == 'driver' ? 'selected' : '' }}>Driver</option>
                        <option value="staff" {{ old('role', $user->role ?? '') == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    <div class="form-hint">
                        <strong>Admin</strong> = Full access |
                        <strong>Staff</strong> = Limited access (orders, quotes, customers, reports) |
                        <strong>Driver</strong> = Delivery portal |
                        <strong>Customer</strong> = Customer portal &amp; ordering
                    </div>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                    <label for="is_active">Active</label>
                </div>
                <div class="form-hint" style="margin-bottom:0.75rem;">Inactive users cannot log in.</div>
                <div class="form-check">
                    <input type="checkbox" name="two_factor_enabled" value="1" id="two_factor_enabled" {{ old('two_factor_enabled', $user->two_factor_enabled ?? false) ? 'checked' : '' }}>
                    <label for="two_factor_enabled">Enable Two-Factor Authentication (email code on login)</label>
                </div>
                <div class="form-hint">Recommended for admin and staff roles. A 6-digit code is emailed on each login.</div>
            </div>
        </div>

        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Update' : 'Create' }} User</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Cancel</a>
            @if(isset($user) && $user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" style="margin-left:auto;">
                    @csrf
                    <button type="submit" class="btn {{ $user->is_active ? 'btn-ghost' : 'btn-primary' }}" onclick="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} this user?')">
                        {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                    </button>
                </form>
            @endif
        </div>
    </form>
@endsection
