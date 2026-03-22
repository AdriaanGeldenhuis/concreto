@extends('layouts.admin')
@section('title', 'Accounts Payable - Supplier Invoices')

@section('content')
<div class="page-header">
    <h1>Accounts Payable</h1>
    <div>
        <a href="{{ route('admin.supplier-invoices.aging') }}" class="btn btn-secondary btn-sm">AP Aging Report</a>
        <a href="{{ route('admin.supplier-invoices.export', request()->query()) }}" class="btn btn-secondary btn-sm">Export CSV</a>
        <a href="{{ route('admin.supplier-invoices.create') }}" class="btn btn-primary btn-sm">+ New Invoice</a>
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Owed</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($totalUnpaid, 2) }}</h3>
    </div></div>
    <div class="card" style="border-left:4px solid var(--danger, #e74c3c);"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Overdue</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--danger, #e74c3c);">R{{ number_format($totalOverdue, 2) }}</h3>
        <small class="text-muted">{{ $overdueCount }} invoices</small>
    </div></div>
    <div class="card" style="border-left:4px solid var(--warning, #e67e22);"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Due This Month</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($thisMonthDue, 2) }}</h3>
    </div></div>
</div>

{{-- Filters --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" action="{{ route('admin.supplier-invoices.index') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Invoice #, vendor, reference..." value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Vendor</label>
                <select name="vendor_id" class="form-control">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                    @endforeach
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
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Invoice List --}}
<div class="card">
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Vendor</th>
            <th>Invoice #</th>
            <th>Date</th>
            <th>Due Date</th>
            <th class="text-right">Total</th>
            <th class="text-right">Paid</th>
            <th class="text-right">Balance</th>
            <th>Status</th>
            <th></th>
        </tr></thead>
        <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td>{{ $inv->vendor->name }}</td>
                    <td><a href="{{ route('admin.supplier-invoices.show', $inv) }}">{{ $inv->invoice_number }}</a></td>
                    <td>{{ $inv->invoice_date->format('d M Y') }}</td>
                    <td style="{{ $inv->is_overdue ? 'color:var(--danger, #e74c3c); font-weight:600;' : '' }}">
                        {{ $inv->due_date->format('d M Y') }}
                        @if($inv->is_overdue)<br><small>{{ $inv->days_overdue }}d overdue</small>@endif
                    </td>
                    <td class="text-right">R{{ number_format($inv->total, 2) }}</td>
                    <td class="text-right">R{{ number_format($inv->amount_paid, 2) }}</td>
                    <td class="text-right font-semibold">R{{ number_format($inv->balance_due, 2) }}</td>
                    <td>
                        <span class="badge badge-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'partial' ? 'warning' : ($inv->is_overdue ? 'danger' : 'secondary')) }}">
                            {{ ucfirst($inv->status) }}{{ $inv->is_overdue && $inv->status !== 'paid' ? ' (Overdue)' : '' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.supplier-invoices.show', $inv) }}" class="btn btn-secondary btn-sm">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-muted text-center" style="padding:2rem;">No supplier invoices found.</td></tr>
            @endforelse
        </tbody>
    </table></div>
    @if($invoices->hasPages())
        <div style="padding: 0.75rem;">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
