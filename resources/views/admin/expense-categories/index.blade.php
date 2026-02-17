@extends('layouts.admin')
@section('title', 'Expense Categories')

@section('content')
<div class="container-fluid" style="max-width: 900px;">
    <h1 class="h3 mb-4">Expense Categories</h1>

    <!-- Add Category Form -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Add Category</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.expense-categories.store') }}">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Vehicle Maintenance">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Optional">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Color</label>
                        <input type="color" name="color" class="form-control form-control-color" value="#6c757d">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories List -->
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Categories</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 30px;"></th>
                            <th>Name</th>
                            <th>Description</th>
                            <th class="text-center">Expenses</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td><span style="display:inline-block;width:16px;height:16px;border-radius:3px;background:{{ $cat->color }}"></span></td>
                            <td class="fw-bold">{{ $cat->name }}</td>
                            <td class="text-muted">{{ $cat->description ?? '-' }}</td>
                            <td class="text-center">{{ $cat->expenses_count }}</td>
                            <td class="text-center">
                                @if($cat->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-secondary" onclick="document.getElementById('edit-{{ $cat->id }}').style.display = document.getElementById('edit-{{ $cat->id }}').style.display === 'none' ? 'table-row' : 'none'">Edit</button>
                                @if($cat->expenses_count === 0)
                                <form method="POST" action="{{ route('admin.expense-categories.destroy', $cat) }}" style="display:inline" onsubmit="return confirm('Delete this category?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        <tr id="edit-{{ $cat->id }}" style="display: none; background: rgba(0,0,0,0.05);">
                            <td colspan="6">
                                <form method="POST" action="{{ route('admin.expense-categories.update', $cat) }}" class="row g-2 align-items-end p-2">
                                    @csrf @method('PUT')
                                    <div class="col-md-3">
                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $cat->name }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="description" class="form-control form-control-sm" value="{{ $cat->description }}">
                                    </div>
                                    <div class="col-md-1">
                                        <input type="color" name="color" class="form-control form-control-sm form-control-color" value="{{ $cat->color }}">
                                    </div>
                                    <div class="col-md-1">
                                        <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ $cat->sort_order }}" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $cat->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label small">Active</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No categories yet. Add one above.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
