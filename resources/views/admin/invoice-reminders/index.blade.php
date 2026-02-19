@extends('layouts.admin')
@section('title', 'Invoice Reminders')

@section('content')
<div class="page-header">
    <div>
        <h1>Invoice Reminders</h1>
        <small class="text-muted">Track and send payment reminders for overdue invoices.</small>
    </div>
</div>

{{-- Stats --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Reminders Sent</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">{{ $totalReminders }}</h3>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Sent This Month</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">{{ $thisMonth }}</h3>
    </div></div>
    <div class="card" style="border-left:4px solid var(--danger, #e74c3c);"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Overdue (No Recent Reminder)</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--danger, #e74c3c);">{{ $overdueCount }}</h3>
        <small class="text-muted">7+ days since last reminder</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Overdue Value</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($overdueInvoices->sum(fn($inv) => $inv->order?->total ?? 0), 2) }}</h3>
        <small class="text-muted">across {{ $overdueInvoices->count() }} invoices</small>
    </div></div>
</div>

{{-- Overdue Invoices Needing Reminders --}}
@if($overdueInvoices->count() > 0)
<div class="card mb-2">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <span>Invoices Needing Reminders</span>
        <form method="POST" action="{{ route('admin.invoice-reminders.send-bulk') }}">
            @csrf
            @foreach($overdueInvoices as $inv)
                <input type="hidden" name="invoice_ids[]" value="{{ $inv->id }}">
            @endforeach
            <button type="submit" class="btn btn-sm" style="background:var(--warning, #e67e22); color:#fff;" onclick="return confirm('Send reminders for all {{ $overdueInvoices->count() }} overdue invoices?')">
                Send All Reminders ({{ $overdueInvoices->count() }})
            </button>
        </form>
    </div>
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Invoice</th>
            <th>Customer</th>
            <th class="text-right">Amount</th>
            <th>Invoice Date</th>
            <th>Reminders Sent</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
            @foreach($overdueInvoices as $invoice)
                <tr>
                    <td class="font-semibold">{{ $invoice->invoice_no }}</td>
                    <td>
                        {{ $invoice->order?->customer?->user?->name ?? 'N/A' }}
                        <br><small class="text-muted">{{ $invoice->order?->customer?->user?->email ?? '' }}</small>
                    </td>
                    <td class="text-right">R{{ number_format($invoice->order?->total ?? 0, 2) }}</td>
                    <td style="white-space:nowrap;">{{ $invoice->created_at->format('d M Y') }}</td>
                    <td>
                        {{ $invoice->reminders->count() }}
                        @if($invoice->reminders->count() > 0)
                            <br><small class="text-muted">Last: {{ $invoice->reminders->sortByDesc('sent_at')->first()->sent_at->format('d M Y') }}</small>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.invoice-reminders.send', $invoice) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background:var(--warning, #e67e22); color:#fff;" onclick="return confirm('Send reminder for {{ $invoice->invoice_no }}?')">
                                Send Reminder
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table></div>
</div>
@endif

{{-- Reminder History Filters --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" class="filters">
            <div class="form-group" style="flex:2;">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Invoice number or customer..." value="{{ request('search') }}">
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
                @if(request()->hasAny(['search', 'from', 'to']))
                    <a href="{{ route('admin.invoice-reminders.index') }}" class="btn btn-outline btn-sm">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Reminder History --}}
<div class="card">
    <div class="card-header">Reminder History</div>
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Sent At</th>
            <th>Invoice</th>
            <th>Customer</th>
            <th>Reminder #</th>
            <th class="text-right">Amount Owed</th>
        </tr></thead>
        <tbody>
            @forelse($reminders as $reminder)
                <tr>
                    <td style="white-space:nowrap;">{{ $reminder->sent_at?->format('d M Y H:i') ?? '-' }}</td>
                    <td class="font-semibold">{{ $reminder->invoice?->invoice_no ?? '-' }}</td>
                    <td>{{ $reminder->invoice?->order?->customer?->user?->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-{{ $reminder->reminder_number >= 3 ? 'danger' : ($reminder->reminder_number >= 2 ? 'warning' : 'info') }}">
                            #{{ $reminder->reminder_number }}
                        </span>
                    </td>
                    <td class="text-right">R{{ number_format($reminder->invoice?->order?->total ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <h3>No reminders sent yet</h3>
                            <p>Reminders will appear here once sent.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@if($reminders->hasPages())
    <div style="display:flex; justify-content:center; margin-top:1rem;">
        {{ $reminders->appends(request()->query())->links() }}
    </div>
@endif
@endsection
