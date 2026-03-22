@extends('layouts.admin')
@section('title', 'Credit Notes')

@section('content')
<div class="page-header">
    <h1>Credit Notes</h1>
    <div>
        <a href="{{ route('admin.credit-notes.export', request()->query()) }}" class="btn btn-secondary btn-sm">Export CSV</a>
        <a href="{{ route('admin.credit-notes.create') }}" class="btn btn-primary btn-sm">+ Issue Credit Note</a>
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Credits Issued</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($totalCreditNotes, 2) }}</h3>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">This Month</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($thisMonthTotal, 2) }}</h3>
    </div></div>
</div>

{{-- Filters --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" action="{{ route('admin.credit-notes.index') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="CN #, invoice #, customer..." value="{{ request('search') }}">
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

{{-- Credit Notes List --}}
<div class="card">
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Credit Note #</th>
            <th>Date</th>
            <th>Invoice #</th>
            <th>Customer</th>
            <th class="text-right">Amount</th>
            <th class="text-right">VAT</th>
            <th class="text-right">Total</th>
            <th>Reason</th>
            <th></th>
        </tr></thead>
        <tbody>
            @forelse($creditNotes as $cn)
                <tr>
                    <td><a href="{{ route('admin.credit-notes.show', $cn) }}">{{ $cn->credit_note_no }}</a></td>
                    <td>{{ $cn->created_at->format('d M Y') }}</td>
                    <td>{{ $cn->invoice?->invoice_no ?? '-' }}</td>
                    <td>{{ $cn->customer?->user?->name ?? '-' }}</td>
                    <td class="text-right">R{{ number_format($cn->amount, 2) }}</td>
                    <td class="text-right">R{{ number_format($cn->vat_amount, 2) }}</td>
                    <td class="text-right font-semibold">R{{ number_format($cn->total, 2) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($cn->reason, 40) }}</td>
                    <td>
                        <a href="{{ route('admin.credit-notes.download', $cn) }}" class="btn btn-secondary btn-sm">PDF</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-muted text-center" style="padding:2rem;">No credit notes issued yet.</td></tr>
            @endforelse
        </tbody>
    </table></div>
    @if($creditNotes->hasPages())
        <div style="padding: 0.75rem;">{{ $creditNotes->links() }}</div>
    @endif
</div>
@endsection
