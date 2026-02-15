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

    {{-- Statement Download --}}
    <div class="card mb-2">
        <div class="card-header">Download Statement</div>
        <div class="card-body">
            <form method="GET" action="{{ route('customer.statement') }}" class="d-flex gap-1 items-end flex-wrap">
                <div class="form-group" style="margin-bottom:0; flex:1; min-width:140px;">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                </div>
                <div class="form-group" style="margin-bottom:0; flex:1; min-width:140px;">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="min-height:42px;">Download PDF</button>
            </form>
        </div>
    </div>

    @if($invoices->count())
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Order #</th>
                        <th>Amount</th>
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
                        <td class="font-bold">
                            @if($invoice->order)
                                R{{ number_format($invoice->order->total, 2) }}
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
    @if($invoices->hasPages())
        <div class="pagination">{!! $invoices->links('pagination::simple-default') !!}</div>
    @endif
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
