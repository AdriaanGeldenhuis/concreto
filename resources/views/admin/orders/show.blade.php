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

            {{-- Delivery Details --}}
            <div class="card">
                <div class="card-header">Delivery Details</div>
                <div class="card-body">
                    @if($order->deliveryAddress)
                    <div class="info-row">
                        <span class="label">Address</span>
                        <span class="value">{{ $order->deliveryAddress->full_address }}</span>
                    </div>
                    @endif
                    @if($order->scheduled_date)
                    <div class="info-row">
                        <span class="label">Scheduled Date</span>
                        <span class="value">{{ $order->scheduled_date->format('d M Y') }}</span>
                    </div>
                    @endif
                    @if($order->scheduled_time_window)
                    <div class="info-row">
                        <span class="label">Time Window</span>
                        <span class="value">{{ $order->scheduled_time_window }}</span>
                    </div>
                    @endif
                    @if($order->notes)
                    <div class="info-row">
                        <span class="label">Notes</span>
                        <span class="value">{{ $order->notes }}</span>
                    </div>
                    @endif
                    @if($order->promoCode)
                    <div class="info-row">
                        <span class="label">Promo Code</span>
                        <span class="value"><span class="badge badge-success">{{ $order->promoCode->code }}</span></span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Proof of Delivery --}}
            @if($order->proofOfDelivery)
            <div class="card">
                <div class="card-header">
                    <span>Proof of Delivery</span>
                    <span class="badge badge-success">Signed</span>
                </div>
                <div class="card-body">
                    <div class="pod-display">
                        @if($order->proofOfDelivery->signature_path)
                            <img src="{{ asset('storage/' . $order->proofOfDelivery->signature_path) }}" alt="Proof of delivery signature" class="signature-img">
                        @endif
                        @if($order->proofOfDelivery->photo_path)
                            <img src="{{ asset('storage/' . $order->proofOfDelivery->photo_path) }}" alt="Delivery photo" class="signature-img" style="margin-top:0.5rem;">
                        @endif
                    </div>
                    @if($order->proofOfDelivery->signed_by)
                        <p class="text-small text-muted mt-1">Signed by: {{ $order->proofOfDelivery->signed_by }}</p>
                    @endif
                    @if($order->proofOfDelivery->created_at)
                        <p class="text-small text-muted">Delivered {{ $order->proofOfDelivery->created_at->format('d M Y \a\t H:i') }}</p>
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
                        @if($order->discount_amount > 0)
                        <div class="totals-row" style="color:var(--success, #22c55e);">
                            <span>Discount</span>
                            <span>-R{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                        @endif
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

            {{-- Record Manual Payment --}}
            <div class="card">
                <div class="card-header">Record Payment (EFT/Cash)</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.record-payment', $order) }}">
                        @csrf
                        <div class="form-group">
                            <label>Amount (R)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" value="{{ $order->total }}" required>
                        </div>
                        <div class="form-group">
                            <label>Method</label>
                            <select name="provider" class="form-control" required>
                                <option value="eft">EFT / Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="card_manual">Card (Manual)</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reference</label>
                            <input type="text" name="reference" class="form-control" placeholder="EFT reference / receipt #">
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">Record Payment</button>
                    </form>
                </div>
            </div>

            {{-- Refund --}}
            @if(in_array($order->status, ['DELIVERED', 'PLACED', 'ASSIGNED', 'ACCEPTED', 'LOADED', 'CANCELLED']))
            @php
                $totalPaid = $order->payments->where('status', 'completed')->where('amount', '>', 0)->sum('amount');
                $totalRefunded = abs($order->payments->where('status', 'completed')->filter(fn($p) => $p->amount < 0)->sum('amount'));
                $maxRefundable = max(0, $totalPaid - $totalRefunded);
            @endphp
            <div class="card">
                <div class="card-header">
                    <span>Process Refund</span>
                    @if($totalPaid > 0)
                        <span class="badge badge-info">Paid: R{{ number_format($totalPaid, 2) }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($maxRefundable <= 0)
                        <p class="text-muted">No refundable amount available (total paid: R{{ number_format($totalPaid, 2) }}, already refunded: R{{ number_format($totalRefunded, 2) }}).</p>
                    @else
                    <form method="POST" action="{{ route('admin.orders.refund', $order) }}">
                        @csrf
                        <div class="form-group">
                            <label>Refund Amount (R) <span class="text-muted" style="font-size:0.8rem;">(max: R{{ number_format($maxRefundable, 2) }})</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control" value="{{ $maxRefundable }}" max="{{ $maxRefundable }}" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control" rows="2" required placeholder="Reason for refund..."></textarea>
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" name="mark_refunded" value="1"> Mark order as Refunded</label>
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Process this refund?')">Process Refund</button>
                    </form>
                    @endif
                </div>
            </div>
            @endif

            {{-- Force Status Override --}}
            <div class="card">
                <div class="card-header">
                    <span>Force Status Override</span>
                    <span class="badge badge-warning">Admin Only</span>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:0.8rem; margin-bottom:0.75rem;">Bypass normal status transitions. A reason is required and will be recorded in the audit trail.</p>
                    <form method="POST" action="{{ route('admin.orders.force-status', $order) }}">
                        @csrf
                        <div class="form-group">
                            <label>New Status</label>
                            <select name="status" class="form-control">
                                @foreach(\App\Models\Order::STATUSES as $s)
                                    @if($s !== $order->status)
                                    <option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reason (required)</label>
                            <textarea name="reason" class="form-control" rows="2" required placeholder="Why is this override needed?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Force override status? This bypasses normal transition rules.')">Force Update</button>
                    </form>
                </div>
            </div>

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
                        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" id="cancel-form">
                            @csrf
                            <input type="hidden" name="reason" id="cancel-reason-value">
                            <button type="button" class="btn btn-danger btn-sm" onclick="promptCancel()">
                                Cancel Order
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Order Timeline / Audit History --}}
            @if(isset($auditLogs) && $auditLogs->count())
            <div class="card">
                <div class="card-header">
                    <span>Order Timeline</span>
                    <span class="badge badge-secondary">{{ $auditLogs->count() }} events</span>
                </div>
                <div class="card-body" style="padding:0;">
                    <div style="max-height:400px; overflow-y:auto;">
                        @foreach($auditLogs as $log)
                        <div style="padding:0.75rem 1rem; border-bottom:1px solid var(--border, #e5e7eb); font-size:0.85rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span class="font-semibold">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                <span class="text-muted" style="font-size:0.75rem;">{{ $log->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div class="text-muted" style="font-size:0.8rem;">
                                @if($log->actor)
                                    By {{ $log->actor->name }} ({{ $log->actor_role }})
                                @else
                                    System
                                @endif
                            </div>
                            @if($log->meta)
                                <div style="font-size:0.75rem; margin-top:0.25rem; color:var(--text-secondary, #6b7280);">
                                    @if(isset($log->meta['from']) && isset($log->meta['to']))
                                        {{ str_replace('_', ' ', $log->meta['from']) }} &rarr; {{ str_replace('_', ' ', $log->meta['to']) }}
                                    @endif
                                    @if(isset($log->meta['reason']))
                                        <br>Reason: {{ $log->meta['reason'] }}
                                    @endif
                                    @if(isset($log->meta['driver_id']))
                                        <br>Driver ID: {{ $log->meta['driver_id'] }}
                                    @endif
                                    @if(isset($log->meta['amount']))
                                        <br>Amount: R{{ number_format($log->meta['amount'], 2) }}
                                    @endif
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

@push('scripts')
<script>
function promptCancel() {
    var reason = prompt('Reason for cancellation (optional):');
    if (reason === null) return; // user clicked Cancel on prompt
    document.getElementById('cancel-reason-value').value = reason;
    if (confirm('Are you sure you want to cancel this order?')) {
        document.getElementById('cancel-form').submit();
    }
}
</script>
@endpush
@endsection
