@extends('layouts.admin')
@section('title', 'Users')
@section('content')
    <div class="page-header">
        <h1>Users & Roles</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add User</a>
    </div>

    <div class="card mb-2">
        <div class="card-body">
            <form class="filters" method="GET">
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="driver" {{ request('role') == 'driver' ? 'selected' : '' }}>Driver</option>
                        <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name or email..." value="{{ request('search') }}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['role', 'search']))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="font-semibold">{{ $user->name }}</td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>
                            <span class="badge badge-{{ match($user->role) {
                                'admin' => 'danger',
                                'staff' => 'info',
                                'driver' => 'warning',
                                default => 'secondary'
                            } }}">{{ ucfirst($user->role) }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="icon">&#9998;</div>
                                <h3>No users found</h3>
                                <p>Try adjusting your filters or add a new user.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($users->hasPages())
        <div class="pagination">{!! $users->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
@endsection
