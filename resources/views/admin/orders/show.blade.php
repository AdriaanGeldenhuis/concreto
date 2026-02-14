@extends('layouts.admin')
@section('title', 'Order ' . $order->order_number)
@section('content')
    <div class="page-header">
        <div class="breadcrumb"><a href="{{ route('admin.orders.index') }}">Orders</a> / {{ $order->order_number }}</div>
        <h1>{{ $order->order_number }}</h1>
    </div>

    <div class="form-row">
        <div>
            <div class="card">
                <div class="card-header"><span>Status</span><span class="badge badge-primary">{{ str_replace('_', ' ', $order->status) }}</span></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="form-inline">
                        @csrf
                        <select name="status" class="form-control">
                            @foreach(\App\Models\Order::STATUSES as $s)
                                <option value="{{ $s }}" {{ $order->status == $s ? 'selected' : '' }}>{{ str_replace('_', ' ', $s) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Assign Driver</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.assign-driver', $order) }}" class="form-inline">
                        @csrf
                        <select name="driver_id" class="form-control">
                            <option value="">Select driver</option>
                            @foreach($drivers as $d)
                                <option value="{{ $d->id }}" {{ $order->driver_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Assign</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Customer</div>
                <div class="card-body">
                    <p><strong>{{ $order->customer->user->name ?? '-' }}</strong></p>
                    <p>{{ $order->customer->user->email ?? '' }}</p>
                    <p>{{ $order->customer->user->phone ?? '' }}</p>
                    <p>Type: <span class="badge badge-info">{{ $order->customer->type }}</span></p>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header">Items</div>
                <div class="table-responsive">
                    <table>
                        <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                        <tbody>
                        @foreach($order->items as $item)
                            <tr><td>{{ $item->product->name }}</td><td>{{ $item->qty }}</td><td>R{{ number_format($item->unit_price, 2) }}</td><td>R{{ number_format($item->line_total, 2) }}</td></tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="3" class="text-right">Subtotal</td><td>R{{ number_format($order->subtotal, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-right">Delivery</td><td>R{{ number_format($order->delivery_fee, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-right">VAT</td><td>R{{ number_format($order->vat, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-right font-bold">Total</td><td class="font-bold text-primary">R{{ number_format($order->total, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($order->deliveryAddress)
            <div class="card"><div class="card-body">
                <strong>Delivery Address:</strong> {{ $order->deliveryAddress->full_address }}
            </div></div>
            @endif

            @if($order->payments->count())
            <div class="card">
                <div class="card-header">Payments</div>
                <div class="table-responsive"><table>
                    <thead><tr><th>Provider</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    @foreach($order->payments as $payment)
                        <tr><td>{{ $payment->provider }}</td><td>R{{ number_format($payment->amount, 2) }}</td><td><span class="badge badge-{{ $payment->status == 'completed' ? 'success' : 'warning' }}">{{ $payment->status }}</span></td><td>{{ $payment->created_at->format('d M H:i') }}</td></tr>
                    @endforeach
                    </tbody>
                </table></div>
            </div>
            @endif

            <div class="d-flex gap-1 flex-wrap">
                <form method="POST" action="{{ route('admin.orders.resend-invoice', $order) }}">@csrf
                    <button type="submit" class="btn btn-primary btn-sm">{{ $order->invoice ? 'Resend' : 'Generate' }} Invoice</button>
                </form>
                @if(!in_array($order->status, ['CANCELLED', 'REFUNDED', 'DELIVERED']))
                <form method="POST" action="{{ route('admin.orders.cancel', $order) }}">@csrf
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this order?')">Cancel Order</button>
                </form>
                @endif
            </div>
        </div>
    </div>
@endsection
