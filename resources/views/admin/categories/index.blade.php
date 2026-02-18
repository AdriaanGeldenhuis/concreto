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
            <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <div class="form-group" style="flex:2;">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Category name" required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group" style="flex:0 0 100px;">
                        <label class="form-label">Sort</label>
                        <input type="number" name="sort_order" class="form-control" placeholder="0" value="0">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:2;">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Optional description">
                    </div>
                    <div class="form-check" style="padding-top:1.5rem;">
                        <input type="checkbox" name="is_active" value="1" id="new_active" checked>
                        <label for="new_active">Active</label>
                    </div>
                    <div style="padding-top:1.5rem;">
                        <button type="submit" class="btn btn-primary btn-sm">Add Category</button>
                    </div>
                </div>
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
                        <th style="width:50px;"></th>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center">Sort</th>
                        <th class="text-center">Products</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr id="cat-row-{{ $category->id }}">
                        <td>
                            @if($category->image_path)
                                <img src="{{ url('media/' . $category->image_path) }}" alt="" style="width:40px; height:40px; object-fit:cover; border-radius:var(--radius, 6px);">
                            @else
                                <div style="width:40px; height:40px; background:rgba(255,255,255,0.05); border-radius:var(--radius, 6px); display:flex; align-items:center; justify-content:center; font-size:0.85rem; color:var(--text-secondary, #6b7280);">&#9776;</div>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="cat-edit-form" style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                                @csrf @method('PUT')
                                <input type="text" name="name" class="form-control" value="{{ $category->name }}" required style="max-width:180px;">
                                <input type="number" name="sort_order" class="form-control" value="{{ $category->sort_order }}" style="max-width:70px;" title="Sort order">
                                <input type="hidden" name="description" value="{{ $category->description }}">
                                <input type="hidden" name="is_active" value="{{ $category->is_active ? '1' : '0' }}">
                                <input type="file" name="image" accept="image/*" style="max-width:150px; font-size:0.75rem;">
                                <button type="submit" class="btn btn-sm btn-outline">Save</button>
                            </form>
                        </td>
                        <td class="text-muted" style="font-size:0.85rem; max-width:200px;">
                            {{ Str::limit($category->description, 60) ?: '-' }}
                        </td>
                        <td class="text-center">{{ $category->sort_order }}</td>
                        <td class="text-center">
                            <span class="badge badge-secondary">{{ $category->products_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $category->is_active ? 'success' : 'secondary' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-{{ $category->is_active ? 'warning' : 'success' }}" onclick="toggleActive({{ $category->id }}, {{ $category->is_active ? 0 : 1 }})">
                                    {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category? Products will become uncategorised.')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
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

@push('scripts')
<script>
function toggleActive(categoryId, newState) {
    const row = document.getElementById('cat-row-' + categoryId);
    const form = row.querySelector('.cat-edit-form');
    const hiddenActive = form.querySelector('input[name="is_active"]');
    hiddenActive.value = newState;
    form.submit();
}
</script>
@endpush
@endsection
