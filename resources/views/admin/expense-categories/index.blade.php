@extends('layouts.admin')
@section('title', 'Expense Categories')

@section('content')
<div class="page-header">
    <h1>Expense Categories</h1>
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline btn-sm">View Expenses</a>
</div>

{{-- Add Category Form --}}
<div class="card mb-2">
    <div class="card-header">Add Category</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.expense-categories.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Vehicle Maintenance">
                </div>
                <div class="form-group" style="flex:2;">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" placeholder="Optional description">
                </div>
                <div class="form-group" style="flex:0.5;">
                    <label class="form-label">Color</label>
                    <input type="color" name="color" class="form-control" value="#6c757d" style="padding:0.25rem; height:38px;">
                </div>
                <div class="form-group" style="flex:0.5;">
                    <label class="form-label">Sort</label>
                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end;">
                    <button type="submit" class="btn btn-primary btn-sm">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Categories List --}}
<div class="card">
    <div class="card-header"><span>Categories</span><span class="badge badge-secondary">{{ $categories->count() }}</span></div>
    @if($categories->isEmpty())
        <div class="card-body">
            <div class="empty-state">
                <div class="icon">&#128193;</div>
                <h3>No categories yet</h3>
                <p>Add one above to start organizing expenses.</p>
            </div>
        </div>
    @else
        <div class="table-responsive"><table><thead><tr>
            <th style="width:24px;"></th>
            <th>Name</th>
            <th>Description</th>
            <th class="text-right">Expenses</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        </tr></thead><tbody>
            @foreach($categories as $cat)
            <tr>
                <td><span style="display:inline-block;width:16px;height:16px;border-radius:3px;background:{{ $cat->color }}"></span></td>
                <td class="font-semibold">{{ $cat->name }}</td>
                <td><small class="text-muted">{{ $cat->description ?? '-' }}</small></td>
                <td class="text-right">{{ $cat->expenses_count }}</td>
                <td><span class="badge badge-{{ $cat->is_active ? 'success' : 'secondary' }}">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="text-right" style="white-space:nowrap;">
                    <button class="btn btn-sm btn-outline" onclick="var el=document.getElementById('edit-{{ $cat->id }}'); el.style.display = el.style.display === 'none' ? 'table-row' : 'none'">Edit</button>
                    @if($cat->expenses_count === 0)
                    <form method="POST" action="{{ route('admin.expense-categories.destroy', $cat) }}" style="display:inline;" onsubmit="return confirm('Delete this category?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            <tr id="edit-{{ $cat->id }}" style="display:none; background:rgba(255,255,255,0.02);">
                <td colspan="6">
                    <form method="POST" action="{{ route('admin.expense-categories.update', $cat) }}" style="padding:0.5rem;">
                        @csrf @method('PUT')
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required>
                            </div>
                            <div class="form-group" style="flex:2;">
                                <input type="text" name="description" class="form-control" value="{{ $cat->description }}" placeholder="Description">
                            </div>
                            <div class="form-group" style="flex:0.4;">
                                <input type="color" name="color" class="form-control" value="{{ $cat->color }}" style="padding:0.25rem; height:38px;">
                            </div>
                            <div class="form-group" style="flex:0.4;">
                                <input type="number" name="sort_order" class="form-control" value="{{ $cat->sort_order }}" min="0">
                            </div>
                            <div class="form-group" style="flex:0.5;">
                                <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer; font-size:0.85rem;">
                                    <input type="checkbox" name="is_active" value="1" {{ $cat->is_active ? 'checked' : '' }}> Active
                                </label>
                            </div>
                            <div class="form-group" style="display:flex; align-items:flex-end;">
                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>
@endsection
