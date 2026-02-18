@extends('layouts.admin')
@section('title', 'Recurring Order #' . $recurringOrder->id)

@section('content')
<div class="page-header">
    <div>
        <a href="{{ route('admin.recurring-orders.index') }}" class="text-muted" style="font-size:0.85rem;">&larr; Back to Recurring Orders</a>
        <h1 style="margin-top:0.25rem;">Recurring Order #{{ $recurringOrder->id }}</h1>
    </div>
    @if($recurringOrder->is_active)
        <form method="POST" action="{{ route('admin.recurring-orders.pause', $recurringOrder) }}">
            @csrf
            <button type="submit" class="btn btn-sm" style="background:var(--warning, #e67e22); color:#fff;" onclick="return confirm('Pause this recurring order?')">Pause</button>
        </form>
    @else
        <form method="POST" action="{{ route('admin.recurring-orders.resume', $recurringOrder) }}">
            @csrf
            <button type="submit" class="btn btn-sm" style="background:var(--success, #27ae60); color:#fff;">Resume</button>
        </form>
    @endif
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
    {{-- Left Column --}}
    <div>
        {{-- Details --}}
        <div class="card mb-2">
            <div class="card-header">Details</div>
            <div class="table-responsive"><table>
                <tr>
                    <th style="width:40%;">Customer</th>
                    <td><a href="{{ route('admin.customers.show', $recurringOrder->customer) }}">{{ $recurringOrder->customer?->user?->name ?? 'N/A' }}</a></td>
                </tr>
                <tr>
                    <th>Company</th>
                    <td>{{ $recurringOrder->customer?->company?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Template</th>
                    <td>
                        @if($recurringOrder->template)
                            <a href="{{ route('admin.order-templates.show', $recurringOrder->template) }}">{{ $recurringOrder->template->name }}</a>
                        @else - @endif
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><span class="badge badge-{{ $recurringOrder->is_active ? 'success' : 'warning' }}">{{ $recurringOrder->is_active ? 'Active' : 'Paused' }}</span></td>
                </tr>
                <tr>
                    <th>Frequency</th>
                    <td><span class="badge badge-info">{{ ucfirst($recurringOrder->frequency) }}</span></td>
                </tr>
                <tr>
                    <th>Next Run</th>
                    <td style="{{ $recurringOrder->next_run_date?->isPast() ? 'color:var(--danger, #e74c3c); font-weight:600;' : '' }}">
                        {{ $recurringOrder->next_run_date?->format('d M Y') ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <th>Last Run</th>
                    <td>{{ $recurringOrder->last_run_date?->format('d M Y') ?? 'Never' }}</td>
                </tr>
                <tr>
                    <th>Delivery Address</th>
                    <td>{{ $recurringOrder->deliveryAddress?->line_1 ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Notes</th>
                    <td>{{ $recurringOrder->notes ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Created</th>
                    <td>{{ $recurringOrder->created_at->format('d M Y H:i') }}</td>
                </tr>
            </table></div>
        </div>

        {{-- Update Schedule --}}
        <div class="card mb-2">
            <div class="card-header">Update Schedule</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.recurring-orders.update-frequency', $recurringOrder) }}">
                    @csrf
                    @method('PUT')
                    <div class="filters" style="flex-wrap:wrap;">
                        <div class="form-group">
                            <label class="form-label">Frequency</label>
                            <select name="frequency" class="form-control">
                                <option value="weekly" {{ $recurringOrder->frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="biweekly" {{ $recurringOrder->frequency === 'biweekly' ? 'selected' : '' }}>Biweekly</option>
                                <option value="monthly" {{ $recurringOrder->frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Next Run Date</label>
                            <input type="date" name="next_run_date" class="form-control" value="{{ $recurringOrder->next_run_date?->format('Y-m-d') ?? now()->addDay()->format('Y-m-d') }}" min="{{ now()->addDay()->format('Y-m-d') }}">
                        </div>
                        <div class="form-group" style="display:flex; align-items:flex-end;">
                            <button type="submit" class="btn btn-primary btn-sm">Update Schedule</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Right Column --}}
    <div>
        {{-- Order Items --}}
        <div class="card mb-2">
            <div class="card-header">Order Items</div>
            <div class="table-responsive"><table>
                <thead><tr>
                    <th>Product ID</th>
                    <th class="text-right">Quantity</th>
                </tr></thead>
                <tbody>
                    @forelse($recurringOrder->items ?? [] as $item)
                        <tr>
                            <td>{{ $item['product_id'] ?? '-' }}</td>
                            <td class="text-right">{{ $item['qty'] ?? $item['quantity'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2"><div class="empty-state"><h3>No items defined</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>

        {{-- Generated Orders --}}
        <div class="card">
            <div class="card-header">Generated Orders</div>
            <div class="table-responsive"><table>
                <thead><tr>
                    <th>Order</th>
                    <th>Date</th>
                    <th class="text-right">Total</th>
                    <th>Status</th>
                </tr></thead>
                <tbody>
                    @forelse($generatedOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td class="text-right">R{{ number_format($order->total, 2) }}</td>
                            <td><span class="badge badge-secondary">{{ $order->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state"><h3>No orders generated yet</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>
@endsection
