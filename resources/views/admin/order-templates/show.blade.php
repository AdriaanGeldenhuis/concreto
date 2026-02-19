@extends('layouts.admin')
@section('title', 'Template: ' . $orderTemplate->name)

@section('content')
<div class="page-header">
    <div>
        <a href="{{ route('admin.order-templates.index') }}" class="text-muted" style="font-size:0.85rem;">&larr; Back to Templates</a>
        <h1 style="margin-top:0.25rem;">{{ $orderTemplate->name }}</h1>
    </div>
    <form method="POST" action="{{ route('admin.order-templates.destroy', $orderTemplate) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this template?')">Delete Template</button>
    </form>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
    {{-- Left: Details --}}
    <div class="card">
        <div class="card-header">Template Details</div>
        <div class="table-responsive"><table>
            <tr>
                <th style="width:35%;">Customer</th>
                <td><a href="{{ route('admin.customers.show', $orderTemplate->customer) }}">{{ $orderTemplate->customer?->user?->name ?? 'N/A' }}</a></td>
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
        </table></div>
    </div>

    {{-- Right: Items + Linked Recurring --}}
    <div>
        <div class="card mb-2">
            <div class="card-header">Items ({{ count($orderTemplate->items ?? []) }})</div>
            <div class="table-responsive"><table>
                <thead><tr>
                    <th>Product</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                </tr></thead>
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
                                    <br><small style="color:var(--danger, #e74c3c);">Product may have been deleted</small>
                                @endif
                            </td>
                            <td class="text-right">{{ $item['qty'] ?? $item['quantity'] ?? '-' }}</td>
                            <td class="text-right">
                                @if($product)
                                    R{{ number_format($product->price ?? 0, 2) }}
                                @else - @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><h3>No items</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>

        <div class="card">
            <div class="card-header">Linked Recurring Orders</div>
            <div class="table-responsive"><table>
                <thead><tr>
                    <th>ID</th>
                    <th>Frequency</th>
                    <th>Next Run</th>
                    <th>Status</th>
                </tr></thead>
                <tbody>
                    @forelse($recurringOrders as $ro)
                        <tr>
                            <td><a href="{{ route('admin.recurring-orders.show', $ro) }}">#{{ $ro->id }}</a></td>
                            <td>{{ ucfirst($ro->frequency) }}</td>
                            <td>{{ $ro->next_run_date?->format('d M Y') ?? '-' }}</td>
                            <td><span class="badge badge-{{ $ro->is_active ? 'success' : 'warning' }}">{{ $ro->is_active ? 'Active' : 'Paused' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state"><h3>No linked recurring orders</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>
@endsection
