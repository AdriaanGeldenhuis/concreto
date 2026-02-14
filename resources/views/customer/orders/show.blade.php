@extends('layouts.portal')
@section('title', 'Order ' . $order->order_number)
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))

@section('content')
    <div class="page-header">
        <div class="breadcrumb"><a href="{{ route('customer.orders.index') }}">Orders</a> / {{ $order->order_number }}</div>
        <h1>Order {{ $order->order_number }}</h1>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Status</span>
            <span class="badge badge-primary">{{ str_replace('_', ' ', $order->status) }}</span>
        </div>
        <div class="card-body">
            @if($order->status === 'PENDING_PAYMENT')
                <div class="alert alert-warning">Payment required. <a href="{{ route('customer.orders.pay', $order) }}" class="font-bold">Pay Now</a></div>
            @endif

            @if($order->driver)
                <p><strong>Driver:</strong> {{ $order->driver->name }} ({{ $order->driver->phone }})</p>
            @endif
            @if($order->scheduled_date)
                <p><strong>Scheduled:</strong> {{ $order->scheduled_date->format('d M Y') }} {{ $order->scheduled_time_window }}</p>
            @endif
            @if($order->deliveryAddress)
                <p><strong>Delivery:</strong> {{ $order->deliveryAddress->full_address }}</p>
            @endif
            @if($order->notes)
                <p><strong>Notes:</strong> {{ $order->notes }}</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">Items</div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
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
                <tfoot>
                    <tr><td colspan="3" class="text-right font-bold">Subtotal</td><td>R{{ number_format($order->subtotal, 2) }}</td></tr>
                    <tr><td colspan="3" class="text-right font-bold">Delivery Fee</td><td>R{{ number_format($order->delivery_fee, 2) }}</td></tr>
                    <tr><td colspan="3" class="text-right font-bold">VAT (15%)</td><td>R{{ number_format($order->vat, 2) }}</td></tr>
                    <tr><td colspan="3" class="text-right font-bold" style="font-size:1.125rem;">Total</td><td style="font-size:1.125rem;font-weight:800;color:var(--primary);">R{{ number_format($order->total, 2) }}</td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($order->proofOfDelivery)
    <div class="card">
        <div class="card-header">Proof of Delivery</div>
        <div class="card-body">
            <p><strong>Signed by:</strong> {{ $order->proofOfDelivery->signer_name }}</p>
            <p><strong>Signed at:</strong> {{ $order->proofOfDelivery->signed_at->format('d M Y H:i') }}</p>
        </div>
    </div>
    @endif

    @if($order->invoice)
    <div class="card">
        <div class="card-header">Invoice</div>
        <div class="card-body">
            <p><strong>Invoice #:</strong> {{ $order->invoice->invoice_no }}</p>
            <a href="{{ route('customer.invoices.download', $order->invoice) }}" class="btn btn-primary btn-sm">Download PDF</a>
        </div>
    </div>
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Home</a>
    <a href="{{ route('customer.orders.index') }}" class="active"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.orders.create') }}"><span class="nav-icon">&#10010;</span>New Order</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.addresses.index') }}"><span class="nav-icon">&#9873;</span>Addresses</a>
</nav>
@endsection
