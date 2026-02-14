@extends('layouts.portal')
@section('title', 'Invoices')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}">Orders</a>
    <a href="{{ route('customer.invoices.index') }}" class="active">Invoices</a>
@endsection

@section('content')
    <div class="page-header">
        <h1>My Invoices</h1>
    </div>

    @if($invoices->count())
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Order #</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td><strong>{{ $invoice->invoice_no }}</strong></td>
                        <td>
                            @if($invoice->order)
                                <a href="{{ route('customer.orders.show', $invoice->order) }}">
                                    {{ $invoice->order->order_number }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $invoice->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('customer.invoices.download', $invoice) }}" class="btn btn-sm btn-primary">
                                Download
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="empty-state">
            <div class="icon">&#9993;</div>
            <h3>No invoices yet</h3>
            <p>Invoices will appear here once your orders are processed.</p>
        </div>
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}" class="active"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.addresses.index') }}"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
