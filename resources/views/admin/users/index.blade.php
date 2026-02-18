@extends('layouts.admin')
@section('title', 'Users')
@section('content')
    <div class="page-header">
        <h1>Users & Roles</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Add User</a>
    </div>

    {{-- Summary Stats --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">Total Users</h6>
                <h3 class="mb-0" style="margin-top:0.25rem;">{{ $stats['total'] }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">Admins</h6>
                <h3 class="mb-0" style="margin-top:0.25rem;">{{ $stats['admins'] }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">Staff</h6>
                <h3 class="mb-0" style="margin-top:0.25rem;">{{ $stats['staff'] }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">Drivers</h6>
                <h3 class="mb-0" style="margin-top:0.25rem;">{{ $stats['drivers'] }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">Customers</h6>
                <h3 class="mb-0" style="margin-top:0.25rem;">{{ $stats['customers'] }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">Active</h6>
                <h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $stats['active'] }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">Inactive</h6>
                <h3 class="mb-0" style="margin-top:0.25rem; {{ $stats['inactive'] > 0 ? 'color:var(--danger, #e74c3c);' : '' }}">{{ $stats['inactive'] }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">2FA Enabled</h6>
                <h3 class="mb-0" style="margin-top:0.25rem;">{{ $stats['two_factor'] }}</h3>
            </div>
        </div>
    </div>

    {{-- Filters --}}
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
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, email or phone..." value="{{ request('search') }}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['role', 'search', 'status']))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'name', 'dir' => request('sort') == 'name' && request('dir') != 'desc' ? 'desc' : 'asc'])) }}" style="color:inherit; text-decoration:none;">
                                Name {!! request('sort') == 'name' ? (request('dir') == 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                            </a>
                        </th>
                        <th>Contact</th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'role', 'dir' => request('sort') == 'role' && request('dir') != 'desc' ? 'desc' : 'asc'])) }}" style="color:inherit; text-decoration:none;">
                                Role {!! request('sort') == 'role' ? (request('dir') == 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                            </a>
                        </th>
                        <th>Status</th>
                        <th>2FA</th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'created_at', 'dir' => request('sort') == 'created_at' && request('dir') != 'desc' ? 'desc' : 'asc'])) }}" style="color:inherit; text-decoration:none;">
                                Joined {!! request('sort') == 'created_at' ? (request('dir') == 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                            </a>
                        </th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr style="{{ !$user->is_active ? 'opacity: 0.55;' : '' }}">
                        <td class="font-semibold">{{ $user->name }}</td>
                        <td>
                            <div>{{ $user->email }}</div>
                            @if($user->phone)
                                <small class="text-muted">{{ $user->phone }}</small>
                            @endif
                        </td>
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
                        <td>
                            @if($user->two_factor_enabled)
                                <span class="badge badge-success" title="Two-factor authentication enabled">On</span>
                            @else
                                <span class="badge badge-secondary" title="Two-factor authentication disabled">Off</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $user->created_at->format('d M Y') }}</small>
                        </td>
                        <td class="text-right">
                            <div style="display:flex; gap:0.25rem; justify-content:flex-end; align-items:center;">
                                @if($user->role === 'customer' && $user->customer)
                                    <a href="{{ route('admin.customers.show', $user->customer) }}" class="btn btn-sm btn-outline" title="View customer profile">Profile</a>
                                @endif
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline">Edit</a>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-ghost' : 'btn-primary' }}" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }} user" onclick="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} {{ $user->name }}?')">
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
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
