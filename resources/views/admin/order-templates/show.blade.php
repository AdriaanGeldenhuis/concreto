@extends('layouts.admin')
@section('title', 'Template: ' . $orderTemplate->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.order-templates.index') }}" class="text-muted">&larr; Back to Templates</a>
            <h1 class="h3 mb-0 mt-2">{{ $orderTemplate->name }}</h1>
        </div>
        <form method="POST" action="{{ route('admin.order-templates.destroy', $orderTemplate) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this template?')">Delete Template</button>
        </form>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Template Details</h5></div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="35%">Customer</th>
                            <td>
                                <a href="{{ route('admin.customers.show', $orderTemplate->customer) }}">
                                    {{ $orderTemplate->customer?->user?->name ?? 'N/A' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $orderTemplate->customer?->user?->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Company</th>
                            <td>{{ $orderTemplate->customer?->company?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Delivery Address</th>
                            <td>{{ $orderTemplate->deliveryAddress?->line_1 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Notes</th>
                            <td>{{ $orderTemplate->notes ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td>{{ $orderTemplate->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $orderTemplate->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Items -->
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Items ({{ count($orderTemplate->items ?? []) }})</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orderTemplate->items ?? [] as $item)
                                    @php $product = $products[$item['product_id'] ?? 0] ?? null; @endphp
                                    <tr>
                                        <td>
                                            @if($product)
                                                {{ $product->name }}
                                                <br><small class="text-muted">SKU: {{ $product->sku ?? '-' }}</small>
                                            @else
                                                Product #{{ $item['product_id'] ?? '?' }}
                                                <br><small class="text-danger">Product may have been deleted</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item['qty'] ?? $item['quantity'] ?? '-' }}</td>
                                        <td class="text-end">
                                            @if($product)
                                                R {{ number_format($product->price ?? 0, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No items.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Linked Recurring Orders -->
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Linked Recurring Orders</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Frequency</th>
                                    <th>Next Run</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recurringOrders as $ro)
                                    <tr>
                                        <td><a href="{{ route('admin.recurring-orders.show', $ro) }}">#{{ $ro->id }}</a></td>
                                        <td>{{ ucfirst($ro->frequency) }}</td>
                                        <td>{{ $ro->next_run_date?->format('d M Y') ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $ro->is_active ? 'success' : 'warning' }}">
                                                {{ $ro->is_active ? 'Active' : 'Paused' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No linked recurring orders.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
