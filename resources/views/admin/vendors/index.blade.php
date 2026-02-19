@extends('layouts.admin')
@section('title', 'Vendors')
@section('content')
    <div class="page-header">
        <h1>Vendors / Suppliers</h1>
        <span class="badge badge-secondary">{{ $vendors->total() }} total</span>
        <div style="margin-left: auto;">
            <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary btn-sm">+ Add Vendor</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Contact Person</th>
                        <th class="text-center">Products</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($vendors as $vendor)
                    <tr>
                        <td style="font-weight: 600;">{{ $vendor->name }}</td>
                        <td>{{ $vendor->email }}</td>
                        <td>{{ $vendor->phone ?? '-' }}</td>
                        <td>{{ $vendor->contact_person ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge badge-secondary">{{ $vendor->products_count }}</span>
                        </td>
                        <td>
                            @if($vendor->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.vendors.destroy', $vendor) }}" style="display:inline;" onsubmit="return confirm('Delete vendor {{ $vendor->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <h3>No vendors yet</h3>
                                <p>Add your first vendor/supplier to start receiving order notifications.</p>
                                <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary btn-sm">+ Add Vendor</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($vendors->hasPages())
        <div class="pagination">{!! $vendors->links('pagination::simple-default') !!}</div>
    @endif
@endsection
