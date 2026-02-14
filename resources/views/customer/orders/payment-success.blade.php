@extends('layouts.portal')
@section('title', 'Payment Successful')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}" class="active">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="success-page">
        <div class="success-icon">&#10003;</div>
        <h2>Payment Successful!</h2>
        <p class="text-muted mb-2">
            Your payment for order <strong>{{ $order->order_number }}</strong> has been confirmed.
        </p>
        <p class="text-muted mb-3">
            We'll notify you when your delivery is on its way.
        </p>
        <div class="d-flex gap-1 flex-wrap" style="justify-content:center;">
            <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-primary">View Order</a>
            <a href="{{ route('customer.dashboard') }}" class="btn btn-outline">Back to Dashboard</a>
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
