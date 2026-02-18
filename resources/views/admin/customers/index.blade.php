@extends('layouts.admin')
@section('title', 'Customers')
@section('content')
    <div class="page-header">
        <h1>Customers</h1>
        <div style="display:flex; gap:0.5rem;">
            <a href="{{ route('admin.customers.export', request()->query()) }}" class="btn btn-outline">&#8615; Export CSV</a>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">Total Customers</h6>
                <h3 class="mb-0" style="margin-top:0.25rem;">{{ $stats['total'] }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">COD</h6>
                <h3 class="mb-0" style="margin-top:0.25rem;">{{ $stats['cod'] }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">Account</h6>
                <h3 class="mb-0" style="margin-top:0.25rem;">{{ $stats['account'] }}</h3>
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
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">Outstanding</h6>
                <h3 class="mb-0" style="margin-top:0.25rem; {{ $stats['total_outstanding'] > 0 ? 'color:var(--danger, #e74c3c);' : '' }}">R{{ number_format($stats['total_outstanding'], 2) }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:0.75rem; text-align:center;">
                <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;">New This Month</h6>
                <h3 class="mb-0" style="margin-top:0.25rem;">{{ $stats['new_this_month'] }}</h3>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-2">
        <div class="card-body">
            <form class="filters" method="GET">
                <div class="form-group">
                    <label class="form-label">Account Type</label>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="COD" {{ request('type') == 'COD' ? 'selected' : '' }}>COD</option>
                        <option value="ACCOUNT" {{ request('type') == 'ACCOUNT' ? 'selected' : '' }}>Account</option>
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
                    <input type="text" name="search" class="form-control" placeholder="Name, email, phone, company, VAT..." value="{{ request('search') }}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['type', 'search', 'status']))
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Customers Table --}}
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['sort' => 'name', 'dir' => request('sort') == 'name' && request('dir') != 'desc' ? 'desc' : 'asc'])) }}" style="color:inherit; text-decoration:none;">
                                Customer {!! request('sort') == 'name' ? (request('dir') == 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                            </a>
                        </th>
                        <th>Company</th>
                        <th>Phone</th>
                        <th>
                            <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['sort' => 'type', 'dir' => request('sort') == 'type' && request('dir') != 'desc' ? 'desc' : 'asc'])) }}" style="color:inherit; text-decoration:none;">
                                Type {!! request('sort') == 'type' ? (request('dir') == 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                            </a>
                        </th>
                        <th class="text-right">
                            <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['sort' => 'credit_limit', 'dir' => request('sort') == 'credit_limit' && request('dir') != 'desc' ? 'desc' : 'asc'])) }}" style="color:inherit; text-decoration:none;">
                                Credit Limit {!! request('sort') == 'credit_limit' ? (request('dir') == 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                            </a>
                        </th>
                        <th>Status</th>
                        <th>
                            <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['sort' => 'created_at', 'dir' => request('sort') == 'created_at' && request('dir') != 'desc' ? 'desc' : 'asc'])) }}" style="color:inherit; text-decoration:none;">
                                Since {!! request('sort') == 'created_at' ? (request('dir') == 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                            </a>
                        </th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($customers as $customer)
                    <tr style="{{ !($customer->user->is_active ?? true) ? 'opacity: 0.55;' : '' }}">
                        <td>
                            <div class="font-semibold">{{ $customer->user->name }}</div>
                            <small class="text-muted">{{ $customer->user->email }}</small>
                        </td>
                        <td>
                            @if($customer->company)
                                <div>{{ $customer->company->display_name }}</div>
                                @if($customer->company->vat_number)
                                    <small class="text-muted">VAT: {{ $customer->company->vat_number }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($customer->user->phone)
                                <small>{{ $customer->user->phone }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $customer->type == 'COD' ? 'warning' : 'info' }}">
                                {{ $customer->type }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if($customer->credit_limit)
                                <span class="font-semibold">R{{ number_format($customer->credit_limit, 2) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ ($customer->user->is_active ?? false) ? 'success' : 'danger' }}">
                                {{ ($customer->user->is_active ?? false) ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">{{ $customer->created_at->format('d M Y') }}</small>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="icon">&#9786;</div>
                                <h3>No customers found</h3>
                                <p>Customers will appear here when they register or are created via Users.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($customers->hasPages())
        <div class="pagination">{!! $customers->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
@endsection
