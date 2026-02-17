@extends('layouts.admin')
@section('title', 'Reconciliation Statement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Bank Reconciliation Statement</h1>
        <div class="d-flex gap-2">
            @if($statement)
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
            <form method="GET" action="{{ route('admin.bank-reconciliation.statement') }}" class="row g-3">
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
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>

    @if($statement)
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <small class="text-muted d-block">Bank Balance</small>
                        <h4 class="mb-0">R {{ number_format($statement['closing_bank_balance'], 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <small class="text-muted d-block">Book Balance</small>
                        <h4 class="mb-0">R {{ number_format($statement['book_balance'], 2) }}</h4>
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Statement Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Reconciliation Statement — {{ $statement['account']->account_name }}</h5>
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
                                    <small>There are unreconciled items. Review unmatched transactions.</small>
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
                <p class="text-muted">Select a bank account and date range to generate a reconciliation statement.</p>
            </div>
        </div>
    @endif
</div>
@endsection
