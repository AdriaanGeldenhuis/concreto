@extends('layouts.admin')
@section('title', 'Order ' . $order->order_number)
@section('content')
    <div class="page-header">
        <div class="breadcrumb"><a href="{{ route('admin.orders.index') }}">Orders</a> / {{ $order->order_number }}</div>
        <h1>{{ $order->order_number }}</h1>
        <span class="badge badge-{{ match($order->status) {
            'DELIVERED' => 'success',
            'CANCELLED', 'REFUNDED' => 'danger',
            'IN_TRANSIT', 'LOADED' => 'warning',
            'PAID', 'PLACED', 'ASSIGNED', 'ACCEPTED' => 'info',
            default => 'primary'
        } }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
    </div>

    <div class="form-row">
        {{-- Left Column --}}
        <div>
            {{-- Status Update --}}
            <div class="card">
                <div class="card-header">
                    <span>Update Status</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="form-inline">
                        @csrf
                        <div class="form-group mb-0" style="flex:1;">
                            <select name="status" class="form-control">
                                @foreach(\App\Models\Order::STATUSES as $s)
                                    <option value="{{ $s }}" {{ $order->status == $s ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </form>
                </div>
            </div>

            {{-- Tracking Link --}}
            @if(in_array($order->status, ['ASSIGNED', 'ACCEPTED', 'LOADED', 'IN_TRANSIT', 'ARRIVED']) && $order->driver)
            <div class="card" style="border-left: 3px solid var(--primary, #F97316);">
                <div class="card-header">
                    <span>&#9737; Delivery Tracking</span>
                    <span class="badge badge-{{ match($order->status) {
                        'IN_TRANSIT' => 'warning',
                        'ARRIVED' => 'success',
                        'LOADED' => 'info',
                        default => 'primary'
                    } }}">{{ str_replace('_', ' ', $order->status) }}</span>
                </div>
                <div class="card-body">
                    @php $lastLoc = $order->driverLocations()->orderBy('recorded_at', 'desc')->first(); @endphp
                    @if($lastLoc)
                        <div class="info-row">
                            <span class="label">Last GPS</span>
                            <span class="value">{{ $lastLoc->recorded_at->diffForHumans() }}</span>
                        </div>
                        @if($lastLoc->speed > 0)
                        <div class="info-row">
                            <span class="label">Speed</span>
                            <span class="value">{{ number_format($lastLoc->speed, 0) }} km/h</span>
                        </div>
                        @endif
                    @else
                        <p class="text-muted" style="font-size:0.875rem;">Waiting for GPS...</p>
                    @endif
                    <div class="mt-1">
                        <a href="{{ route('admin.tracking.order', $order) }}" class="btn btn-sm btn-primary">View Full Tracking</a>
                        <a href="{{ route('admin.tracking.driver-detail', $order->driver) }}" class="btn btn-sm btn-outline">Track Driver</a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Driver Assignment --}}
            <div class="card">
                <div class="card-header">
                    <span>Assign Driver</span>
                    @if($order->driver)
                        <span class="badge badge-info">{{ $order->driver->name }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.assign-driver', $order) }}" class="form-inline">
                        @csrf
                        <div class="form-group mb-0" style="flex:1;">
                            <select name="driver_id" class="form-control">
                                <option value="">Select driver...</option>
                                @foreach($drivers as $d)
                                    <option value="{{ $d->id }}" {{ $order->driver_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Assign</button>
                    </form>
                </div>
            </div>

            {{-- Customer Info --}}
            <div class="card">
                <div class="card-header">
                    <span>Customer</span>
                    <span class="badge badge-{{ $order->customer->type == 'COD' ? 'warning' : 'info' }}">{{ $order->customer->type }}</span>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="label">Name</span>
                            <span class="value">{{ $order->customer->user->name ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Email</span>
                            <span class="value">{{ $order->customer->user->email ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Phone</span>
                            <span class="value">{{ $order->customer->user->phone ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delivery Address --}}
            @if($order->deliveryAddress)
            <div class="card">
                <div class="card-header">Delivery Address</div>
                <div class="card-body">
                    <p class="mb-0">{{ $order->deliveryAddress->full_address }}</p>
                </div>
            </div>
            @endif

            {{-- Proof of Delivery --}}
            @if($order->proof_of_delivery)
            <div class="card">
                <div class="card-header">
                    <span>Proof of Delivery</span>
                    <span class="badge badge-success">Signed</span>
                </div>
                <div class="card-body">
                    <div class="pod-display">
                        <img src="{{ $order->proof_of_delivery }}" alt="Proof of delivery signature" class="signature-img">
                    </div>
                    @if($order->delivered_at)
                        <p class="text-small text-muted mt-1">Delivered {{ $order->delivered_at->format('d M Y \a\t H:i') }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column --}}
        <div>
            {{-- Order Items --}}
            <div class="card">
                <div class="card-header">Order Items</div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Price</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td class="font-semibold">{{ $item->product->name }}</td>
                                <td class="text-right">{{ $item->qty }}</td>
                                <td class="text-right">R{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-right font-semibold">R{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-body">
                    <div class="totals-block">
                        <div class="totals-row">
                            <span>Subtotal</span>
                            <span>R{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="totals-row">
                            <span>Delivery Fee</span>
                            <span>R{{ number_format($order->delivery_fee, 2) }}</span>
                        </div>
                        <div class="totals-row">
                            <span>VAT</span>
                            <span>R{{ number_format($order->vat, 2) }}</span>
                        </div>
                        <div class="totals-row total">
                            <span>Total</span>
                            <span>R{{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payments --}}
            @if($order->payments->count())
            <div class="card">
                <div class="card-header">Payments</div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th class="text-right">Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($order->payments as $payment)
                            <tr>
                                <td class="font-semibold">{{ ucfirst($payment->provider) }}</td>
                                <td class="text-right">R{{ number_format($payment->amount, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $payment->status == 'completed' ? 'success' : ($payment->status == 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $payment->created_at->format('d M H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="card">
                <div class="card-header">Actions</div>
                <div class="card-body">
                    <div class="d-flex gap-1 flex-wrap">
                        <form method="POST" action="{{ route('admin.orders.resend-invoice', $order) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                {{ $order->invoice ? 'Resend' : 'Generate' }} Invoice
                            </button>
                        </form>
                        @if(!in_array($order->status, ['CANCELLED', 'REFUNDED', 'DELIVERED']))
                        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this order?')">
                                Cancel Order
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
