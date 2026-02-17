@extends('layouts.admin')
@section('title', $account ? 'Edit Bank Account' : 'Add Bank Account')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $account ? 'Edit Bank Account' : 'Add Bank Account' }}</h1>
            <small class="text-muted">
                @if($account)
                    Update account details for {{ $account->account_name }}.
                @else
                    Configure a new bank account for statement imports and reconciliation.
                @endif
            </small>
        </div>
        <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary">Back to Accounts</a>
    </div>

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ $account ? route('admin.bank-accounts.update', $account) : route('admin.bank-accounts.store') }}">
                @csrf
                @if($account) @method('PUT') @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Account Name *</label>
                        <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror"
                               value="{{ old('account_name', $account?->account_name) }}" placeholder="e.g. FNB Business Cheque" required>
                        <small class="text-muted">A friendly name to identify this account.</small>
                        @error('account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Bank Name *</label>
                        <select name="bank_name" class="form-select @error('bank_name') is-invalid @enderror" required>
                            <option value="">Select bank...</option>
                            @foreach(['FNB', 'ABSA', 'Nedbank', 'Standard Bank', 'Capitec', 'Investec', 'Other'] as $bank)
                                <option value="{{ $bank }}" {{ old('bank_name', $account?->bank_name) === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">CSV auto-detection is supported for FNB, ABSA, Nedbank, Standard Bank, and Capitec.</small>
                        @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror"
                               placeholder="{{ $account ? 'Leave blank to keep current' : 'Full account number' }}"
                               value="{{ old('account_number') }}" autocomplete="off">
                        @if($account && $account->account_number_last4)
                            <small class="text-muted">Current: ****{{ $account->account_number_last4 }} &middot; Stored encrypted</small>
                        @else
                            <small class="text-muted">Stored encrypted. Only last 4 digits are visible.</small>
                        @endif
                        @error('account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Branch Code</label>
                        <input type="text" name="branch_code" class="form-control @error('branch_code') is-invalid @enderror"
                               value="{{ old('branch_code', $account?->branch_code) }}" placeholder="e.g. 250655">
                        <small class="text-muted">FNB: 250655 &middot; ABSA: 632005 &middot; Nedbank: 198765 &middot; Std Bank: 051001 &middot; Capitec: 470010</small>
                        @error('branch_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Account Type *</label>
                        <select name="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
                            @foreach(['cheque' => 'Cheque / Current', 'savings' => 'Savings', 'credit' => 'Credit'] as $val => $label)
                                <option value="{{ $val }}" {{ old('account_type', $account?->account_type ?? 'cheque') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('account_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Currency *</label>
                        <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                            @foreach(['ZAR' => 'ZAR (South African Rand)', 'USD' => 'USD (US Dollar)', 'EUR' => 'EUR (Euro)', 'GBP' => 'GBP (British Pound)'] as $code => $label)
                                <option value="{{ $code }}" {{ old('currency', $account?->currency ?? 'ZAR') === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Opening Balance *</label>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input type="number" name="opening_balance" step="0.01" class="form-control @error('opening_balance') is-invalid @enderror"
                                   value="{{ old('opening_balance', $account?->opening_balance ?? '0.00') }}" required>
                        </div>
                        @if($account && ($account->transactions_count ?? 0) > 0)
                            <small class="text-muted">Changing this will not recalculate transaction balances.</small>
                        @else
                            <small class="text-muted">Balance before the first imported transaction.</small>
                        @endif
                        @error('opening_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <hr class="my-2">
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="is_primary" value="0">
                            <input type="checkbox" name="is_primary" value="1" class="form-check-input" id="isPrimary"
                                   {{ old('is_primary', $account?->is_primary) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isPrimary">
                                <strong>Set as primary account</strong>
                            </label>
                            <small class="text-muted d-block">The primary account is used as the default for reconciliation and statement views. Only one account can be primary.</small>
                        </div>
                    </div>

                    @if($account)
                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive"
                                   {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">
                                <strong>Account is active</strong>
                            </label>
                            <small class="text-muted d-block">Inactive accounts are hidden from reconciliation but their data is preserved.</small>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ $account ? 'Update Account' : 'Create Account' }}</button>
                    <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @if($account)
        <div class="card mt-4" style="max-width: 700px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Account Summary</h6>
            </div>
            <div class="card-body" style="font-size: 0.9rem;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Current Balance:</span>
                            <strong>R {{ number_format($account->current_balance, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Last Import:</span>
                            <strong>{{ $account->last_import_date ?? 'Never' }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Created:</span>
                            <strong>{{ $account->created_at->format('d M Y') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">CSV Mapping:</span>
                            <strong>{{ $account->csv_column_map ? 'Saved' : 'Auto-detect' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
