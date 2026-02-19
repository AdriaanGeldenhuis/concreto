@extends('layouts.admin')
@section('title', 'Reconciliation Statement')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.bank-reconciliation.index') }}">Reconciliation</a> / Statement</div>
        <h1>Bank Reconciliation Statement</h1>
    </div>
    <div style="display:flex; gap:0.5rem;">
        @if($statement)
            <button onclick="window.print();" class="btn btn-outline btn-sm">Print</button>
            <a href="{{ route('admin.bank-reconciliation.statement.export', ['account' => $accountId, 'from' => $from, 'to' => $to]) }}" class="btn btn-primary btn-sm">Export CSV</a>
        @endif
        <a href="{{ route('admin.bank-reconciliation.index') }}" class="btn btn-outline btn-sm">Back</a>
    </div>
</div>

{{-- Period Selector --}}
<div class="card mb-2">
    <div class="card-header">Select Period</div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.bank-reconciliation.statement') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Bank Account *</label>
                <select name="account" class="form-control" required>
                    <option value="">Select account...</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>{{ $acc->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="form-group">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Generate</button>
            </div>
        </form>
    </div>
</div>

@if($statement)
    {{-- Account header --}}
    <div class="card mb-2">
        <div class="card-body" style="padding:0.75rem; font-size:0.9rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <strong>{{ $statement['account']->account_name }}</strong>
                &middot; {{ $statement['account']->bank_name }}
                &middot; {{ ucfirst($statement['account']->account_type) }}
                &middot; {{ $statement['account']->display_account_number }}
            </div>
            <small class="text-muted">Generated {{ now()->format('d M Y H:i') }}</small>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 1.25rem;">
        <div class="card"><div class="card-body" style="padding:0.75rem;">
            <small class="text-muted" style="display:block;">Bank Balance</small>
            <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($statement['closing_bank_balance'], 2) }}</h3>
            <small class="text-muted">Per statement</small>
        </div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem;">
            <small class="text-muted" style="display:block;">Book Balance</small>
            <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($statement['book_balance'], 2) }}</h3>
            <small class="text-muted">Per system</small>
        </div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem;">
            <small class="text-muted" style="display:block;">Adjusted Bank</small>
            <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($statement['adjusted_bank_balance'], 2) }}</h3>
        </div></div>
        <div class="card" style="border-left:4px solid {{ $statement['difference'] == 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};"><div class="card-body" style="padding:0.75rem;">
            <small class="text-muted" style="display:block;">Difference</small>
            <h3 class="mb-0 font-semibold" style="margin-top:0.25rem; color:{{ $statement['difference'] == 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};">
                R{{ number_format($statement['difference'], 2) }}
            </h3>
            <small style="color:{{ $statement['difference'] == 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};">
                {{ $statement['difference'] == 0 ? 'Balanced' : 'Unreconciled' }}
            </small>
        </div></div>
    </div>

    {{-- Statement Table --}}
    <div class="card mb-2">
        <div class="card-header">
            <span>Reconciliation Statement</span>
            <small class="text-muted">Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</small>
        </div>
        <div class="table-responsive"><table style="max-width:700px;">
            <tbody>
                {{-- Bank Statement Section --}}
                <tr style="background:rgba(255,255,255,0.03);">
                    <td colspan="2" class="font-semibold" style="font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em;">Bank Statement</td>
                </tr>
                <tr>
                    <td style="padding-left:2rem;">Opening bank balance</td>
                    <td class="text-right">R{{ number_format($statement['opening_bank_balance'], 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-left:2rem;">Closing bank balance</td>
                    <td class="text-right">R{{ number_format($statement['closing_bank_balance'], 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-left:2rem;">+ Deposits not yet cleared</td>
                    <td class="text-right" style="color:var(--success, #27ae60);">R{{ number_format($statement['deposits_not_cleared'], 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-left:2rem;">- Outstanding payments</td>
                    <td class="text-right" style="color:var(--danger, #e74c3c);">R{{ number_format($statement['outstanding_payments'], 2) }}</td>
                </tr>
                <tr class="font-semibold" style="border-top:2px solid rgba(255,255,255,0.15);">
                    <td>= Adjusted bank balance</td>
                    <td class="text-right">R{{ number_format($statement['adjusted_bank_balance'], 2) }}</td>
                </tr>

                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- Book Balance Section --}}
                <tr style="background:rgba(255,255,255,0.03);">
                    <td colspan="2" class="font-semibold" style="font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em;">Book Balance (System)</td>
                </tr>
                <tr>
                    <td style="padding-left:2rem;">Book balance (per system)</td>
                    <td class="text-right">R{{ number_format($statement['book_balance'], 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-left:2rem;">+ Unrecorded deposits</td>
                    <td class="text-right" style="color:var(--success, #27ae60);">R{{ number_format($statement['unrecorded_deposits'], 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-left:2rem;">- Bank charges (unrecorded)</td>
                    <td class="text-right" style="color:var(--danger, #e74c3c);">R{{ number_format($statement['bank_charges'], 2) }}</td>
                </tr>
                <tr class="font-semibold" style="border-top:2px solid rgba(255,255,255,0.15);">
                    <td>= Adjusted book balance</td>
                    <td class="text-right">R{{ number_format($statement['adjusted_book_balance'], 2) }}</td>
                </tr>

                <tr><td colspan="2">&nbsp;</td></tr>

                {{-- Difference --}}
                <tr class="font-semibold" style="border-top:3px double rgba(255,255,255,0.2); background:{{ $statement['difference'] == 0 ? 'rgba(39,174,96,0.1)' : 'rgba(231,76,60,0.1)' }}; font-size:1.1rem;">
                    <td>DIFFERENCE</td>
                    <td class="text-right" style="color:{{ $statement['difference'] == 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};">
                        R{{ number_format($statement['difference'], 2) }}
                    </td>
                </tr>
                @if($statement['difference'] == 0)
                    <tr style="background:rgba(39,174,96,0.1);">
                        <td colspan="2" style="text-align:center; color:var(--success, #27ae60);"><strong>Books are balanced.</strong></td>
                    </tr>
                @else
                    <tr style="background:rgba(231,76,60,0.1);">
                        <td colspan="2" style="text-align:center;">
                            <small style="color:var(--danger, #e74c3c);">There are unreconciled items. <a href="{{ route('admin.bank-reconciliation.index', ['account' => $accountId, 'from' => $from, 'to' => $to]) }}">Review unmatched transactions</a>.</small>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table></div>
    </div>
@else
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <div class="icon">&#128209;</div>
                <h3>No statement generated</h3>
                <p>Select a bank account and date range above, then click Generate.</p>
            </div>
        </div>
    </div>
@endif
@endsection
