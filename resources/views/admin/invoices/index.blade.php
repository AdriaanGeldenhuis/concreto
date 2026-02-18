@extends('layouts.admin')
@section('title', 'Invoice Register')

@section('content')
<div class="page-header">
    <h1>Invoice Register</h1>
    <a href="{{ route('admin.invoices.export', request()->all()) }}" class="btn btn-primary btn-sm">Export CSV</a>
</div>

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Invoiced</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($totalInvoiced, 2) }}</h3>
        <small class="text-muted">{{ number_format($invoiceCount) }} invoices</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Paid</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">R{{ number_format($totalPaid, 2) }}</h3>
        <small class="text-muted">Payments received</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Outstanding</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--danger, #e74c3c);">R{{ number_format($totalUnpaid, 2) }}</h3>
        <small class="text-muted">Balance due</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Collection Rate</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">{{ $totalInvoiced > 0 ? number_format(($totalPaid / $totalInvoiced) * 100, 1) : '0' }}%</h3>
        <small class="text-muted">Paid / Invoiced</small>
    </div></div>
</div>

{{-- Filters --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="filters">
            <div class="form-group" style="flex:2;">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Invoice #, order #, customer..." value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue (30+)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="form-group">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end; gap:0.35rem;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Invoice Table --}}
<div class="card">
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Invoice #</th>
            <th>Date</th>
            <th>Order #</th>
            <th>Customer</th>
            <th class="text-right">Total</th>
            <th class="text-right">Paid</th>
            <th class="text-right">Balance</th>
            <th>Status</th>
            <th>Emailed</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
            @forelse($invoices as $invoice)
                @php
                    $balanceDue = max(0, $invoice->order_total - $invoice->total_paid);
                    $statusColors = ['paid' => 'success', 'partial' => 'warning', 'unpaid' => 'secondary', 'overdue' => 'danger'];
                @endphp
                <tr style="{{ $invoice->payment_status === 'overdue' ? 'background:rgba(231,76,60,0.05);' : '' }}">
                    <td class="font-semibold">{{ $invoice->invoice_no }}</td>
                    <td style="white-space:nowrap;">{{ $invoice->created_at->format('d M Y') }}</td>
                    <td>
                        @if($invoice->order)
                            <a href="{{ route('admin.orders.show', $invoice->order) }}">{{ $invoice->order->order_number }}</a>
                        @else - @endif
                    </td>
                    <td>
                        @if($invoice->order?->customer)
                            <a href="{{ route('admin.customers.show', $invoice->order->customer) }}">{{ $invoice->order->customer->user->name ?? 'N/A' }}</a>
                        @else - @endif
                    </td>
                    <td class="text-right">R{{ number_format($invoice->order_total, 2) }}</td>
                    <td class="text-right" style="color:var(--success, #27ae60);">R{{ number_format($invoice->total_paid, 2) }}</td>
                    <td class="text-right {{ $balanceDue > 0 ? 'font-semibold' : '' }}" style="color:{{ $balanceDue > 0 ? 'var(--danger, #e74c3c)' : 'var(--success, #27ae60)' }};">
                        R{{ number_format($balanceDue, 2) }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $statusColors[$invoice->payment_status] ?? 'secondary' }}">{{ ucfirst($invoice->payment_status) }}</span>
                    </td>
                    <td>
                        @if($invoice->emailed_at)
                            <span style="color:var(--success, #27ae60);" title="{{ $invoice->emailed_at->format('d M Y H:i') }}">Yes</span>
                        @else
                            <span class="text-muted">No</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('admin.invoices.download', $invoice) }}" class="btn btn-outline btn-sm" title="Download PDF">PDF</a>
                        @if($invoice->order)
                            <a href="{{ route('admin.orders.show', $invoice->order) }}" class="btn btn-outline btn-sm" title="View Order">Order</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <h3>No invoices found</h3>
                            <p>Try adjusting your filters.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@if($invoices->hasPages())
    <div style="display:flex; justify-content:center; margin-top:1rem;">
        {{ $invoices->appends(request()->query())->links() }}
    </div>
@endif
@endsection
