@extends('layouts.admin')
@section('title', 'Products')
@section('content')
    <div class="page-header">
        <h1>Products</h1>
        <span class="badge badge-secondary">{{ $products->total() }} total</span>
        <div style="margin-left: auto; display:flex; gap:0.5rem;">
            <a href="{{ route('admin.products.export', request()->query()) }}" class="btn btn-outline btn-sm">Export CSV</a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">+ Add Product</a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-2">
        <div class="card-body">
            <form class="filters" method="GET" style="flex-wrap:wrap;">
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, SKU..." value="{{ request('search') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <select name="stock" class="form-control">
                        <option value="">All</option>
                        <option value="in_stock" {{ request('stock') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('stock') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('stock') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="">All</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['search', 'category_id', 'stock', 'is_active']))
                        <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th>
                            <a href="{{ route('admin.products.index', array_merge(request()->query(), ['sort' => 'name', 'dir' => request('sort') === 'name' && request('dir') !== 'desc' ? 'desc' : 'asc'])) }}" style="color:inherit; text-decoration:none;">
                                Name {!! request('sort') === 'name' ? (request('dir') === 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                            </a>
                        </th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th class="text-right">
                            <a href="{{ route('admin.products.index', array_merge(request()->query(), ['sort' => 'price', 'dir' => request('sort') === 'price' && request('dir') !== 'desc' ? 'desc' : 'asc'])) }}" style="color:inherit; text-decoration:none;">
                                Price {!! request('sort') === 'price' ? (request('dir') === 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                            </a>
                        </th>
                        <th>Unit</th>
                        <th class="text-center">
                            <a href="{{ route('admin.products.index', array_merge(request()->query(), ['sort' => 'stock_qty', 'dir' => request('sort') === 'stock_qty' && request('dir') !== 'desc' ? 'desc' : 'asc'])) }}" style="color:inherit; text-decoration:none;">
                                Stock {!! request('sort') === 'stock_qty' ? (request('dir') === 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                            </a>
                        </th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            @if($product->image_path)
                                <img src="{{ url('media/' . $product->image_path) }}" alt="" style="width:36px; height:36px; object-fit:cover; border-radius:var(--radius, 6px);">
                            @else
                                <div style="width:36px; height:36px; background:rgba(255,255,255,0.05); border-radius:var(--radius, 6px); display:flex; align-items:center; justify-content:center; font-size:0.75rem; color:var(--text-secondary, #6b7280);">&#9733;</div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.products.show', $product) }}" class="font-semibold" style="color:inherit;">{{ $product->name }}</a>
                        </td>
                        <td class="text-muted">{{ $product->sku ?? '-' }}</td>
                        <td>
                            @if($product->category)
                                <span class="badge badge-secondary">{{ $product->category->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($product->vendor)
                                <span class="text-small">{{ $product->vendor->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-right font-semibold">R{{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->unit }}</td>
                        <td class="text-center">
                            @if($product->stock_qty !== null)
                                @if($product->stock_qty <= 0)
                                    <span class="badge badge-danger">{{ $product->stock_qty }}</span>
                                @elseif($product->isLowStock())
                                    <span class="badge badge-warning">{{ $product->stock_qty }}</span>
                                @else
                                    <span class="badge badge-success">{{ $product->stock_qty }}</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($product->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-outline">View</a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <div class="icon">&#9733;</div>
                                <h3>No products found</h3>
                                <p>{{ request()->hasAny(['search', 'category_id', 'stock', 'is_active']) ? 'Try adjusting your filters.' : 'Add your first product to get started.' }}</p>
                                @unless(request()->hasAny(['search', 'category_id', 'stock', 'is_active']))
                                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
        <div class="pagination">{!! $products->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
@endsection
