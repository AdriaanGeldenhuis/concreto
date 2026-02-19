@extends('layouts.admin')
@section('title', 'Edit Rule')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.bank-reconciliation.index') }}">Reconciliation</a> / <a href="{{ route('admin.bank-reconciliation.rules.index') }}">Rules</a> / Edit</div>
        <h1>Edit Rule: {{ $rule->name }}</h1>
    </div>
    <a href="{{ route('admin.bank-reconciliation.rules.index') }}" class="btn btn-outline btn-sm">Back to Rules</a>
</div>

<div class="card" style="max-width:720px;">
    <div class="card-header">Rule Settings</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.bank-reconciliation.rules.update', $rule) }}">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Rule Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $rule->name) }}" required>
                    @error('name') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Bank Account</label>
                    <select name="bank_account_id" class="form-control">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ old('bank_account_id', $rule->bank_account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->account_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Leave blank to apply to all accounts.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Match Field *</label>
                    <select name="match_field" class="form-control" required>
                        <option value="description" {{ old('match_field', $rule->match_field) === 'description' ? 'selected' : '' }}>Description</option>
                        <option value="reference" {{ old('match_field', $rule->match_field) === 'reference' ? 'selected' : '' }}>Reference</option>
                        <option value="amount" {{ old('match_field', $rule->match_field) === 'amount' ? 'selected' : '' }}>Amount</option>
                    </select>
                    <small class="text-muted">Which transaction field to check.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Match Type *</label>
                    <select name="match_type" class="form-control" required>
                        <option value="contains" {{ old('match_type', $rule->match_type) === 'contains' ? 'selected' : '' }}>Contains</option>
                        <option value="starts_with" {{ old('match_type', $rule->match_type) === 'starts_with' ? 'selected' : '' }}>Starts With</option>
                        <option value="exact" {{ old('match_type', $rule->match_type) === 'exact' ? 'selected' : '' }}>Exact Match</option>
                        <option value="regex" {{ old('match_type', $rule->match_type) === 'regex' ? 'selected' : '' }}>Regex</option>
                    </select>
                    <small class="text-muted">How to match the value.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Match Value *</label>
                    <input type="text" name="match_value" class="form-control" value="{{ old('match_value', $rule->match_value) }}" required>
                    @error('match_value') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                    <small class="text-muted">
                        @if(old('match_type', $rule->match_type) === 'regex')
                            PHP regex pattern without delimiters.
                        @else
                            Case-insensitive text to match against.
                        @endif
                    </small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-control" required>
                        @foreach(['bank_fee' => 'Bank Fee', 'salary' => 'Salary', 'supplier' => 'Supplier', 'yoco_settlement' => 'Yoco Settlement', 'transfer' => 'Transfer', 'petty_cash' => 'Petty Cash', 'tax' => 'Tax / SARS', 'insurance' => 'Insurance', 'other' => 'Other'] as $val => $label)
                            <option value="{{ $val }}" {{ old('category', $rule->category) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Action *</label>
                    <select name="auto_action" class="form-control" required>
                        <option value="categorise" {{ old('auto_action', $rule->auto_action) === 'categorise' ? 'selected' : '' }}>Categorise Only</option>
                        <option value="exclude" {{ old('auto_action', $rule->auto_action) === 'exclude' ? 'selected' : '' }}>Auto-Exclude</option>
                        <option value="match_customer" {{ old('auto_action', $rule->auto_action) === 'match_customer' ? 'selected' : '' }}>Match to Customer</option>
                    </select>
                    <small class="text-muted">Categorise: tags the transaction. Exclude: removes from reconciliation. Match: links to a customer's payment.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Active</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $rule->is_active) ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ !old('is_active', $rule->is_active) ? 'selected' : '' }}>No</option>
                    </select>
                    <small class="text-muted">Disabled rules are skipped during auto-matching.</small>
                </div>
            </div>

            <div style="background:rgba(255,255,255,0.03); border-radius:var(--radius-sm, 6px); padding:0.75rem; margin-top:0.75rem; font-size:0.85rem; display:flex; justify-content:space-between;">
                <span>Applied <strong>{{ number_format($rule->times_applied) }}</strong> times</span>
                <span>Created <strong>{{ $rule->created_at->format('d M Y') }}</strong></span>
                <span>Last updated <strong>{{ $rule->updated_at->format('d M Y') }}</strong></span>
            </div>

            <div style="margin-top:1rem; display:flex; gap:0.5rem;">
                <button type="submit" class="btn btn-primary">Update Rule</button>
                <a href="{{ route('admin.bank-reconciliation.rules.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
