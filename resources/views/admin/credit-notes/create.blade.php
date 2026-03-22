@extends('layouts.admin')
@section('title', 'Issue Credit Note')

@section('content')
<div class="page-header">
    <h1>Issue Credit Note</h1>
    <a href="{{ route('admin.credit-notes.index') }}" class="btn btn-secondary btn-sm">Back</a>
</div>

@if(!$invoice)
{{-- Invoice search --}}
<div class="card mb-2">
    <div class="card-header">Find Invoice</div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.credit-notes.create') }}" style="max-width:400px;">
            <div class="form-group">
                <label class="form-label">Invoice ID</label>
                <input type="number" name="invoice_id" class="form-control" placeholder="Enter Invoice ID" required>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Find Invoice</button>
        </form>
        <p class="text-muted" style="margin-top:1rem;">
            Tip: You can also issue a credit note from the invoice register or order detail page.
        </p>
    </div>
</div>
@else
{{-- Invoice details + credit note form --}}
<div class="card mb-2">
    <div class="card-header">Invoice: {{ $invoice->invoice_no }}</div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1rem;">
            <div>
                <p><strong>Customer:</strong> {{ $invoice->order->customer->user->name ?? '-' }}</p>
                <p><strong>Order:</strong> {{ $invoice->order->order_number }}</p>
            </div>
            <div>
                <p><strong>Invoice Total:</strong> R{{ number_format($invoice->order->total, 2) }}</p>
                <p><strong>Invoice Date:</strong> {{ $invoice->created_at->format('d M Y') }}</p>
            </div>
        </div>

        {{-- Items --}}
        <div class="table-responsive"><table>
            <thead><tr><th>Product</th><th class="text-right">Qty</th><th class="text-right">Unit Price</th><th class="text-right">Total</th></tr></thead>
            <tbody>
                @foreach($invoice->order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">{{ $item->qty }}</td>
                    <td class="text-right">R{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">R{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>
</div>

<div class="card">
    <div class="card-header">Credit Note Details</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.credit-notes.store') }}" style="max-width:500px;">
            @csrf
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

            <div class="form-group">
                <label class="form-label">Credit Amount (excl. VAT) *</label>
                <input type="number" step="0.01" name="amount" id="cn-amount" class="form-control" required min="0.01" max="{{ $invoice->order->subtotal }}" oninput="calcCnTotal()">
                <small class="text-muted">Max: R{{ number_format($invoice->order->subtotal, 2) }} (excl. VAT)</small>
                @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">VAT (auto-calculated at {{ $vatRate }}%)</label>
                <div id="cn-vat" style="font-weight:600;">R0.00</div>
            </div>

            <div class="form-group">
                <label class="form-label">Total Credit (incl. VAT)</label>
                <div id="cn-total" style="font-size:1.25rem; font-weight:600; color:var(--danger, #e74c3c);">R0.00</div>
            </div>

            <div class="form-group">
                <label class="form-label">Reason *</label>
                <select name="reason" class="form-control" required onchange="if(this.value==='other'){document.getElementById('cn-reason-other').style.display='block';}else{document.getElementById('cn-reason-other').style.display='none';}">
                    <option value="">Select reason...</option>
                    <option value="Goods returned">Goods returned</option>
                    <option value="Damaged goods">Damaged goods</option>
                    <option value="Wrong product delivered">Wrong product delivered</option>
                    <option value="Pricing error">Pricing error</option>
                    <option value="Short delivery">Short delivery</option>
                    <option value="other">Other (specify)</option>
                </select>
                <input type="text" id="cn-reason-other" name="reason_other" class="form-control" style="display:none; margin-top:0.5rem;" placeholder="Specify reason...">
                @error('reason')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Issue Credit Note</button>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
var vatRate = {{ $vatRate }};
function calcCnTotal() {
    var a = parseFloat(document.getElementById('cn-amount').value) || 0;
    var v = (a * vatRate / 100);
    document.getElementById('cn-vat').textContent = 'R' + v.toFixed(2);
    document.getElementById('cn-total').textContent = 'R' + (a + v).toFixed(2);
}
// Handle "other" reason - replace select value
document.querySelector('form').addEventListener('submit', function(e) {
    var sel = document.querySelector('select[name="reason"]');
    var other = document.getElementById('cn-reason-other');
    if (sel.value === 'other' && other.value) {
        sel.name = '_reason_select';
        other.name = 'reason';
    }
});
</script>
@endpush
@endsection
