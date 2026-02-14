@extends('layouts.portal')
@section('title', 'Order ' . $order->order_number)
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}" class="active">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('customer.orders.index') }}">Orders</a> / {{ $order->order_number }}
        </div>
        <div class="d-flex justify-between items-center flex-wrap gap-1">
            <h1>Order {{ $order->order_number }}</h1>
            <span class="badge status-{{ $order->status }}
                @switch($order->status)
                    @case('DELIVERED') badge-success @break
                    @case('CANCELLED') badge-danger @break
                    @case('PENDING_PAYMENT') badge-warning @break
                    @case('IN_TRANSIT')
                    @case('ARRIVED') badge-info @break
                    @default badge-primary
                @endswitch
            ">
                {{ str_replace('_', ' ', $order->status) }}
            </span>
        </div>
    </div>

    @if($order->status === 'PENDING_PAYMENT')
        <div class="alert alert-warning">
            Payment required to process this order.
            <a href="{{ route('customer.orders.pay', $order) }}" class="btn btn-sm btn-warning">Pay Now</a>
        </div>
    @endif

    <div class="card">
        <div class="card-header">Order Details</div>
        <div class="card-body">
            @if($order->driver)
                <div class="info-row">
                    <span class="label">Driver</span>
                    <span class="value">{{ $order->driver->name }} ({{ $order->driver->phone }})</span>
                </div>
            @endif
            @if($order->scheduled_date)
                <div class="info-row">
                    <span class="label">Scheduled Delivery</span>
                    <span class="value">{{ $order->scheduled_date->format('d M Y') }} {{ $order->scheduled_time_window }}</span>
                </div>
            @endif
            @if($order->deliveryAddress)
                <div class="info-row">
                    <span class="label">Delivery Address</span>
                    <span class="value">{{ $order->deliveryAddress->full_address }}</span>
                </div>
            @endif
            @if($order->notes)
                <div class="info-row">
                    <span class="label">Notes</span>
                    <span class="value">{{ $order->notes }}</span>
                </div>
            @endif
            <div class="info-row">
                <span class="label">Order Date</span>
                <span class="value">{{ $order->created_at->format('d M Y, H:i') }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Items</div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->qty }} {{ $item->product->unit }}</td>
                        <td>R{{ number_format($item->unit_price, 2) }}</td>
                        <td>R{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <div class="totals-block">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>R{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span>Delivery Fee</span>
                    <span>R{{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span>VAT (15%)</span>
                    <span>R{{ number_format($order->vat, 2) }}</span>
                </div>
                <div class="totals-row total">
                    <span>Total</span>
                    <span>R{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($order->proofOfDelivery)
    <div class="card">
        <div class="card-header">Proof of Delivery</div>
        <div class="card-body">
            <div class="pod-display">
                <div class="info-row">
                    <span class="label">Signed by</span>
                    <span class="value">{{ $order->proofOfDelivery->signer_name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Signed at</span>
                    <span class="value">{{ $order->proofOfDelivery->signed_at->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($order->invoice)
    <div class="card">
        <div class="card-header">Invoice</div>
        <div class="card-body">
            <div class="info-row">
                <span class="label">Invoice #</span>
                <span class="value">{{ $order->invoice->invoice_no }}</span>
            </div>
            <div class="mt-2">
                <a href="{{ route('customer.invoices.download', $order->invoice) }}" class="btn btn-primary btn-sm">Download PDF</a>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">Actions</div>
        <div class="card-body">
            <div class="d-flex gap-1 flex-wrap">
                <a href="{{ route('customer.orders.create') }}" class="btn btn-primary">Reorder</a>

                <button type="button" class="btn btn-outline" onclick="document.getElementById('dispute-form').classList.toggle('d-none')">
                    Dispute Order
                </button>
            </div>

            <div id="dispute-form" class="d-none mt-2">
                <div class="card">
                    <div class="card-header">Report an Issue</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('customer.orders.show', $order) }}">
                            @csrf
                            <input type="hidden" name="dispute" value="1">
                            <div class="form-group">
                                <label class="form-label">Issue Type</label>
                                <select name="dispute_type" class="form-control" required>
                                    <option value="">Select an issue...</option>
                                    <option value="wrong_product">Wrong product delivered</option>
                                    <option value="damaged">Damaged goods</option>
                                    <option value="short_delivery">Short delivery</option>
                                    <option value="late_delivery">Late delivery</option>
                                    <option value="pricing">Pricing discrepancy</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="dispute_description" class="form-control" rows="3"
                                          placeholder="Please describe the issue in detail..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger btn-sm">Submit Dispute</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}" class="active"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.addresses.index') }}"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
