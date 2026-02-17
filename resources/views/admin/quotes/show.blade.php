@extends('layouts.admin')
@section('title', 'Quote ' . $quote->quote_number)
@section('content')
    <div class="page-header">
        <div class="breadcrumb"><a href="{{ route('admin.quotes.index') }}">Quotes</a> / {{ $quote->quote_number }}</div>
        <h1>{{ $quote->quote_number }}</h1>
        <span class="badge badge-{{ match($quote->status) {
            'approved' => 'success',
            'rejected', 'expired' => 'danger',
            'sent' => 'info',
            'converted' => 'primary',
            default => 'secondary'
        } }}">{{ ucfirst($quote->status) }}</span>
        @if($quote->isExpired() && $quote->status !== 'expired')
            <span class="badge badge-danger">Expired</span>
        @endif
    </div>

    <div class="form-row">
        {{-- Left Column --}}
        <div>
            {{-- Actions --}}
            <div class="card">
                <div class="card-header">Actions</div>
                <div class="card-body">
                    <div class="d-flex gap-1 flex-wrap">
                        @if(in_array($quote->status, ['draft', 'sent']))
                            <form method="POST" action="{{ route('admin.quotes.send', $quote) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    {{ $quote->status === 'sent' ? 'Resend' : 'Send' }} to Customer
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.quotes.pdf', $quote) }}" class="btn btn-sm btn-outline" target="_blank">
                            Download PDF
                        </a>

                        @if(in_array($quote->status, ['draft', 'sent']))
                            <a href="{{ route('admin.quotes.edit', $quote) }}" class="btn btn-sm btn-primary">
                                Edit Quote
                            </a>
                        @endif

                        @if(in_array($quote->status, ['sent', 'approved']))
                            <form method="POST" action="{{ route('admin.quotes.convert', $quote) }}" class="d-inline" onsubmit="return confirm('Convert this quote to an order?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning">Convert to Order</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quote Info --}}
            <div class="card">
                <div class="card-header">Quote Information</div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="label">Quote Number</span>
                            <span class="value font-semibold">{{ $quote->quote_number }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Status</span>
                            <span class="value">
                                <span class="badge badge-{{ match($quote->status) {
                                    'approved' => 'success', 'rejected', 'expired' => 'danger',
                                    'sent' => 'info', 'converted' => 'primary', default => 'secondary'
                                } }}">{{ ucfirst($quote->status) }}</span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="label">Created</span>
                            <span class="value">{{ $quote->created_at->format('d M Y \a\t H:i') }}</span>
                        </div>
                        @if($quote->expires_at)
                        <div class="info-row">
                            <span class="label">Expires</span>
                            <span class="value">
                                {{ $quote->expires_at->format('d M Y') }}
                                @if($quote->expires_at->isPast())
                                    <span class="badge badge-danger">Expired</span>
                                @else
                                    <span class="text-muted">({{ $quote->expires_at->diffForHumans() }})</span>
                                @endif
                            </span>
                        </div>
                        @endif
                        @if($quote->notes)
                        <div class="info-row">
                            <span class="label">Notes</span>
                            <span class="value">{{ $quote->notes }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Customer Info --}}
            <div class="card">
                <div class="card-header">
                    <span>Customer</span>
                    <span class="badge badge-{{ $quote->customer->type == 'COD' ? 'warning' : 'info' }}">{{ $quote->customer->type }}</span>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="label">Name</span>
                            <span class="value">{{ $quote->customer->user->name ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Email</span>
                            <span class="value">{{ $quote->customer->user->email ?? '-' }}</span>
                        </div>
                        @if($quote->customer->user->phone ?? null)
                        <div class="info-row">
                            <span class="label">Phone</span>
                            <span class="value">{{ $quote->customer->user->phone }}</span>
                        </div>
                        @endif
                        @if($quote->customer->company)
                        <div class="info-row">
                            <span class="label">Company</span>
                            <span class="value">{{ $quote->customer->company->display_name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div>
            {{-- Quote Items --}}
            <div class="card">
                <div class="card-header">Quote Items</div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($quote->items as $item)
                            <tr>
                                <td class="font-semibold">{{ $item->product->name ?? '-' }}</td>
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
                            <span>R{{ number_format($quote->subtotal, 2) }}</span>
                        </div>
                        @if($quote->delivery_fee > 0)
                        <div class="totals-row">
                            <span>Delivery Fee</span>
                            <span>R{{ number_format($quote->delivery_fee, 2) }}</span>
                        </div>
                        @endif
                        <div class="totals-row">
                            <span>VAT (15%)</span>
                            <span>R{{ number_format($quote->vat, 2) }}</span>
                        </div>
                        <div class="totals-row total">
                            <span>Total (incl. VAT)</span>
                            <span>R{{ number_format($quote->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Audit Timeline --}}
            @if(isset($auditLogs) && $auditLogs->count())
            <div class="card">
                <div class="card-header">
                    <span>Quote Timeline</span>
                    <span class="badge badge-secondary">{{ $auditLogs->count() }} events</span>
                </div>
                <div class="card-body" style="padding:0;">
                    <div style="max-height:300px; overflow-y:auto;">
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
                                    @if(isset($log->meta['email']))
                                        Sent to: {{ $log->meta['email'] }}
                                    @endif
                                    @if(isset($log->meta['order_id']))
                                        Order ID: {{ $log->meta['order_id'] }}
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
@endsection
