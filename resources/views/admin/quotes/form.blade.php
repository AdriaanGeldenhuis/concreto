@extends('layouts.admin')
@section('title', 'Create Quote')
@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('admin.quotes.index') }}">Quotes</a> / Create
        </div>
        <h1>Create Quote</h1>
    </div>

    <form method="POST" action="{{ route('admin.quotes.store') }}">
        @csrf

        <div class="card">
            <div class="card-header">Quote Details</div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-control" required>
                        <option value="">Select customer...</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->user->name }} ({{ $c->user->email }})</option>
                        @endforeach
                    </select>
                    @error('customer_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Expires At</label>
                        <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                        <div class="form-hint">Leave blank for no expiry.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes for the customer...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span>Line Items</span>
            </div>
            <div class="card-body">
                <div id="quote-items"></div>
                <button type="button" class="btn btn-outline btn-sm mt-1" onclick="addQuoteItem()">+ Add Item</button>
            </div>
        </div>

        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-lg">Create Quote</button>
            <a href="{{ route('admin.quotes.index') }}" class="btn btn-ghost btn-lg">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
let qi = 0;
const qProducts = @json($products);

function addQuoteItem() {
    let opts = '<option value="">Select product...</option>';
    qProducts.forEach(p => {
        opts += '<option value="' + p.id + '" data-price="' + p.price + '">' + p.name + ' (R' + parseFloat(p.price).toFixed(2) + '/' + (p.unit || 'unit') + ')</option>';
    });

    const row = document.createElement('div');
    row.className = 'form-row mb-1';
    row.innerHTML =
        '<div class="form-group" style="flex:3;">' +
            '<label class="form-label">Product</label>' +
            '<select name="items[' + qi + '][product_id]" class="form-control" required onchange="prefillPrice(this, ' + qi + ')">' + opts + '</select>' +
        '</div>' +
        '<div class="form-group" style="flex:1;">' +
            '<label class="form-label">Qty</label>' +
            '<input type="number" name="items[' + qi + '][qty]" class="form-control" placeholder="0" step="0.01" required>' +
        '</div>' +
        '<div class="form-group" style="flex:1;">' +
            '<label class="form-label">Unit Price (R)</label>' +
            '<input type="number" name="items[' + qi + '][unit_price]" id="price_' + qi + '" class="form-control" placeholder="0.00" step="0.01" required>' +
        '</div>' +
        '<div class="form-group" style="flex:0 0 auto; align-self:end;">' +
            '<button type="button" class="btn btn-danger btn-sm" onclick="this.closest(\'.form-row\').remove()">Remove</button>' +
        '</div>';

    document.getElementById('quote-items').appendChild(row);
    qi++;
}

function prefillPrice(select, index) {
    const option = select.selectedOptions[0];
    if (option && option.dataset.price) {
        document.getElementById('price_' + index).value = parseFloat(option.dataset.price).toFixed(2);
    }
}

addQuoteItem();
</script>
@endpush
