@extends('layouts.admin')
@section('title', 'Invoice Reminders')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Invoice Reminders</h1>
            <small class="text-muted">Track and send payment reminders for overdue invoices. Reminders are emailed to the customer on file.</small>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Total Reminders Sent</small>
                    <h5 class="mb-0">{{ $totalReminders }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Sent This Month</small>
                    <h5 class="mb-0">{{ $thisMonth }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid var(--danger, #e74a3b);">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Overdue (No Recent Reminder)</small>
                    <h5 class="mb-0 text-danger">{{ $overdueCount }}</h5>
                    <small class="text-muted">7+ days since last reminder</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Total Overdue Value</small>
                    <h5 class="mb-0">R {{ number_format($overdueInvoices->sum(fn($inv) => $inv->order?->total ?? 0), 2) }}</h5>
                    <small class="text-muted">across {{ $overdueInvoices->count() }} invoices</small>
                </div>
            </div>
        </div>
    </div>

    @if($overdueInvoices->count() > 0)
    <!-- Overdue Invoices Needing Reminders -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Invoices Needing Reminders</h5>
            <form method="POST" action="{{ route('admin.invoice-reminders.send-bulk') }}">
                @csrf
                @foreach($overdueInvoices as $inv)
                    <input type="hidden" name="invoice_ids[]" value="{{ $inv->id }}">
                @endforeach
                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Send reminders for all {{ $overdueInvoices->count() }} overdue invoices?')">
                    Send All Reminders ({{ $overdueInvoices->count() }})
                </button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Invoice Date</th>
                            <th>Reminders Sent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overdueInvoices as $invoice)
                            <tr>
                                <td class="fw-bold">{{ $invoice->invoice_no }}</td>
                                <td>
                                    {{ $invoice->order?->customer?->user?->name ?? 'N/A' }}
                                    <br><small class="text-muted">{{ $invoice->order?->customer?->user?->email ?? '' }}</small>
                                </td>
                                <td>R {{ number_format($invoice->order?->total ?? 0, 2) }}</td>
                                <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                <td>
                                    {{ $invoice->reminders->count() }}
                                    @if($invoice->reminders->count() > 0)
                                        <br><small class="text-muted">Last: {{ $invoice->reminders->sortByDesc('sent_at')->first()->sent_at->format('d M Y') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.invoice-reminders.send', $invoice) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Send reminder for {{ $invoice->invoice_no }}?')">
                                            Send Reminder
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Reminder History -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1" style="font-size: 0.85rem;">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice number or customer..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1" style="font-size: 0.85rem;">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1" style="font-size: 0.85rem;">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['search', 'from', 'to']))
                        <a href="{{ route('admin.invoice-reminders.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Reminder History</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Sent At</th>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Reminder #</th>
                            <th>Amount Owed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reminders as $reminder)
                            <tr>
                                <td class="text-nowrap">{{ $reminder->sent_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="fw-bold">{{ $reminder->invoice?->invoice_no ?? '-' }}</td>
                                <td>{{ $reminder->invoice?->order?->customer?->user?->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $reminder->reminder_number >= 3 ? 'danger' : ($reminder->reminder_number >= 2 ? 'warning' : 'info') }}">
                                        #{{ $reminder->reminder_number }}
                                    </span>
                                </td>
                                <td>R {{ number_format($reminder->invoice?->order?->total ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No reminders sent yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($reminders->hasPages())
        <div class="mt-3">{!! $reminders->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
</div>
@endsection
