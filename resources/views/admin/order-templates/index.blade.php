@extends('layouts.admin')
@section('title', 'Order Templates')

@section('content')
<div class="page-header">
    <h1>Order Templates</h1>
    <div style="display:flex; gap:0.5rem; align-items:center;">
        <span class="badge badge-primary">{{ $totalTemplates }} templates</span>
        <span class="badge badge-info">{{ $customersWithTemplates }} customers</span>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" class="filters">
            <div class="form-group" style="flex:2;">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Template name or customer..." value="{{ request('search') }}">
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end; gap:0.35rem;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.order-templates.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Template Name</th>
            <th>Customer</th>
            <th>Company</th>
            <th>Items</th>
            <th>Delivery Address</th>
            <th>Last Updated</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
            @forelse($templates as $template)
                <tr>
                    <td class="font-semibold">{{ $template->name }}</td>
                    <td>
                        <a href="{{ route('admin.customers.show', $template->customer) }}">{{ $template->customer?->user?->name ?? 'N/A' }}</a>
                    </td>
                    <td>{{ $template->customer?->company?->name ?? '-' }}</td>
                    <td>
                        @php $items = $template->items ?? []; @endphp
                        @foreach(array_slice($items, 0, 3) as $item)
                            <span class="badge badge-secondary">
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
                    <td style="white-space:nowrap;">{{ $template->updated_at->format('d M Y') }}</td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('admin.order-templates.show', $template) }}" class="btn btn-outline btn-sm">View</a>
                        <form method="POST" action="{{ route('admin.order-templates.destroy', $template) }}" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this template? This cannot be undone.')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <h3>No order templates found</h3>
                            <p>Templates are created by customers from their portal.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@if($templates->hasPages())
    <div style="display:flex; justify-content:center; margin-top:1rem;">
        {{ $templates->appends(request()->query())->links() }}
    </div>
@endif
@endsection
