@extends('layouts.portal')
@section('title', 'Quote Detail')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))

@section('content')
    <div class="page-header"><h1>Quote Q-{{ $quote->id }}</h1></div>
    <div class="card">
        <div class="card-header"><span>Status</span><span class="badge badge-info">{{ ucfirst($quote->status) }}</span></div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                <tbody>
                @foreach($quote->items as $item)
                    <tr><td>{{ $item->product->name }}</td><td>{{ $item->qty }}</td><td>R{{ number_format($item->unit_price, 2) }}</td><td>R{{ number_format($item->line_total, 2) }}</td></tr>
                @endforeach
                </tbody>
                <tfoot><tr><td colspan="3" class="text-right font-bold">Total</td><td class="font-bold text-primary">R{{ number_format($quote->total, 2) }}</td></tr></tfoot>
            </table>
        </div>
        @if($quote->notes)<div class="card-body"><p><strong>Notes:</strong> {{ $quote->notes }}</p></div>@endif
        @if($quote->status === 'sent')
        <div class="card-footer">
            <form method="POST" action="{{ route('customer.quotes.approve', $quote) }}">
                @csrf
                <button type="submit" class="btn btn-success">Approve & Create Order</button>
            </form>
        </div>
        @endif
    </div>
@endsection
