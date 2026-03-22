@extends('layouts.admin')
@section('title', 'Credit Note ' . $creditNote->credit_note_no)

@section('content')
<div class="page-header">
    <h1>{{ $creditNote->credit_note_no }}</h1>
    <div>
        <a href="{{ route('admin.credit-notes.download', $creditNote) }}" class="btn btn-primary btn-sm">Download PDF</a>
        <a href="{{ route('admin.credit-notes.index') }}" class="btn btn-secondary btn-sm">Back</a>
    </div>
</div>

<div class="card mb-2">
    <div class="card-header">Credit Note Details</div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
            <div>
                <p><strong>Credit Note #:</strong> {{ $creditNote->credit_note_no }}</p>
                <p><strong>Date:</strong> {{ $creditNote->created_at->format('d M Y') }}</p>
                <p><strong>Customer:</strong> {{ $creditNote->customer?->user?->name ?? '-' }}</p>
                @if($creditNote->customer?->company)
                    <p><strong>Company:</strong> {{ $creditNote->customer->company->display_name }}</p>
                @endif
            </div>
            <div>
                <p><strong>Invoice:</strong> {{ $creditNote->invoice?->invoice_no ?? '-' }}</p>
                <p><strong>Order:</strong> {{ $creditNote->order?->order_number ?? '-' }}</p>
                <p><strong>Reason:</strong> {{ $creditNote->reason }}</p>
                <p><strong>Issued By:</strong> {{ $creditNote->createdBy?->name ?? '-' }}</p>
            </div>
        </div>

        @if($creditNote->notes)
            <p style="margin-top:1rem;"><strong>Notes:</strong> {{ $creditNote->notes }}</p>
        @endif

        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:0.75rem; margin-top:1rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.06);">
            <div style="text-align:center;">
                <small class="text-muted">Amount (excl. VAT)</small>
                <div class="font-semibold" style="font-size:1.25rem;">R{{ number_format($creditNote->amount, 2) }}</div>
            </div>
            <div style="text-align:center;">
                <small class="text-muted">VAT</small>
                <div class="font-semibold" style="font-size:1.25rem;">R{{ number_format($creditNote->vat_amount, 2) }}</div>
            </div>
            <div style="text-align:center;">
                <small class="text-muted">Total Credit</small>
                <div class="font-semibold" style="font-size:1.25rem; color:var(--danger, #e74c3c);">R{{ number_format($creditNote->total, 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Original Invoice Items --}}
@if($creditNote->order && $creditNote->order->items->count())
<div class="card">
    <div class="card-header">Original Invoice Items</div>
    <div class="table-responsive"><table>
        <thead><tr><th>Product</th><th class="text-right">Qty</th><th class="text-right">Unit Price</th><th class="text-right">Total</th></tr></thead>
        <tbody>
            @foreach($creditNote->order->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td class="text-right">{{ $item->qty }}</td>
                <td class="text-right">R{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">R{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-semibold"><td colspan="3">Invoice Total</td><td class="text-right">R{{ number_format($creditNote->order->total, 2) }}</td></tr>
        </tfoot>
    </table></div>
</div>
@endif
@endsection
