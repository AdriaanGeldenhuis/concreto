@extends('layouts.admin')
@section('title', 'Recurring Order #' . $recurringOrder->id)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.recurring-orders.index') }}" class="text-muted">&larr; Back to Recurring Orders</a>
            <h1 class="h3 mb-0 mt-2">Recurring Order #{{ $recurringOrder->id }}</h1>
        </div>
        <div class="btn-group">
            @if($recurringOrder->is_active)
                <form method="POST" action="{{ route('admin.recurring-orders.pause', $recurringOrder) }}">
                    @csrf
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Pause this recurring order?')">Pause</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.recurring-orders.resume', $recurringOrder) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">Resume</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Details -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Details</h5></div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">Customer</th>
                            <td>
                                <a href="{{ route('admin.customers.show', $recurringOrder->customer) }}">
                                    {{ $recurringOrder->customer?->user?->name ?? 'N/A' }}
                                </a>
                            </td>
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
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($recurringOrder->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-warning">Paused</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Frequency</th>
                            <td><span class="badge bg-info">{{ ucfirst($recurringOrder->frequency) }}</span></td>
                        </tr>
                        <tr>
                            <th>Next Run</th>
                            <td class="{{ $recurringOrder->next_run_date?->isPast() ? 'text-danger fw-bold' : '' }}">
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
                    </table>
                </div>
            </div>

            <!-- Update Frequency -->
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Update Schedule</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.recurring-orders.update-frequency', $recurringOrder) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Frequency</label>
                                <select name="frequency" class="form-select">
                                    <option value="weekly" {{ $recurringOrder->frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="fortnightly" {{ $recurringOrder->frequency === 'fortnightly' ? 'selected' : '' }}>Fortnightly</option>
                                    <option value="monthly" {{ $recurringOrder->frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Next Run Date</label>
                                <input type="date" name="next_run_date" class="form-control" value="{{ $recurringOrder->next_run_date?->format('Y-m-d') ?? now()->addDay()->format('Y-m-d') }}" min="{{ now()->addDay()->format('Y-m-d') }}">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Update Schedule</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Items & History -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Order Items</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recurringOrder->items ?? [] as $item)
                                    <tr>
                                        <td>{{ $item['product_id'] ?? '-' }}</td>
                                        <td>{{ $item['qty'] ?? $item['quantity'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-3">No items defined.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0">Generated Orders</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($generatedOrders as $order)
                                    <tr>
                                        <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                        <td>{{ $order->created_at->format('d M Y') }}</td>
                                        <td>R {{ number_format($order->total, 2) }}</td>
                                        <td><span class="badge bg-secondary">{{ $order->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No orders generated yet.</td></tr>
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
