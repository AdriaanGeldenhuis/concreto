@extends('layouts.admin')
@section('title', 'New Supplier Invoice')

@section('content')
<div class="page-header">
    <h1>New Supplier Invoice</h1>
    <a href="{{ route('admin.supplier-invoices.index') }}" class="btn btn-secondary btn-sm">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.supplier-invoices.store') }}" style="max-width:600px;">
            @csrf

            <div class="form-group">
                <label class="form-label">Vendor / Supplier *</label>
                <select name="vendor_id" class="form-control" required>
                    <option value="">Select vendor...</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                    @endforeach
                </select>
                @error('vendor_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Invoice Number *</label>
                <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number') }}" required placeholder="Supplier's invoice #">
                @error('invoice_number')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Invoice Date *</label>
                    <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                    @error('invoice_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date *</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}" required>
                    @error('due_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Amount (excl. VAT) *</label>
                    <input type="number" step="0.01" name="amount" id="si-amount" class="form-control" value="{{ old('amount') }}" required min="0.01" oninput="calcTotal()">
                    @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">VAT Amount</label>
                    <input type="number" step="0.01" name="vat_amount" id="si-vat" class="form-control" value="{{ old('vat_amount', '0') }}" min="0" oninput="calcTotal()">
                    <small class="text-muted"><a href="#" onclick="autoCalcVat(); return false;">Auto-calc {{ $vatRate }}% VAT</a></small>
                    @error('vat_amount')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Total (incl. VAT)</label>
                <div id="si-total" style="font-size:1.25rem; font-weight:600; padding:0.5rem 0;">R0.00</div>
            </div>

            <div class="form-group">
                <label class="form-label">Reference</label>
                <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="PO number, delivery note, etc.">
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Invoice</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
var vatRate = {{ $vatRate }};
function calcTotal() {
    var a = parseFloat(document.getElementById('si-amount').value) || 0;
    var v = parseFloat(document.getElementById('si-vat').value) || 0;
    document.getElementById('si-total').textContent = 'R' + (a + v).toFixed(2);
}
function autoCalcVat() {
    var a = parseFloat(document.getElementById('si-amount').value) || 0;
    document.getElementById('si-vat').value = (a * vatRate / 100).toFixed(2);
    calcTotal();
}
calcTotal();
</script>
@endpush
@endsection
