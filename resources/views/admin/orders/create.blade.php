@extends('layouts.admin')
@section('title', 'Create Order')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Create New Order</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.orders.store') }}" id="create-order-form">
        @csrf

        <div class="row">
            <!-- Left Column: Customer & Delivery -->
            <div class="col-md-5">
                <!-- Customer Selection -->
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Customer</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Customer</label>
                            <select name="customer_id" id="customer-select" class="form-select" required onchange="updateAddresses()">
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                            data-addresses='@json($customer->addresses)'
                                            data-type="{{ $customer->type }}"
                                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->user->name }} ({{ $customer->user->email }}) - {{ $customer->type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Delivery Address</label>
                            <select name="delivery_address_id" id="address-select" class="form-select" required>
                                <option value="">-- Select customer first --</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Scheduled Date</label>
                                    <input type="date" name="scheduled_date" class="form-control" value="{{ old('scheduled_date') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Time Window</label>
                                    <select name="scheduled_time_window" class="form-select">
                                        <option value="">Any time</option>
                                        <option value="08:00-10:00">08:00 - 10:00</option>
                                        <option value="10:00-12:00">10:00 - 12:00</option>
                                        <option value="12:00-14:00">12:00 - 14:00</option>
                                        <option value="14:00-16:00">14:00 - 16:00</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign Driver (optional)</label>
                            <select name="driver_id" class="form-select">
                                <option value="">-- Assign later --</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Delivery instructions, special requirements...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Products -->
            <div class="col-md-7">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order Items</h5>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">+ Add Item</button>
                    </div>
                    <div class="card-body">
                        <div id="order-items">
                            <div class="order-item row g-2 mb-3 align-items-end" data-index="0">
                                <div class="col-md-5">
                                    <label class="form-label">Product</label>
                                    <select name="items[0][product_id]" class="form-select product-select" required onchange="updatePrice(this)">
                                        <option value="">-- Select Product --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                    data-price="{{ $product->price }}"
                                                    data-unit="{{ $product->unit }}">
                                                {{ $product->name }} ({{ $product->unit }}) - R{{ number_format($product->price, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Qty</label>
                                    <input type="number" name="items[0][qty]" class="form-control qty-input" step="0.01" min="0.01" value="1" required onchange="updateLineTotal(this)">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Unit Price</label>
                                    <input type="text" class="form-control unit-price" readonly value="R 0.00">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Line Total</label>
                                    <input type="text" class="form-control line-total fw-bold" readonly value="R 0.00">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)" title="Remove">&times;</button>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-md-7"></div>
                            <div class="col-md-5">
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <td class="text-muted">Subtotal (excl. VAT)</td>
                                        <td class="text-end" id="order-subtotal">R 0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">VAT (15%)</td>
                                        <td class="text-end" id="order-vat">R 0.00</td>
                                    </tr>
                                    <tr class="fw-bold" style="border-top: 2px solid #dee2e6;">
                                        <td>Estimated Total</td>
                                        <td class="text-end" id="order-total">R 0.00</td>
                                    </tr>
                                </table>
                                <small class="text-muted">Final total includes delivery fee calculated server-side.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg">Create Order</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let itemIndex = 1;

function addItem() {
    const container = document.getElementById('order-items');
    const template = container.querySelector('.order-item').cloneNode(true);
    template.dataset.index = itemIndex;

    template.querySelectorAll('select, input').forEach(el => {
        if (el.name) {
            el.name = el.name.replace(/\[\d+\]/, `[${itemIndex}]`);
        }
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        if (el.classList.contains('qty-input')) el.value = '1';
        if (el.classList.contains('unit-price') || el.classList.contains('line-total')) el.value = 'R 0.00';
    });

    container.appendChild(template);
    itemIndex++;
}

function removeItem(btn) {
    const items = document.querySelectorAll('.order-item');
    if (items.length <= 1) return;
    btn.closest('.order-item').remove();
    recalcTotal();
}

function updatePrice(select) {
    const row = select.closest('.order-item');
    const option = select.options[select.selectedIndex];
    const price = parseFloat(option.dataset.price || 0);
    const qty = parseFloat(row.querySelector('.qty-input').value || 0);

    row.querySelector('.unit-price').value = 'R ' + price.toFixed(2);
    row.querySelector('.line-total').value = 'R ' + (price * qty).toFixed(2);
    recalcTotal();
}

function updateLineTotal(input) {
    const row = input.closest('.order-item');
    const select = row.querySelector('.product-select');
    const option = select.options[select.selectedIndex];
    const price = parseFloat(option.dataset.price || 0);
    const qty = parseFloat(input.value || 0);

    row.querySelector('.line-total').value = 'R ' + (price * qty).toFixed(2);
    recalcTotal();
}

function recalcTotal() {
    let subtotal = 0;
    document.querySelectorAll('.line-total').forEach(el => {
        subtotal += parseFloat(el.value.replace('R ', '') || 0);
    });
    const vat = subtotal * 0.15;
    const total = subtotal + vat;

    document.getElementById('order-subtotal').textContent = 'R ' + subtotal.toFixed(2);
    document.getElementById('order-vat').textContent = 'R ' + vat.toFixed(2);
    document.getElementById('order-total').textContent = 'R ' + total.toFixed(2);
}

function updateAddresses() {
    const select = document.getElementById('customer-select');
    const addressSelect = document.getElementById('address-select');
    const option = select.options[select.selectedIndex];

    addressSelect.innerHTML = '<option value="">-- Select Address --</option>';

    if (!option.value) return;

    try {
        const addresses = JSON.parse(option.dataset.addresses || '[]');
        addresses.forEach(addr => {
            const opt = document.createElement('option');
            opt.value = addr.id;
            const parts = [addr.line1, addr.line2, addr.city, addr.province, addr.postal_code].filter(Boolean);
            opt.textContent = (addr.label ? addr.label + ': ' : '') + parts.join(', ');
            addressSelect.appendChild(opt);
        });
    } catch (e) {}
}

// Initialize on page load if customer was pre-selected
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('customer-select').value) {
        updateAddresses();
    }
});
</script>
@endsection
