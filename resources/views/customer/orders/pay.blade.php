@extends('layouts.portal')
@section('title', 'Pay Order')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}" class="active">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="page-header text-center">
        <div class="breadcrumb">
            <a href="{{ route('customer.orders.show', $order) }}">Order {{ $order->order_number }}</a> / Payment
        </div>
        <h1>Complete Payment</h1>
    </div>

    <div class="payment-card">
        <p class="text-muted">Amount due for order {{ $order->order_number }}</p>
        <div class="amount">R{{ number_format($order->total, 2) }}</div>
        <form method="POST" action="{{ route('customer.orders.pay.create', $order) }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-lg btn-block">Pay with Yoco</button>
        </form>
        <div class="secure-note">
            &#128274; You will be redirected to Yoco's secure payment page
        </div>
    </div>
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}" class="active"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.account') }}"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
