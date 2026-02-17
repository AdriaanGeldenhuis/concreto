@extends('layouts.admin')
@section('title', 'Reconciliation Rules')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Reconciliation Rules</h1>
            <small class="text-muted">Define rules to auto-categorise or auto-exclude recurring bank transactions like fees, salaries, and transfers.</small>
        </div>
        <a href="{{ route('admin.bank-reconciliation.index') }}" class="btn btn-secondary">Back to Reconciliation</a>
    </div>

    <!-- Add Rule Form -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Add New Rule</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.bank-reconciliation.rules.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Rule Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g. FNB Monthly Fee" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bank Account</label>
                        <select name="bank_account_id" class="form-select">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('bank_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Match Field *</label>
                        <select name="match_field" class="form-select" required>
                            <option value="description" {{ old('match_field') === 'description' ? 'selected' : '' }}>Description</option>
                            <option value="reference" {{ old('match_field') === 'reference' ? 'selected' : '' }}>Reference</option>
                            <option value="amount" {{ old('match_field') === 'amount' ? 'selected' : '' }}>Amount</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Match Type *</label>
                        <select name="match_type" class="form-select" required>
                            <option value="contains" {{ old('match_type') === 'contains' ? 'selected' : '' }}>Contains</option>
                            <option value="starts_with" {{ old('match_type') === 'starts_with' ? 'selected' : '' }}>Starts With</option>
                            <option value="exact" {{ old('match_type') === 'exact' ? 'selected' : '' }}>Exact Match</option>
                            <option value="regex" {{ old('match_type') === 'regex' ? 'selected' : '' }}>Regex</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Match Value *</label>
                        <input type="text" name="match_value" class="form-control @error('match_value') is-invalid @enderror"
                               value="{{ old('match_value') }}" placeholder="e.g. MONTHLY SERVICE FEE" required>
                        @error('match_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category *</label>
                        <select name="category" class="form-select" required>
                            @foreach(['bank_fee' => 'Bank Fee', 'salary' => 'Salary', 'supplier' => 'Supplier', 'yoco_settlement' => 'Yoco Settlement', 'transfer' => 'Transfer', 'petty_cash' => 'Petty Cash', 'tax' => 'Tax / SARS', 'insurance' => 'Insurance', 'other' => 'Other'] as $val => $label)
                                <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Action *</label>
                        <select name="auto_action" class="form-select" required>
                            <option value="categorise" {{ old('auto_action') === 'categorise' ? 'selected' : '' }}>Categorise Only</option>
                            <option value="exclude" {{ old('auto_action') === 'exclude' ? 'selected' : '' }}>Auto-Exclude</option>
                            <option value="match_customer" {{ old('auto_action') === 'match_customer' ? 'selected' : '' }}>Match to Customer</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Create Rule</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Rules -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Active Rules ({{ $rules->count() }})</h5>
            @if($rules->isNotEmpty())
                <small class="text-muted">Rules are applied in order during auto-matching.</small>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 0.875rem;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Account</th>
                            <th>Match</th>
                            <th>Value</th>
                            <th>Category</th>
                            <th>Action</th>
                            <th class="text-center">Applied</th>
                            <th class="text-center">Active</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                            <tr class="{{ !$rule->is_active ? 'opacity-50' : '' }}">
                                <td class="fw-bold">{{ $rule->name }}</td>
                                <td>{{ $rule->bankAccount?->account_name ?? 'All' }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($rule->match_field) }}</span>
                                    <small class="text-muted">{{ str_replace('_', ' ', $rule->match_type) }}</small>
                                </td>
                                <td><code>{{ Str::limit($rule->match_value, 30) }}</code></td>
                                <td><span class="badge bg-info">{{ ucwords(str_replace('_', ' ', $rule->category)) }}</span></td>
                                <td>
                                    @php
                                        $actionColors = ['categorise' => 'primary', 'exclude' => 'warning', 'match_customer' => 'success'];
                                    @endphp
                                    <span class="badge bg-{{ $actionColors[$rule->auto_action] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $rule->auto_action)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="{{ $rule->times_applied > 0 ? 'fw-bold' : 'text-muted' }}">
                                        {{ number_format($rule->times_applied) }}x
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                        {{ $rule->is_active ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.bank-reconciliation.rules.edit', $rule) }}" class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.bank-reconciliation.rules.destroy', $rule) }}" class="d-inline"
                                          onsubmit="return confirm('Delete the rule \'{{ $rule->name }}\'? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No rules configured yet. Add a rule above to auto-categorise or exclude recurring bank transactions during auto-matching.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Common Rule Examples -->
    <div class="card mt-4">
        <div class="card-header"><h5 class="mb-0">Suggested Rules</h5></div>
        <div class="card-body" style="font-size: 0.9rem;">
            <p class="text-muted mb-3">Here are common rules for South African banks. Use them as a starting point:</p>
            <div class="row">
                <div class="col-md-6">
                    <ul class="mb-0">
                        <li class="mb-2"><strong>Bank fees:</strong> Description <em>contains</em> "MONTHLY SERVICE FEE" or "ADMIN FEE" &rarr; Bank Fee, Auto-Exclude</li>
                        <li class="mb-2"><strong>Salaries:</strong> Description <em>contains</em> "SALARY" &rarr; Salary, Auto-Exclude</li>
                        <li class="mb-2"><strong>Yoco settlements:</strong> Description <em>contains</em> "YOCO" &rarr; Yoco Settlement, Categorise</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="mb-0">
                        <li class="mb-2"><strong>Own transfers:</strong> Description <em>contains</em> "TRANSFER" &rarr; Transfer, Auto-Exclude</li>
                        <li class="mb-2"><strong>Card fees:</strong> Description <em>contains</em> "CARD FEE" &rarr; Bank Fee, Auto-Exclude</li>
                        <li class="mb-2"><strong>Cash deposits:</strong> Description <em>contains</em> "CASH DEP" &rarr; Categorise only (review manually)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
