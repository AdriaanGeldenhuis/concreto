@extends('layouts.portal')
@section('title', 'Pay Order')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))

@section('content')
    <div class="container" style="max-width:500px;padding-top:2rem;">
        <div class="page-header text-center">
            <h1>Pay for Order {{ $order->order_number }}</h1>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <p style="font-size:2rem;font-weight:800;color:var(--primary);">R{{ number_format($order->total, 2) }}</p>
                <p class="text-muted mb-3">Amount due</p>
                <form method="POST" action="{{ route('customer.orders.pay.create', $order) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Pay with Yoco</button>
                </form>
                <p class="text-small text-muted mt-2">You will be redirected to Yoco's secure payment page.</p>
            </div>
        </div>
    </div>
@endsection
