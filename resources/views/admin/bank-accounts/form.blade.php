@extends('layouts.admin')
@section('title', $account ? 'Edit Bank Account' : 'Add Bank Account')

@section('content')
<div class="page-header">
    <div class="breadcrumb"><a href="{{ route('admin.bank-accounts.index') }}">Bank Accounts</a> / {{ $account ? 'Edit' : 'New' }}</div>
    <h1>{{ $account ? 'Edit Bank Account' : 'Add Bank Account' }}</h1>
</div>

<div class="card" style="max-width:720px;">
    <div class="card-header">{{ $account ? $account->account_name : 'New Bank Account' }}</div>
    <div class="card-body">
        <form method="POST" action="{{ $account ? route('admin.bank-accounts.update', $account) : route('admin.bank-accounts.store') }}">
            @csrf
            @if($account) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Account Name *</label>
                    <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $account?->account_name) }}" placeholder="e.g. FNB Business Cheque" required>
                    <small class="text-muted">A friendly name to identify this account.</small>
                    @error('account_name') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Bank Name *</label>
                    <select name="bank_name" class="form-control" required>
                        <option value="">Select bank...</option>
                        @foreach(['FNB', 'ABSA', 'Nedbank', 'Standard Bank', 'Capitec', 'Investec', 'Other'] as $bank)
                            <option value="{{ $bank }}" {{ old('bank_name', $account?->bank_name) === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">CSV auto-detect for FNB, ABSA, Nedbank, Standard Bank, Capitec.</small>
                    @error('bank_name') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="account_number" class="form-control" placeholder="{{ $account ? 'Leave blank to keep current' : 'Full account number' }}" value="{{ old('account_number') }}" autocomplete="off">
                    @if($account && $account->account_number_last4)
                        <small class="text-muted">Current: ****{{ $account->account_number_last4 }} &middot; Encrypted</small>
                    @else
                        <small class="text-muted">Stored encrypted. Only last 4 digits visible.</small>
                    @endif
                    @error('account_number') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Branch Code</label>
                    <input type="text" name="branch_code" class="form-control" value="{{ old('branch_code', $account?->branch_code) }}" placeholder="e.g. 250655">
                    <small class="text-muted">FNB: 250655 &middot; ABSA: 632005 &middot; Nedbank: 198765</small>
                    @error('branch_code') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Account Type *</label>
                    <select name="account_type" class="form-control" required>
                        @foreach(['cheque' => 'Cheque / Current', 'savings' => 'Savings', 'credit' => 'Credit'] as $val => $label)
                            <option value="{{ $val }}" {{ old('account_type', $account?->account_type ?? 'cheque') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('account_type') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Currency *</label>
                    <select name="currency" class="form-control" required>
                        @foreach(['ZAR' => 'ZAR (Rand)', 'USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP'] as $code => $label)
                            <option value="{{ $code }}" {{ old('currency', $account?->currency ?? 'ZAR') === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Opening Balance *</label>
                    <input type="number" name="opening_balance" step="0.01" class="form-control" value="{{ old('opening_balance', $account?->opening_balance ?? '0.00') }}" required>
                    <small class="text-muted">{{ $account && ($account->transactions_count ?? 0) > 0 ? 'Won\'t recalculate transactions.' : 'Balance before first import.' }}</small>
                    @error('opening_balance') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="form-group">
                <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer;">
                    <input type="hidden" name="is_primary" value="0">
                    <input type="checkbox" name="is_primary" value="1" {{ old('is_primary', $account?->is_primary) ? 'checked' : '' }}>
                    <strong>Set as primary account</strong>
                </label>
                <small class="text-muted">Default for reconciliation and statement views. Only one can be primary.</small>
            </div>

            @if($account)
            <div class="form-group">
                <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                    <strong>Account is active</strong>
                </label>
                <small class="text-muted">Inactive accounts hidden from reconciliation, data preserved.</small>
            </div>
            @endif

            <button type="submit" class="btn btn-primary">{{ $account ? 'Update Account' : 'Create Account' }}</button>
        </form>
    </div>
</div>

@if($account)
<div class="card" style="max-width:720px; margin-top:1rem;">
    <div class="card-header">Account Summary</div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; font-size:0.9rem;">
            <div><span class="text-muted">Current Balance:</span> <strong>R{{ number_format($account->current_balance, 2) }}</strong></div>
            <div><span class="text-muted">Last Import:</span> <strong>{{ $account->last_import_date ?? 'Never' }}</strong></div>
            <div><span class="text-muted">Created:</span> <strong>{{ $account->created_at->format('d M Y') }}</strong></div>
            <div><span class="text-muted">CSV Mapping:</span> <strong>{{ $account->csv_column_map ? 'Saved' : 'Auto-detect' }}</strong></div>
        </div>
    </div>
</div>
@endif
@endsection
