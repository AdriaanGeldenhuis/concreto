@extends('layouts.admin')
@section('title', 'Customers')
@section('content')
    <div class="page-header"><h1>Customers</h1></div>
    <form class="filters" method="GET">
        <div class="form-group"><select name="type" class="form-control"><option value="">All Types</option><option value="COD" {{ request('type') == 'COD' ? 'selected' : '' }}>COD</option><option value="ACCOUNT" {{ request('type') == 'ACCOUNT' ? 'selected' : '' }}>Account</option></select></div>
        <div class="form-group"><input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}"></div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>
    <div class="card"><div class="table-responsive">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Type</th><th>Credit Limit</th><th></th></tr></thead>
            <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->user->name }}</td>
                    <td>{{ $customer->user->email }}</td>
                    <td><span class="badge badge-{{ $customer->type == 'COD' ? 'warning' : 'info' }}">{{ $customer->type }}</span></td>
                    <td>{{ $customer->credit_limit ? 'R' . number_format($customer->credit_limit, 2) : '-' }}</td>
                    <td><a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>
@endsection
