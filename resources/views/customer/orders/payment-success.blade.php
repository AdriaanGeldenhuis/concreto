@extends('layouts.portal')
@section('title', 'Payment Success')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))

@section('content')
    <div class="container" style="max-width:500px;padding-top:3rem;">
        <div class="card text-center">
            <div class="card-body" style="padding:2rem;">
                <div style="font-size:3rem;color:var(--success);">&#10003;</div>
                <h2 class="mt-2">Payment Successful!</h2>
                <p class="text-muted">Your order {{ $order->order_number }} has been confirmed.</p>
                <p class="text-muted">We'll notify you when your delivery is on its way.</p>
                <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-primary mt-2">View Order</a>
            </div>
        </div>
    </div>
@endsection
