@extends('layouts.admin')
@section('title', $account ? 'Edit Bank Account' : 'Add Bank Account')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $account ? 'Edit Bank Account' : 'Add Bank Account' }}</h1>
        <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary">Back</a>
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
                        @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror"
                               placeholder="{{ $account ? 'Leave blank to keep current' : 'Full account number (encrypted)' }}"
                               value="{{ old('account_number') }}">
                        @if($account && $account->account_number_last4)
                            <small class="text-muted">Current: ****{{ $account->account_number_last4 }}</small>
                        @endif
                        @error('account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Branch Code</label>
                        <input type="text" name="branch_code" class="form-control @error('branch_code') is-invalid @enderror"
                               value="{{ old('branch_code', $account?->branch_code) }}" placeholder="e.g. 250655">
                        @error('branch_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Account Type *</label>
                        <select name="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
                            @foreach(['cheque' => 'Cheque', 'savings' => 'Savings', 'credit' => 'Credit'] as $val => $label)
                                <option value="{{ $val }}" {{ old('account_type', $account?->account_type ?? 'cheque') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('account_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Currency *</label>
                        <input type="text" name="currency" class="form-control @error('currency') is-invalid @enderror"
                               value="{{ old('currency', $account?->currency ?? 'ZAR') }}" maxlength="3" required>
                        @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Opening Balance *</label>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input type="number" name="opening_balance" step="0.01" class="form-control @error('opening_balance') is-invalid @enderror"
                                   value="{{ old('opening_balance', $account?->opening_balance ?? '0.00') }}" required>
                        </div>
                        @error('opening_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="is_primary" value="0">
                            <input type="checkbox" name="is_primary" value="1" class="form-check-input" id="isPrimary"
                                   {{ old('is_primary', $account?->is_primary) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isPrimary">Set as primary account</label>
                        </div>
                    </div>

                    @if($account)
                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive"
                                   {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Account is active</label>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">{{ $account ? 'Update Account' : 'Create Account' }}</button>
                    <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
