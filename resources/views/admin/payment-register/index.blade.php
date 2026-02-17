@extends('layouts.admin')
@section('title', 'Payment Register')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Payment Register</h1>
        <a href="{{ route('admin.payment-register.export', request()->all()) }}" class="btn btn-primary">
            Export CSV
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Income</h6>
                    <h3 class="mb-0 text-success">R {{ number_format($totalIncome, 2) }}</h3>
                    <small class="text-muted">{{ number_format($paymentCount) }} transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Refunds</h6>
                    <h3 class="mb-0 text-danger">R {{ number_format($totalRefunds, 2) }}</h3>
                    <small class="text-muted">Money returned</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Net Income</h6>
                    <h3 class="mb-0">R {{ number_format($netIncome, 2) }}</h3>
                    <small class="text-muted">Income - Refunds</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">By Method</h6>
                    @forelse($providerBreakdown as $pb)
                        <div class="d-flex justify-content-between">
                            <small><span class="badge bg-primary">{{ ucfirst($pb->provider) }}</span></small>
                            <small class="fw-bold">R {{ number_format($pb->total, 2) }} ({{ $pb->count }})</small>
                        </div>
                    @empty
                        <small class="text-muted">No data</small>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payment-register.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Ref, order, customer..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Provider</label>
                    <select name="provider" class="form-select">
                        <option value="">All Methods</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider }}" {{ request('provider') === $provider ? 'selected' : '' }}>{{ ucfirst($provider) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Income</option>
                        <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Refunds</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.payment-register.index') }}" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Provider</th>
                            <th>Order</th>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Reconciled</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="{{ $payment->amount < 0 ? 'table-danger' : '' }}">
                                <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                                <td><small>{{ $payment->reference ?? '-' }}</small></td>
                                <td>
                                    @php
                                        $providerColors = [
                                            'yoco' => 'primary',
                                            'eft' => 'info',
                                            'cash' => 'success',
                                            'card_manual' => 'warning',
                                            'refund' => 'danger',
                                            'other' => 'secondary',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $providerColors[$payment->provider] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $payment->provider)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($payment->order)
                                        <a href="{{ route('admin.orders.show', $payment->order) }}">{{ $payment->order->order_number }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($payment->order?->invoice)
                                        {{ $payment->order->invoice->invoice_no }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($payment->order?->customer?->user)
                                        <a href="{{ route('admin.customers.show', $payment->order->customer) }}">
                                            {{ $payment->order->customer->user->name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end fw-bold {{ $payment->amount < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $payment->amount < 0 ? '-' : '' }}R {{ number_format(abs($payment->amount), 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($payment->reconciled_at)
                                        <span class="badge bg-success" title="Reconciled {{ $payment->reconciled_at->format('d M Y') }}">Reconciled</span>
                                    @else
                                        <span class="badge bg-warning">Unreconciled</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($payment->notes ?? '', 40) }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($payments->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $payments->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
