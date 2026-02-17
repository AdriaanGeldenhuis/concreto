@extends('layouts.admin')
@section('title', 'Invoice Register')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Invoice Register</h1>
        <a href="{{ route('admin.invoices.export', request()->all()) }}" class="btn btn-primary">
            Export CSV
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Invoiced</h6>
                    <h3 class="mb-0">R {{ number_format($totalInvoiced, 2) }}</h3>
                    <small class="text-muted">{{ number_format($invoiceCount) }} invoices</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Paid</h6>
                    <h3 class="mb-0 text-success">R {{ number_format($totalPaid, 2) }}</h3>
                    <small class="text-muted">Payments received</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Outstanding</h6>
                    <h3 class="mb-0 text-danger">R {{ number_format($totalUnpaid, 2) }}</h3>
                    <small class="text-muted">Balance due</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Collection Rate</h6>
                    <h3 class="mb-0">{{ $totalInvoiced > 0 ? number_format(($totalPaid / $totalInvoiced) * 100, 1) : '0' }}%</h3>
                    <small class="text-muted">Paid / Invoiced</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.invoices.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Invoice #, order #, customer..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue (30+ days)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoice Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Emailed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            @php
                                $balanceDue = max(0, $invoice->order_total - $invoice->total_paid);
                                $statusColors = [
                                    'paid' => 'success',
                                    'partial' => 'warning',
                                    'unpaid' => 'secondary',
                                    'overdue' => 'danger',
                                ];
                            @endphp
                            <tr class="{{ $invoice->payment_status === 'overdue' ? 'table-danger' : '' }}">
                                <td class="fw-bold">{{ $invoice->invoice_no }}</td>
                                <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                <td>
                                    @if($invoice->order)
                                        <a href="{{ route('admin.orders.show', $invoice->order) }}">{{ $invoice->order->order_number }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($invoice->order?->customer)
                                        <a href="{{ route('admin.customers.show', $invoice->order->customer) }}">
                                            {{ $invoice->order->customer->user->name ?? 'N/A' }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">R {{ number_format($invoice->order_total, 2) }}</td>
                                <td class="text-end text-success">R {{ number_format($invoice->total_paid, 2) }}</td>
                                <td class="text-end {{ $balanceDue > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                    R {{ number_format($balanceDue, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $statusColors[$invoice->payment_status] ?? 'secondary' }}">
                                        {{ ucfirst($invoice->payment_status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($invoice->emailed_at)
                                        <span class="text-success" title="{{ $invoice->emailed_at->format('d M Y H:i') }}">Yes</span>
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.invoices.download', $invoice) }}" class="btn btn-outline-primary" title="Download PDF">PDF</a>
                                        @if($invoice->order)
                                            <a href="{{ route('admin.orders.show', $invoice->order) }}" class="btn btn-outline-secondary" title="View Order">Order</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($invoices->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $invoices->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
