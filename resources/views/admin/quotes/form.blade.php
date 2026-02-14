@extends('layouts.admin')
@section('title', 'Create Quote')
@section('content')
    <div class="page-header"><h1>Create Quote</h1></div>
    <form method="POST" action="{{ route('admin.quotes.store') }}">
        @csrf
        <div class="card"><div class="card-body">
            <div class="form-group"><label class="form-label">Customer</label><select name="customer_id" class="form-control" required><option value="">Select customer</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->user->name }} ({{ $c->user->email }})</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Expires At</label><input type="date" name="expires_at" class="form-control"></div>
            <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
        </div></div>

        <div class="card">
            <div class="card-header">Items</div>
            <div class="card-body">
                <div id="quote-items"></div>
                <button type="button" class="btn btn-primary btn-sm" onclick="addQuoteItem()">+ Add Item</button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg">Create Quote</button>
    </form>
@endsection

@push('scripts')
<script>
let qi = 0;
const qProducts = @json($products);
function addQuoteItem() {
    let opts = '<option value="">Select</option>';
    qProducts.forEach(p => { opts += '<option value="'+p.id+'" data-price="'+p.price+'">'+p.name+' (R'+parseFloat(p.price).toFixed(2)+')</option>'; });
    document.getElementById('quote-items').insertAdjacentHTML('beforeend',
        '<div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;flex-wrap:wrap;">' +
        '<select name="items['+qi+'][product_id]" class="form-control" style="flex:2;" required>'+opts+'</select>' +
        '<input type="number" name="items['+qi+'][qty]" class="form-control" placeholder="Qty" step="0.01" style="flex:1;" required>' +
        '<input type="number" name="items['+qi+'][unit_price]" class="form-control" placeholder="Price" step="0.01" style="flex:1;" required>' +
        '</div>');
    qi++;
}
addQuoteItem();
</script>
@endpush
