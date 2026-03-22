@extends('layouts.admin')
@section('title', 'Balance Sheet')

@section('content')
<div class="page-header">
    <h1>Balance Sheet</h1>
</div>

{{-- Date Selector --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" action="{{ route('admin.balance-sheet.index') }}" class="filters">
            <div class="form-group">
                <label class="form-label">As At</label>
                <input type="date" name="as_at" class="form-control" value="{{ $asAt }}">
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Generate</button>
            </div>
        </form>
        <small class="text-muted" style="display:block; margin-top:0.5rem;">
            Balance sheet as at {{ \Carbon\Carbon::parse($asAt)->format('d M Y') }}
        </small>
    </div>
</div>

{{-- Balance Sheet --}}
<div class="card mb-2">
    <div class="card-header">Statement of Financial Position</div>
    <div class="card-body">
        <div class="table-responsive"><table style="max-width:700px;">
            <tbody>
                {{-- ASSETS --}}
                <tr><td colspan="2" class="font-semibold" style="padding-top:0.75rem; font-size:1.1em; color:var(--success, #27ae60);">ASSETS</td></tr>

                <tr><td colspan="2" class="font-semibold" style="padding-top:0.5rem;">Cash & Bank Balances</td></tr>
                @foreach($bankAccounts as $ba)
                <tr>
                    <td style="padding-left:1.5rem;">{{ $ba->account_name }} ({{ $ba->bank_name }})</td>
                    <td class="text-right">R{{ number_format($ba->current_balance, 2) }}</td>
                </tr>
                @endforeach
                <tr class="font-semibold" style="background:rgba(255,255,255,0.02);">
                    <td style="padding-left:1.5rem;">Total Cash & Bank</td>
                    <td class="text-right">R{{ number_format($totalCash, 2) }}</td>
                </tr>

                <tr><td colspan="2" class="font-semibold" style="padding-top:0.5rem;">Accounts Receivable</td></tr>
                <tr>
                    <td style="padding-left:1.5rem;">Trade debtors (unpaid delivered orders)</td>
                    <td class="text-right">R{{ number_format($totalAR, 2) }}</td>
                </tr>

                <tr class="font-semibold" style="background:rgba(39,174,96,0.06); font-size:1.05em;">
                    <td>TOTAL ASSETS</td>
                    <td class="text-right">R{{ number_format($totalAssets, 2) }}</td>
                </tr>

                {{-- LIABILITIES --}}
                <tr><td colspan="2" class="font-semibold" style="padding-top:1.25rem; font-size:1.1em; color:var(--danger, #e74c3c);">LIABILITIES</td></tr>

                <tr><td colspan="2" class="font-semibold" style="padding-top:0.5rem;">Current Liabilities</td></tr>
                <tr>
                    <td style="padding-left:1.5rem;">Accounts payable (owed to suppliers)</td>
                    <td class="text-right">R{{ number_format($totalAP, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;">VAT liability (current period)</td>
                    <td class="text-right">R{{ number_format($vatLiability, 2) }}</td>
                </tr>

                <tr class="font-semibold" style="background:rgba(231,76,60,0.06); font-size:1.05em;">
                    <td>TOTAL LIABILITIES</td>
                    <td class="text-right">R{{ number_format($totalLiabilities, 2) }}</td>
                </tr>

                {{-- EQUITY --}}
                <tr><td colspan="2" class="font-semibold" style="padding-top:1.25rem; font-size:1.1em; color:var(--primary, #3498db);">EQUITY</td></tr>
                <tr>
                    <td style="padding-left:1.5rem;">Retained earnings (Assets - Liabilities)</td>
                    <td class="text-right">R{{ number_format($equity, 2) }}</td>
                </tr>

                <tr class="font-semibold" style="background:rgba(52,152,219,0.06); font-size:1.05em;">
                    <td>TOTAL EQUITY</td>
                    <td class="text-right">R{{ number_format($equity, 2) }}</td>
                </tr>

                {{-- Verification --}}
                <tr><td colspan="2" style="padding-top:1rem;"></td></tr>
                <tr class="font-semibold" style="background:rgba(255,255,255,0.04); font-size:1.1em;">
                    <td>LIABILITIES + EQUITY</td>
                    <td class="text-right">R{{ number_format($totalLiabilities + $equity, 2) }}</td>
                </tr>
            </tbody>
        </table></div>

        <div style="background:rgba(52,152,219,0.08); border-radius:var(--radius-sm, 6px); padding:0.75rem; margin-top:1rem; font-size:0.85rem;">
            <strong>Note:</strong> This is a simplified balance sheet based on data tracked in Concreto. It includes bank balances, trade debtors (AR), trade creditors (AP), and VAT liability. Fixed assets, loans, and other items not tracked in the system are excluded.
        </div>
    </div>
</div>

{{-- Quick Links --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
    <a href="{{ route('admin.accounts-receivable.index') }}" class="card" style="text-decoration:none;">
        <div class="card-body" style="padding:0.75rem; text-align:center;">
            <h6 class="text-muted mb-0" style="font-size:0.75rem;">View AR Detail</h6>
            <div class="font-semibold">R{{ number_format($totalAR, 2) }}</div>
        </div>
    </a>
    <a href="{{ route('admin.supplier-invoices.aging') }}" class="card" style="text-decoration:none;">
        <div class="card-body" style="padding:0.75rem; text-align:center;">
            <h6 class="text-muted mb-0" style="font-size:0.75rem;">View AP Detail</h6>
            <div class="font-semibold">R{{ number_format($totalAP, 2) }}</div>
        </div>
    </a>
    <a href="{{ route('admin.bank-accounts.index') }}" class="card" style="text-decoration:none;">
        <div class="card-body" style="padding:0.75rem; text-align:center;">
            <h6 class="text-muted mb-0" style="font-size:0.75rem;">Bank Accounts</h6>
            <div class="font-semibold">R{{ number_format($totalCash, 2) }}</div>
        </div>
    </a>
    <a href="{{ route('admin.profit-loss.index') }}" class="card" style="text-decoration:none;">
        <div class="card-body" style="padding:0.75rem; text-align:center;">
            <h6 class="text-muted mb-0" style="font-size:0.75rem;">Profit & Loss</h6>
            <div class="font-semibold">View P&L</div>
        </div>
    </a>
</div>
@endsection
