@extends('layouts.portal')
@section('title', 'Quote Q-' . $quote->id)
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('customer.quotes.index') }}">Quotes</a> / Q-{{ $quote->id }}
        </div>
        <div class="d-flex justify-between items-center flex-wrap gap-1">
            <h1>Quote Q-{{ $quote->id }}</h1>
            <span class="badge
                @switch($quote->status)
                    @case('approved') badge-success @break
                    @case('expired')
                    @case('rejected') badge-danger @break
                    @case('sent') badge-info @break
                    @default badge-secondary
                @endswitch
            ">
                {{ ucfirst($quote->status) }}
            </span>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Quote Items</div>
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
                @foreach($quote->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>R{{ number_format($item->unit_price, 2) }}</td>
                        <td>R{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <div class="totals-block">
                <div class="totals-row total">
                    <span>Total</span>
                    <span>R{{ number_format($quote->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($quote->notes)
    <div class="card">
        <div class="card-header">Notes</div>
        <div class="card-body">
            <p>{{ $quote->notes }}</p>
        </div>
    </div>
    @endif

    @if($quote->expires_at)
    <div class="card">
        <div class="card-body">
            <div class="info-row">
                <span class="label">Valid Until</span>
                <span class="value">{{ $quote->expires_at->format('d M Y') }}</span>
            </div>
        </div>
    </div>
    @endif

    @if($quote->status === 'sent')
    <div class="card">
        <div class="card-body text-center">
            <p class="text-muted mb-2">Ready to proceed? Approve this quote to create an order.</p>
            <form method="POST" action="{{ route('customer.quotes.approve', $quote) }}">
                @csrf
                <button type="submit" class="btn btn-success btn-lg">Approve & Create Order</button>
            </form>
        </div>
    </div>
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.addresses.index') }}"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
