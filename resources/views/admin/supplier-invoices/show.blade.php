@extends('layouts.admin')
@section('title', 'Supplier Invoice #' . $supplierInvoice->invoice_number)

@section('content')
<div class="page-header">
    <h1>Supplier Invoice #{{ $supplierInvoice->invoice_number }}</h1>
    <div>
        @if($supplierInvoice->status !== 'paid')
            <a href="{{ route('admin.supplier-invoices.edit', $supplierInvoice) }}" class="btn btn-secondary btn-sm">Edit</a>
        @endif
        <a href="{{ route('admin.supplier-invoices.index') }}" class="btn btn-secondary btn-sm">Back</a>
    </div>
</div>

{{-- Invoice Details --}}
<div class="card mb-2">
    <div class="card-header">Invoice Details</div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
            <div>
                <p><strong>Vendor:</strong> {{ $supplierInvoice->vendor->name }}</p>
                <p><strong>Invoice #:</strong> {{ $supplierInvoice->invoice_number }}</p>
                <p><strong>Reference:</strong> {{ $supplierInvoice->reference ?? '-' }}</p>
                @if($supplierInvoice->notes)
                    <p><strong>Notes:</strong> {{ $supplierInvoice->notes }}</p>
                @endif
            </div>
            <div>
                <p><strong>Invoice Date:</strong> {{ $supplierInvoice->invoice_date->format('d M Y') }}</p>
                <p><strong>Due Date:</strong>
                    <span style="{{ $supplierInvoice->is_overdue ? 'color:var(--danger, #e74c3c); font-weight:600;' : '' }}">
                        {{ $supplierInvoice->due_date->format('d M Y') }}
                        @if($supplierInvoice->is_overdue) ({{ $supplierInvoice->days_overdue }} days overdue) @endif
                    </span>
                </p>
                <p><strong>Status:</strong>
                    <span class="badge badge-{{ $supplierInvoice->status === 'paid' ? 'success' : ($supplierInvoice->status === 'partial' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($supplierInvoice->status) }}
                    </span>
                </p>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:0.75rem; margin-top:1rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.06);">
            <div style="text-align:center;">
                <small class="text-muted">Excl. VAT</small>
                <div class="font-semibold" style="font-size:1.1rem;">R{{ number_format($supplierInvoice->amount, 2) }}</div>
            </div>
            <div style="text-align:center;">
                <small class="text-muted">VAT</small>
                <div class="font-semibold" style="font-size:1.1rem;">R{{ number_format($supplierInvoice->vat_amount, 2) }}</div>
            </div>
            <div style="text-align:center;">
                <small class="text-muted">Total</small>
                <div class="font-semibold" style="font-size:1.1rem;">R{{ number_format($supplierInvoice->total, 2) }}</div>
            </div>
            <div style="text-align:center;">
                <small class="text-muted">Balance Due</small>
                <div class="font-semibold" style="font-size:1.1rem; color:{{ $supplierInvoice->balance_due > 0 ? 'var(--danger, #e74c3c)' : 'var(--success, #27ae60)' }};">
                    R{{ number_format($supplierInvoice->balance_due, 2) }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Payment History --}}
<div class="card mb-2">
    <div class="card-header">Payment History</div>
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Date</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Reference</th>
            <th>Notes</th>
            <th>Recorded By</th>
        </tr></thead>
        <tbody>
            @forelse($supplierInvoice->payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                    <td class="font-semibold">R{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ ucfirst($payment->method) }}</td>
                    <td>{{ $payment->reference ?? '-' }}</td>
                    <td>{{ $payment->notes ?? '-' }}</td>
                    <td>{{ $payment->createdBy?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted text-center">No payments recorded yet.</td></tr>
            @endforelse
        </tbody>
        @if($supplierInvoice->payments->count())
        <tfoot>
            <tr class="font-semibold"><td>Total Paid</td><td>R{{ number_format($supplierInvoice->amount_paid, 2) }}</td><td colspan="4"></td></tr>
        </tfoot>
        @endif
    </table></div>
</div>

{{-- Record Payment --}}
@if($supplierInvoice->status !== 'paid')
<div class="card mb-2">
    <div class="card-header">Record Payment</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.supplier-invoices.record-payment', $supplierInvoice) }}" style="max-width:500px;">
            @csrf
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Amount *</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required min="0.01" max="{{ $supplierInvoice->balance_due }}" value="{{ $supplierInvoice->balance_due }}">
                    <small class="text-muted">Max: R{{ number_format($supplierInvoice->balance_due, 2) }}</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Date *</label>
                    <input type="date" name="payment_date" class="form-control" required value="{{ now()->format('Y-m-d') }}">
                </div>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Method *</label>
                    <select name="method" class="form-control" required>
                        <option value="eft">EFT</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="cheque">Cheque</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Reference</label>
                    <input type="text" name="reference" class="form-control" placeholder="Bank ref, cheque #...">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Record Payment</button>
        </form>
    </div>
</div>
@endif

{{-- Delete --}}
@if(!$supplierInvoice->payments()->exists())
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.supplier-invoices.destroy', $supplierInvoice) }}" onsubmit="return confirm('Delete this supplier invoice? This cannot be undone.');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Delete Invoice</button>
        </form>
    </div>
</div>
@endif
@endsection
