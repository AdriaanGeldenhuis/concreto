@extends('layouts.admin')
@section('title', 'Payment Register')

@section('content')
<div class="page-header">
    <h1>Payment Register</h1>
    <a href="{{ route('admin.payment-register.export', request()->all()) }}" class="btn btn-primary btn-sm">Export CSV</a>
</div>

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Income</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">R{{ number_format($totalIncome, 2) }}</h3>
        <small class="text-muted">{{ number_format($paymentCount) }} transactions</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Refunds</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--danger, #e74c3c);">R{{ number_format($totalRefunds, 2) }}</h3>
        <small class="text-muted">Money returned</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Net Income</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($netIncome, 2) }}</h3>
        <small class="text-muted">Income - Refunds</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">By Method</h6>
        <div style="margin-top:0.35rem;">
            @forelse($providerBreakdown as $pb)
                <div style="display:flex; justify-content:space-between; margin-bottom:0.15rem;">
                    <small><span class="badge badge-primary">{{ ucfirst($pb->provider) }}</span></small>
                    <small class="font-semibold">R{{ number_format($pb->total, 2) }} ({{ $pb->count }})</small>
                </div>
            @empty
                <small class="text-muted">No data</small>
            @endforelse
        </div>
    </div></div>
</div>

{{-- Filters --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" action="{{ route('admin.payment-register.index') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Ref, order, customer..." value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Provider</label>
                <select name="provider" class="form-control">
                    <option value="">All Methods</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider }}" {{ request('provider') === $provider ? 'selected' : '' }}>{{ ucfirst($provider) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Type</label>
                <select name="type" class="form-control">
                    <option value="">All</option>
                    <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Income</option>
                    <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Refunds</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="form-group">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end; gap:0.35rem;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.payment-register.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Payment Table --}}
<div class="card">
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Date</th>
            <th>Reference</th>
            <th>Provider</th>
            <th>Order</th>
            <th>Invoice</th>
            <th>Customer</th>
            <th class="text-right">Amount</th>
            <th>Status</th>
            <th>Reconciled</th>
            <th>Notes</th>
        </tr></thead>
        <tbody>
            @forelse($payments as $payment)
                <tr style="{{ $payment->amount < 0 ? 'background:rgba(231,76,60,0.05);' : '' }}">
                    <td style="white-space:nowrap;">{{ $payment->created_at->format('d M Y H:i') }}</td>
                    <td><small>{{ $payment->reference ?? '-' }}</small></td>
                    <td>
                        @php
                            $providerColors = ['yoco' => 'primary', 'eft' => 'info', 'cash' => 'success', 'card_manual' => 'warning', 'refund' => 'danger', 'other' => 'secondary'];
                        @endphp
                        <span class="badge badge-{{ $providerColors[$payment->provider] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $payment->provider)) }}
                        </span>
                    </td>
                    <td>
                        @if($payment->order)
                            <a href="{{ route('admin.orders.show', $payment->order) }}">{{ $payment->order->order_number }}</a>
                        @else - @endif
                    </td>
                    <td>
                        @if($payment->order?->invoice)
                            {{ $payment->order->invoice->invoice_no }}
                        @else - @endif
                    </td>
                    <td>
                        @if($payment->order?->customer?->user)
                            <a href="{{ route('admin.customers.show', $payment->order->customer) }}">{{ $payment->order->customer->user->name }}</a>
                        @else - @endif
                    </td>
                    <td class="text-right font-semibold" style="color:{{ $payment->amount < 0 ? 'var(--danger, #e74c3c)' : 'var(--success, #27ae60)' }};">
                        {{ $payment->amount < 0 ? '-' : '' }}R{{ number_format(abs($payment->amount), 2) }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td>
                        @if($payment->reconciled_at)
                            <span class="badge badge-success" title="Reconciled {{ $payment->reconciled_at->format('d M Y') }}">Reconciled</span>
                        @else
                            <span class="badge badge-warning">Unreconciled</span>
                        @endif
                    </td>
                    <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($payment->notes ?? '', 40) }}</small></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <h3>No payments found</h3>
                            <p>Try adjusting your filters.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@if($payments->hasPages())
    <div style="display:flex; justify-content:center; margin-top:1rem;">
        {{ $payments->appends(request()->query())->links() }}
    </div>
@endif
@endsection
