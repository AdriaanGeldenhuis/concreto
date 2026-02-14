@extends('layouts.admin')
@section('title', isset($product) ? 'Edit Product' : 'New Product')
@section('content')
    <div class="page-header"><h1>{{ isset($product) ? 'Edit' : 'New' }} Product</h1></div>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($product)) @method('PUT') @endif
            <div class="form-row">
                <div class="form-group"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>@error('name')<div class="form-error">{{ $message }}</div>@enderror</div>
                <div class="form-group"><label class="form-label">Category</label><select name="category_id" class="form-control"><option value="">None</option>@foreach($categories as $c)<option value="{{ $c->id }}" {{ old('category_id', $product->category_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Price (R)</label><input type="number" name="price" class="form-control" step="0.01" value="{{ old('price', $product->price ?? '') }}" required></div>
                <div class="form-group"><label class="form-label">Unit</label><input type="text" name="unit" class="form-control" value="{{ old('unit', $product->unit ?? 'ton') }}" required></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control">{{ old('description', $product->description ?? '') }}</textarea></div>
            <div class="form-group"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
            <div class="form-check"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}><label>Active</label></div>
            <div class="form-check"><input type="checkbox" name="in_stock" value="1" {{ old('in_stock', $product->in_stock ?? true) ? 'checked' : '' }}><label>In Stock</label></div>
            <button type="submit" class="btn btn-primary mt-2">{{ isset($product) ? 'Update' : 'Create' }} Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline mt-2">Cancel</a>
        </form>
    </div></div>
@endsection
