@extends('layouts.admin')
@section('title', 'Reconciliation Rules')

@section('content')
<div class="page-header">
    <div>
        <h1>Reconciliation Rules</h1>
        <small class="text-muted">Define rules to auto-categorise or auto-exclude recurring bank transactions like fees, salaries, and transfers.</small>
    </div>
    <a href="{{ route('admin.bank-reconciliation.index') }}" class="btn btn-outline btn-sm">Back to Reconciliation</a>
</div>

{{-- Add Rule Form --}}
<div class="card mb-2">
    <div class="card-header">Add New Rule</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.bank-reconciliation.rules.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Rule Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. FNB Monthly Fee" required>
                    @error('name') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Bank Account</label>
                    <select name="bank_account_id" class="form-control">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ old('bank_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Match Field *</label>
                    <select name="match_field" class="form-control" required>
                        <option value="description" {{ old('match_field') === 'description' ? 'selected' : '' }}>Description</option>
                        <option value="reference" {{ old('match_field') === 'reference' ? 'selected' : '' }}>Reference</option>
                        <option value="amount" {{ old('match_field') === 'amount' ? 'selected' : '' }}>Amount</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Match Type *</label>
                    <select name="match_type" class="form-control" required>
                        <option value="contains" {{ old('match_type') === 'contains' ? 'selected' : '' }}>Contains</option>
                        <option value="starts_with" {{ old('match_type') === 'starts_with' ? 'selected' : '' }}>Starts With</option>
                        <option value="exact" {{ old('match_type') === 'exact' ? 'selected' : '' }}>Exact Match</option>
                        <option value="regex" {{ old('match_type') === 'regex' ? 'selected' : '' }}>Regex</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Match Value *</label>
                    <input type="text" name="match_value" class="form-control" value="{{ old('match_value') }}" placeholder="e.g. MONTHLY SERVICE FEE" required>
                    @error('match_value') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-control" required>
                        @foreach(['bank_fee' => 'Bank Fee', 'salary' => 'Salary', 'supplier' => 'Supplier', 'yoco_settlement' => 'Yoco Settlement', 'transfer' => 'Transfer', 'petty_cash' => 'Petty Cash', 'tax' => 'Tax / SARS', 'insurance' => 'Insurance', 'other' => 'Other'] as $val => $label)
                            <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Action *</label>
                    <select name="auto_action" class="form-control" required>
                        <option value="categorise" {{ old('auto_action') === 'categorise' ? 'selected' : '' }}>Categorise Only</option>
                        <option value="exclude" {{ old('auto_action') === 'exclude' ? 'selected' : '' }}>Auto-Exclude</option>
                        <option value="match_customer" {{ old('auto_action') === 'match_customer' ? 'selected' : '' }}>Match to Customer</option>
                    </select>
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end;">
                    <button type="submit" class="btn btn-primary btn-sm">Create Rule</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Existing Rules --}}
<div class="card">
    <div class="card-header">
        <span>Active Rules</span>
        <span class="badge badge-secondary">{{ $rules->count() }}</span>
        @if($rules->isNotEmpty())
            <small class="text-muted" style="margin-left:auto;">Rules are applied in order during auto-matching.</small>
        @endif
    </div>
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Name</th>
            <th>Account</th>
            <th>Match</th>
            <th>Value</th>
            <th>Category</th>
            <th>Action</th>
            <th class="text-right">Applied</th>
            <th class="text-right">Active</th>
            <th class="text-right">Actions</th>
        </tr></thead>
        <tbody>
            @forelse($rules as $rule)
                <tr style="{{ !$rule->is_active ? 'opacity:0.5;' : '' }}">
                    <td class="font-semibold">{{ $rule->name }}</td>
                    <td>{{ $rule->bankAccount?->account_name ?? 'All' }}</td>
                    <td>
                        <span class="badge badge-secondary">{{ ucfirst($rule->match_field) }}</span>
                        <small class="text-muted">{{ str_replace('_', ' ', $rule->match_type) }}</small>
                    </td>
                    <td><code>{{ Str::limit($rule->match_value, 30) }}</code></td>
                    <td><span class="badge badge-info">{{ ucwords(str_replace('_', ' ', $rule->category)) }}</span></td>
                    <td>
                        @php
                            $actionColors = ['categorise' => 'primary', 'exclude' => 'warning', 'match_customer' => 'success'];
                        @endphp
                        <span class="badge badge-{{ $actionColors[$rule->auto_action] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $rule->auto_action)) }}
                        </span>
                    </td>
                    <td class="text-right">
                        <span class="{{ $rule->times_applied > 0 ? 'font-semibold' : 'text-muted' }}">{{ number_format($rule->times_applied) }}x</span>
                    </td>
                    <td class="text-right">
                        <span class="badge badge-{{ $rule->is_active ? 'success' : 'secondary' }}">{{ $rule->is_active ? 'Yes' : 'No' }}</span>
                    </td>
                    <td class="text-right" style="white-space:nowrap;">
                        <a href="{{ route('admin.bank-reconciliation.rules.edit', $rule) }}" class="btn btn-outline btn-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.bank-reconciliation.rules.destroy', $rule) }}" style="display:inline;" onsubmit="return confirm('Delete the rule \'{{ $rule->name }}\'? This cannot be undone.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <h3>No rules configured yet</h3>
                            <p>Add a rule above to auto-categorise or exclude recurring bank transactions during auto-matching.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

{{-- Suggested Rules --}}
<div class="card" style="margin-top:1rem;">
    <div class="card-header">Suggested Rules</div>
    <div class="card-body" style="font-size:0.9rem;">
        <p class="text-muted" style="margin-bottom:0.75rem;">Common rules for South African banks. Use them as a starting point:</p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <ul style="margin:0; padding-left:1.25rem;">
                <li style="margin-bottom:0.5rem;"><strong>Bank fees:</strong> Description <em>contains</em> "MONTHLY SERVICE FEE" or "ADMIN FEE" &rarr; Bank Fee, Auto-Exclude</li>
                <li style="margin-bottom:0.5rem;"><strong>Salaries:</strong> Description <em>contains</em> "SALARY" &rarr; Salary, Auto-Exclude</li>
                <li style="margin-bottom:0.5rem;"><strong>Yoco settlements:</strong> Description <em>contains</em> "YOCO" &rarr; Yoco Settlement, Categorise</li>
            </ul>
            <ul style="margin:0; padding-left:1.25rem;">
                <li style="margin-bottom:0.5rem;"><strong>Own transfers:</strong> Description <em>contains</em> "TRANSFER" &rarr; Transfer, Auto-Exclude</li>
                <li style="margin-bottom:0.5rem;"><strong>Card fees:</strong> Description <em>contains</em> "CARD FEE" &rarr; Bank Fee, Auto-Exclude</li>
                <li style="margin-bottom:0.5rem;"><strong>Cash deposits:</strong> Description <em>contains</em> "CASH DEP" &rarr; Categorise only (review manually)</li>
            </ul>
        </div>
    </div>
</div>
@endsection
