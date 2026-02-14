@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
    <div class="page-header"><h1>Categories</h1></div>

    <div class="card">
        <div class="card-header">Add Category</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="form-inline">
                @csrf
                <input type="text" name="name" class="form-control" placeholder="Category name" required>
                <input type="number" name="sort_order" class="form-control" placeholder="Sort" value="0" style="width:80px;">
                <div class="form-check"><input type="checkbox" name="is_active" value="1" checked><label>Active</label></div>
                <button type="submit" class="btn btn-primary btn-sm">Add</button>
            </form>
        </div>
    </div>

    @foreach($categories as $category)
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="form-inline">
                @csrf @method('PUT')
                <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                <input type="number" name="sort_order" class="form-control" value="{{ $category->sort_order }}" style="width:80px;">
                <div class="form-check"><input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}><label>Active</label></div>
                <span class="badge badge-secondary">{{ $category->products_count }} products</span>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </form>
            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display:inline;margin-left:0.5rem;">@csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</button>
            </form>
        </div>
    </div>
    @endforeach
@endsection
