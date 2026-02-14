@extends('layouts.admin')
@section('title', 'Users')
@section('content')
    <div class="page-header d-flex justify-between items-center flex-wrap gap-1">
        <h1>Users & Roles</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add User</a>
    </div>
    <form class="filters" method="GET">
        <div class="form-group"><select name="role" class="form-control"><option value="">All Roles</option><option value="admin" {{ request('role')=='admin'?'selected':'' }}>Admin</option><option value="staff" {{ request('role')=='staff'?'selected':'' }}>Staff</option><option value="driver" {{ request('role')=='driver'?'selected':'' }}>Driver</option><option value="customer" {{ request('role')=='customer'?'selected':'' }}>Customer</option></select></div>
        <div class="form-group"><input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}"></div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>
    <div class="card"><div class="table-responsive">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th></th></tr></thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td><td>{{ $user->email }}</td>
                    <td><span class="badge badge-info">{{ ucfirst($user->role) }}</span></td>
                    <td><span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">{{ $user->is_active ? 'Yes' : 'No' }}</span></td>
                    <td><a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>
    <div class="pagination">{!! $users->appends(request()->query())->links('pagination::simple-default') !!}</div>
@endsection
