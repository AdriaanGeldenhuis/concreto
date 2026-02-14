@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
    <div class="page-header">
        <h1>Categories</h1>
        <span class="badge badge-secondary">{{ count($categories) }} total</span>
    </div>

    {{-- Add New Category --}}
    <div class="card">
        <div class="card-header">Add New Category</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="form-inline">
                @csrf
                <div class="form-group mb-0" style="flex:2;">
                    <input type="text" name="name" class="form-control" placeholder="Category name" required>
                </div>
                <div class="form-group mb-0" style="flex:0 0 100px;">
                    <input type="number" name="sort_order" class="form-control" placeholder="Sort" value="0">
                </div>
                <div class="form-check mb-0">
                    <input type="checkbox" name="is_active" value="1" id="new_active" checked>
                    <label for="new_active">Active</label>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Add Category</button>
            </form>
        </div>
    </div>

    {{-- Existing Categories --}}
    <div class="card">
        <div class="card-header">All Categories</div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Sort Order</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="form-inline">
                                @csrf @method('PUT')
                                <input type="text" name="name" class="form-control" value="{{ $category->name }}" required style="max-width:200px;">
                                <input type="number" name="sort_order" class="form-control" value="{{ $category->sort_order }}" style="max-width:80px;">
                                <input type="hidden" name="is_active" value="{{ $category->is_active ? '1' : '0' }}">
                                <button type="submit" class="btn btn-sm btn-outline">Save</button>
                            </form>
                        </td>
                        <td>{{ $category->sort_order }}</td>
                        <td>
                            <span class="badge badge-secondary">{{ $category->products_count }} products</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $category->is_active ? 'success' : 'secondary' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category? Products in this category will become uncategorized.')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="icon">&#9776;</div>
                                <h3>No categories yet</h3>
                                <p>Use the form above to add your first category.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
