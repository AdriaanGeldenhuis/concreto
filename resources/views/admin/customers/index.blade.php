@extends('layouts.admin')
@section('title', 'Customers')
@section('content')
    <div class="page-header">
        <h1>Customers</h1>
    </div>

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
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name or email..." value="{{ request('search') }}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['type', 'search']))
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost btn-sm">Clear</a>
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
                        <th>Company</th>
                        <th>Type</th>
                        <th class="text-right">Credit Limit</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td class="font-semibold">
                            {{ $customer->user->name }}
                            <div class="text-muted text-small">{{ $customer->user->email }}</div>
                        </td>
                        <td>{{ $customer->company->display_name ?? '-' }}</td>
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
                        <td class="text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="icon">&#9786;</div>
                                <h3>No customers found</h3>
                                <p>Customers will appear here when they register.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
