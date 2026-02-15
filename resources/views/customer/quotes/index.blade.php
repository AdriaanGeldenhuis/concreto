@extends('layouts.portal')
@section('title', 'Quotes')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="page-header">
        <h1>My Quotes</h1>
    </div>

    @if($quotes->count())
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Expires</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($quotes as $quote)
                    <tr>
                        <td><strong>Q-{{ $quote->id }}</strong></td>
                        <td>
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
                        </td>
                        <td>R{{ number_format($quote->total, 2) }}</td>
                        <td>
                            @if($quote->expires_at)
                                {{ $quote->expires_at->format('d M Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('customer.quotes.show', $quote) }}" class="btn btn-sm btn-outline">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="empty-state">
            <div class="icon">&#9997;</div>
            <h3>No quotes yet</h3>
            <p>Quotes from the team will appear here.</p>
        </div>
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.account') }}"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
