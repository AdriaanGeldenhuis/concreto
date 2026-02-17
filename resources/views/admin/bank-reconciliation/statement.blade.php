@extends('layouts.admin')
@section('title', 'Reconciliation Statement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Bank Reconciliation Statement</h1>
            <small class="text-muted">Compare bank balance against system book balance to identify discrepancies.</small>
        </div>
        <div class="d-flex gap-2">
            @if($statement)
                <button onclick="window.print();" class="btn btn-secondary">Print</button>
                <a href="{{ route('admin.bank-reconciliation.statement.export', ['account' => $accountId, 'from' => $from, 'to' => $to]) }}" class="btn btn-primary">
                    Export CSV
                </a>
            @endif
            <a href="{{ route('admin.bank-reconciliation.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <!-- Period Selector -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.bank-reconciliation.statement') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Bank Account *</label>
                    <select name="account" class="form-select" required>
                        <option value="">Select account...</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>{{ $acc->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>

    @if($statement)
        {{-- Account header --}}
        <div class="card mb-4">
            <div class="card-body py-2" style="font-size: 0.9rem;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $statement['account']->account_name }}</strong>
                        &middot; {{ $statement['account']->bank_name }}
                        &middot; {{ ucfirst($statement['account']->account_type) }}
                        &middot; {{ $statement['account']->display_account_number }}
                    </div>
                    <small class="text-muted">
                        Generated {{ now()->format('d M Y H:i') }}
                    </small>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <small class="text-muted d-block">Bank Balance</small>
                        <h4 class="mb-0">R {{ number_format($statement['closing_bank_balance'], 2) }}</h4>
                        <small class="text-muted">Per statement</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <small class="text-muted d-block">Book Balance</small>
                        <h4 class="mb-0">R {{ number_format($statement['book_balance'], 2) }}</h4>
                        <small class="text-muted">Per system</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <small class="text-muted d-block">Adjusted Bank</small>
                        <h4 class="mb-0">R {{ number_format($statement['adjusted_bank_balance'], 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card" style="border-left: 4px solid {{ $statement['difference'] == 0 ? 'var(--success, #1cc88a)' : 'var(--danger, #e74a3b)' }};">
                    <div class="card-body">
                        <small class="text-muted d-block">Difference</small>
                        <h4 class="mb-0 fw-bold {{ $statement['difference'] == 0 ? 'text-success' : 'text-danger' }}">
                            R {{ number_format($statement['difference'], 2) }}
                        </h4>
                        <small class="{{ $statement['difference'] == 0 ? 'text-success' : 'text-danger' }}">
                            {{ $statement['difference'] == 0 ? 'Balanced' : 'Unreconciled' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statement Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Reconciliation Statement</h5>
                <small class="text-muted">
                    Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                </small>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0" style="max-width: 700px;">
                    <tbody>
                        <!-- Bank Statement Section -->
                        <tr style="background: #f8f9fc;">
                            <td colspan="2" class="fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.05em;">
                                Bank Statement
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2rem;">Opening bank balance</td>
                            <td class="text-end">R {{ number_format($statement['opening_bank_balance'], 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2rem;">Closing bank balance</td>
                            <td class="text-end">R {{ number_format($statement['closing_bank_balance'], 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2rem;">+ Deposits not yet cleared</td>
                            <td class="text-end text-success">R {{ number_format($statement['deposits_not_cleared'], 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2rem;">- Outstanding payments</td>
                            <td class="text-end text-danger">R {{ number_format($statement['outstanding_payments'], 2) }}</td>
                        </tr>
                        <tr class="fw-bold" style="border-top: 2px solid #dee2e6;">
                            <td>= Adjusted bank balance</td>
                            <td class="text-end">R {{ number_format($statement['adjusted_bank_balance'], 2) }}</td>
                        </tr>

                        <tr><td colspan="2">&nbsp;</td></tr>

                        <!-- Book Balance Section -->
                        <tr style="background: #f8f9fc;">
                            <td colspan="2" class="fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.05em;">
                                Book Balance (System)
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2rem;">Book balance (per system)</td>
                            <td class="text-end">R {{ number_format($statement['book_balance'], 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2rem;">+ Unrecorded deposits</td>
                            <td class="text-end text-success">R {{ number_format($statement['unrecorded_deposits'], 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2rem;">- Bank charges (unrecorded)</td>
                            <td class="text-end text-danger">R {{ number_format($statement['bank_charges'], 2) }}</td>
                        </tr>
                        <tr class="fw-bold" style="border-top: 2px solid #dee2e6;">
                            <td>= Adjusted book balance</td>
                            <td class="text-end">R {{ number_format($statement['adjusted_book_balance'], 2) }}</td>
                        </tr>

                        <tr><td colspan="2">&nbsp;</td></tr>

                        <!-- Difference -->
                        <tr class="fw-bold" style="border-top: 3px double #dee2e6; background: {{ $statement['difference'] == 0 ? '#d4edda' : '#f8d7da' }}; font-size: 1.1rem;">
                            <td>DIFFERENCE</td>
                            <td class="text-end {{ $statement['difference'] == 0 ? 'text-success' : 'text-danger' }}">
                                R {{ number_format($statement['difference'], 2) }}
                            </td>
                        </tr>
                        @if($statement['difference'] == 0)
                            <tr style="background: #d4edda;">
                                <td colspan="2" class="text-center text-success">
                                    <strong>Books are balanced.</strong>
                                </td>
                            </tr>
                        @else
                            <tr style="background: #f8d7da;">
                                <td colspan="2" class="text-center text-danger">
                                    <small>There are unreconciled items. <a href="{{ route('admin.bank-reconciliation.index', ['account' => $accountId, 'from' => $from, 'to' => $to]) }}">Review unmatched transactions</a>.</small>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <div style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;">&#128209;</div>
                <h5 class="text-muted">No statement generated</h5>
                <p class="text-muted">Select a bank account and date range above, then click Generate.</p>
            </div>
        </div>
    @endif
</div>
@endsection
