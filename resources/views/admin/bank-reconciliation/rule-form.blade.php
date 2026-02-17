@extends('layouts.admin')
@section('title', 'Edit Rule')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Rule: {{ $rule->name }}</h1>
        <a href="{{ route('admin.bank-reconciliation.rules.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card" style="max-width: 700px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.bank-reconciliation.rules.update', $rule) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Rule Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $rule->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank Account</label>
                        <select name="bank_account_id" class="form-select">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('bank_account_id', $rule->bank_account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Match Field *</label>
                        <select name="match_field" class="form-select" required>
                            <option value="description" {{ old('match_field', $rule->match_field) === 'description' ? 'selected' : '' }}>Description</option>
                            <option value="reference" {{ old('match_field', $rule->match_field) === 'reference' ? 'selected' : '' }}>Reference</option>
                            <option value="amount" {{ old('match_field', $rule->match_field) === 'amount' ? 'selected' : '' }}>Amount</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Match Type *</label>
                        <select name="match_type" class="form-select" required>
                            <option value="contains" {{ old('match_type', $rule->match_type) === 'contains' ? 'selected' : '' }}>Contains</option>
                            <option value="starts_with" {{ old('match_type', $rule->match_type) === 'starts_with' ? 'selected' : '' }}>Starts With</option>
                            <option value="exact" {{ old('match_type', $rule->match_type) === 'exact' ? 'selected' : '' }}>Exact Match</option>
                            <option value="regex" {{ old('match_type', $rule->match_type) === 'regex' ? 'selected' : '' }}>Regex</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Match Value *</label>
                        <input type="text" name="match_value" class="form-control @error('match_value') is-invalid @enderror"
                               value="{{ old('match_value', $rule->match_value) }}" required>
                        @error('match_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category *</label>
                        <select name="category" class="form-select" required>
                            @foreach(['bank_fee' => 'Bank Fee', 'salary' => 'Salary', 'supplier' => 'Supplier', 'yoco_settlement' => 'Yoco Settlement', 'transfer' => 'Transfer', 'other' => 'Other'] as $val => $label)
                                <option value="{{ $val }}" {{ old('category', $rule->category) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Action *</label>
                        <select name="auto_action" class="form-select" required>
                            <option value="categorise" {{ old('auto_action', $rule->auto_action) === 'categorise' ? 'selected' : '' }}>Categorise Only</option>
                            <option value="exclude" {{ old('auto_action', $rule->auto_action) === 'exclude' ? 'selected' : '' }}>Auto-Exclude</option>
                            <option value="match_customer" {{ old('auto_action', $rule->auto_action) === 'match_customer' ? 'selected' : '' }}>Match to Customer</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Active</label>
                        <select name="is_active" class="form-select">
                            <option value="1" {{ old('is_active', $rule->is_active) ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !old('is_active', $rule->is_active) ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 text-muted" style="font-size: 0.85rem;">
                    Applied {{ number_format($rule->times_applied) }} times &middot;
                    Created {{ $rule->created_at->format('d M Y') }}
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Rule</button>
                    <a href="{{ route('admin.bank-reconciliation.rules.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
