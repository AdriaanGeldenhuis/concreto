@extends('layouts.admin')
@section('title', isset($product) ? 'Edit Product' : 'New Product')
@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('admin.products.index') }}">Products</a> / {{ isset($product) ? 'Edit' : 'New' }}
        </div>
        <h1>{{ isset($product) ? 'Edit' : 'New' }} Product</h1>
    </div>

    <form method="POST" action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($product)) @method('PUT') @endif

        <div class="card">
            <div class="card-header">Product Details</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">None</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ old('category_id', $product->category_id ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Price (R)</label>
                        <input type="number" name="price" class="form-control" step="0.01" value="{{ old('price', $product->price ?? '') }}" required>
                        @error('price')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit" class="form-control" value="{{ old('unit', $product->unit ?? 'ton') }}" required>
                        <div class="form-hint">e.g. ton, m3, bag, load</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Image & Status</div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    @if(isset($product) && $product->image)
                        <div class="form-hint mt-1">Current image is set. Upload a new one to replace it.</div>
                    @endif
                </div>
                <div class="form-row">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                        <label for="is_active">Active</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="in_stock" value="1" id="in_stock" {{ old('in_stock', $product->in_stock ?? true) ? 'checked' : '' }}>
                        <label for="in_stock">In Stock</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary">{{ isset($product) ? 'Update' : 'Create' }} Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
@endsection
