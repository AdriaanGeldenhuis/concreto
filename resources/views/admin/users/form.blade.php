@extends('layouts.admin')
@section('title', isset($user) ? 'Edit User' : 'New User')
@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('admin.users.index') }}">Users</a> / {{ isset($user) ? 'Edit' : 'New' }}
        </div>
        <h1>{{ isset($user) ? 'Edit' : 'New' }} User</h1>
    </div>

    <form method="POST" action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        <div class="card">
            <div class="card-header">Account Details</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
                        @error('email')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}" placeholder="e.g. 082 123 4567">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Password
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
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="customer" {{ old('role', $user->role ?? '') == 'customer' ? 'selected' : '' }}>Customer</option>
                        <option value="driver" {{ old('role', $user->role ?? '') == 'driver' ? 'selected' : '' }}>Driver</option>
                        <option value="staff" {{ old('role', $user->role ?? '') == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    <div class="form-hint">Admins have full access. Staff can view/manage orders, quotes, customers, and reports but cannot access settings, user management, driver salary, promo codes, or financial exports.</div>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                    <label for="is_active">Active</label>
                </div>
                <div class="form-check" style="margin-top: 0.5rem;">
                    <input type="checkbox" name="two_factor_enabled" value="1" id="two_factor_enabled" {{ old('two_factor_enabled', $user->two_factor_enabled ?? false) ? 'checked' : '' }}>
                    <label for="two_factor_enabled">Enable Two-Factor Authentication (email code on login)</label>
                </div>
                <div class="form-hint">Staff and admin roles should have 2FA enabled. When enabled, a 6-digit code is emailed on each login.</div>
            </div>
        </div>

        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Update' : 'Create' }} User</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
@endsection
