@extends('layouts.admin')
@section('title', isset($user) ? 'Edit User' : 'New User')
@section('content')
    <div class="page-header"><h1>{{ isset($user) ? 'Edit' : 'New' }} User</h1></div>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf
            @if(isset($user)) @method('PUT') @endif
            <div class="form-row">
                <div class="form-group"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>@error('name')<div class="form-error">{{ $message }}</div>@enderror</div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>@error('email')<div class="form-error">{{ $message }}</div>@enderror</div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}"></div>
                <div class="form-group"><label class="form-label">Password {{ isset($user) ? '(leave blank to keep)' : '' }}</label><input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>@error('password')<div class="form-error">{{ $message }}</div>@enderror</div>
            </div>
            <div class="form-group"><label class="form-label">Role</label><select name="role" class="form-control" required><option value="customer" {{ old('role', $user->role ?? '') == 'customer' ? 'selected' : '' }}>Customer</option><option value="driver" {{ old('role', $user->role ?? '') == 'driver' ? 'selected' : '' }}>Driver</option><option value="staff" {{ old('role', $user->role ?? '') == 'staff' ? 'selected' : '' }}>Staff</option><option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Admin</option></select></div>
            <div class="form-check"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}><label>Active</label></div>
            <button type="submit" class="btn btn-primary mt-2">{{ isset($user) ? 'Update' : 'Create' }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline mt-2">Cancel</a>
        </form>
    </div></div>
@endsection
