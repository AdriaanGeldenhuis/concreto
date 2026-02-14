@extends('layouts.portal')
@section('title', 'Invoices')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))

@section('content')
    <div class="page-header"><h1>My Invoices</h1></div>
    @if($invoices->count())
    <div class="card"><div class="table-responsive">
        <table>
            <thead><tr><th>Invoice #</th><th>Order #</th><th>Date</th><th></th></tr></thead>
            <tbody>
            @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_no }}</td>
                    <td>{{ $invoice->order->order_number ?? '-' }}</td>
                    <td>{{ $invoice->created_at->format('d M Y') }}</td>
                    <td><a href="{{ route('customer.invoices.download', $invoice) }}" class="btn btn-sm btn-primary">Download</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>
    @else
        <div class="empty-state"><div class="icon">&#9993;</div><p>No invoices yet.</p></div>
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Home</a>
    <a href="{{ route('customer.orders.index') }}"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.orders.create') }}"><span class="nav-icon">&#10010;</span>New Order</a>
    <a href="{{ route('customer.invoices.index') }}" class="active"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.addresses.index') }}"><span class="nav-icon">&#9873;</span>Addresses</a>
</nav>
@endsection
