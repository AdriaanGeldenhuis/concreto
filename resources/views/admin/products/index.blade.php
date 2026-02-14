@extends('layouts.admin')
@section('title', 'Products')
@section('content')
    <div class="page-header d-flex justify-between items-center flex-wrap gap-1">
        <h1>Products</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
    </div>
    <div class="card"><div class="table-responsive">
        <table>
            <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Unit</th><th>Stock</th><th>Active</th><th></th></tr></thead>
            <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>R{{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->unit }}</td>
                    <td><span class="badge badge-{{ $product->in_stock ? 'success' : 'danger' }}">{{ $product->in_stock ? 'Yes' : 'No' }}</span></td>
                    <td><span class="badge badge-{{ $product->is_active ? 'success' : 'secondary' }}">{{ $product->is_active ? 'Yes' : 'No' }}</span></td>
                    <td>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline">Edit</a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="display:inline;">@csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>
    <div class="pagination">{!! $products->links('pagination::simple-default') !!}</div>
@endsection
