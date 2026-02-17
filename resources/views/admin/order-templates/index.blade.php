@extends('layouts.admin')
@section('title', 'Order Templates')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Order Templates</h1>
        <div>
            <span class="badge bg-primary me-2">{{ $totalTemplates }} templates</span>
            <span class="badge bg-info">{{ $customersWithTemplates }} customers</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Template name or customer..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.order-templates.index') }}" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Template Name</th>
                            <th>Customer</th>
                            <th>Company</th>
                            <th>Items</th>
                            <th>Delivery Address</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td class="fw-bold">{{ $template->name }}</td>
                                <td>
                                    <a href="{{ route('admin.customers.show', $template->customer) }}">
                                        {{ $template->customer?->user?->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>{{ $template->customer?->company?->name ?? '-' }}</td>
                                <td>
                                    @php $items = $template->items ?? []; @endphp
                                    @foreach(array_slice($items, 0, 3) as $item)
                                        <span class="badge bg-light text-dark">
                                            {{ $products[$item['product_id'] ?? 0] ?? 'Product #' . ($item['product_id'] ?? '?') }}
                                            x{{ $item['qty'] ?? $item['quantity'] ?? '?' }}
                                        </span>
                                    @endforeach
                                    @if(count($items) > 3)
                                        <span class="text-muted">+{{ count($items) - 3 }} more</span>
                                    @endif
                                    @if(empty($items))
                                        <span class="text-muted">No items</span>
                                    @endif
                                </td>
                                <td>{{ $template->deliveryAddress?->line_1 ?? '-' }}</td>
                                <td>{{ $template->updated_at->format('d M Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.order-templates.show', $template) }}" class="btn btn-outline-primary">View</a>
                                        <form method="POST" action="{{ route('admin.order-templates.destroy', $template) }}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete this template? This cannot be undone.')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No order templates found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($templates->hasPages())
        <div class="mt-3">{!! $templates->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
</div>
@endsection
