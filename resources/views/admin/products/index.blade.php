@extends('layouts.admin')
@section('title', 'Products')
@section('content')
    <div class="page-header">
        <h1>Products</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th class="text-right">Price</th>
                        <th>Unit</th>
                        <th>In Stock</th>
                        <th>Active</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="font-semibold">{{ $product->name }}</td>
                        <td>
                            @if($product->category)
                                <span class="badge badge-secondary">{{ $product->category->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-right font-semibold">R{{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->unit }}</td>
                        <td>
                            <span class="badge badge-{{ $product->in_stock ? 'success' : 'danger' }}">
                                {{ $product->in_stock ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $product->is_active ? 'success' : 'secondary' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="icon">&#9733;</div>
                                <h3>No products yet</h3>
                                <p>Add your first product to get started.</p>
                                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
        <div class="pagination">{!! $products->links('pagination::simple-default') !!}</div>
    @endif
@endsection
