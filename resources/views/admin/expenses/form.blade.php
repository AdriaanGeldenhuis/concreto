@extends('layouts.admin')
@section('title', isset($expense) ? 'Edit Expense' : 'Add Expense')

@section('content')
<div class="page-header">
    <div class="breadcrumb"><a href="{{ route('admin.expenses.index') }}">Expenses</a> / {{ isset($expense) ? 'Edit' : 'New' }}</div>
    <h1>{{ isset($expense) ? 'Edit Expense' : 'Record Expense' }}</h1>
</div>

<div class="card" style="max-width:720px;">
    <div class="card-header">{{ isset($expense) ? 'Edit Expense' : 'Record Expense' }}</div>
    <div class="card-body">
        @if($categories->isEmpty())
            <div style="padding:1rem; background:rgba(249,115,22,0.08); border:1px solid rgba(249,115,22,0.2); border-radius:var(--radius-sm, 6px); margin-bottom:1rem;">
                No expense categories found. <a href="{{ route('admin.expense-categories.index') }}">Create a category first</a>.
            </div>
        @else
        <form method="POST" action="{{ isset($expense) ? route('admin.expenses.update', $expense) : route('admin.expenses.store') }}">
            @csrf
            @if(isset($expense)) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="expense_category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('expense_category_id', $expense->expense_category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('expense_category_id') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : date('Y-m-d')) }}" required max="{{ date('Y-m-d') }}">
                    @error('expense_date') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description *</label>
                <input type="text" name="description" class="form-control" value="{{ old('description', $expense->description ?? '') }}" required placeholder="e.g. Office rent February 2026">
                @error('description') <small style="color:var(--danger);">{{ $message }}</small> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Amount (excl. VAT) *</label>
                    <input type="number" name="amount" class="form-control" value="{{ old('amount', $expense->amount ?? '') }}" required min="0.01" step="0.01">
                    @error('amount') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">VAT Amount</label>
                    <input type="number" name="vat_amount" class="form-control" value="{{ old('vat_amount', $expense->vat_amount ?? '0') }}" min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">Supplier</label>
                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $expense->supplier ?? '') }}" placeholder="e.g. Landlord Pty">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Reference / Invoice #</label>
                <input type="text" name="reference" class="form-control" value="{{ old('reference', $expense->reference ?? '') }}">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer;">
                        <input type="checkbox" name="is_recurring" value="1" id="is_recurring"
                            {{ old('is_recurring', $expense->is_recurring ?? false) ? 'checked' : '' }}
                            onchange="document.getElementById('freq-wrapper').style.display = this.checked ? 'block' : 'none'">
                        This is a recurring expense
                    </label>
                </div>
                <div class="form-group" id="freq-wrapper" style="display: {{ old('is_recurring', $expense->is_recurring ?? false) ? 'block' : 'none' }}">
                    <label class="form-label">Frequency</label>
                    <select name="recurring_frequency" class="form-control">
                        <option value="">Select...</option>
                        <option value="weekly" {{ old('recurring_frequency', $expense->recurring_frequency ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ old('recurring_frequency', $expense->recurring_frequency ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ old('recurring_frequency', $expense->recurring_frequency ?? '') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ old('recurring_frequency', $expense->recurring_frequency ?? '') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $expense->notes ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">{{ isset($expense) ? 'Update Expense' : 'Record Expense' }}</button>
        </form>
        @endif
    </div>
</div>
@endsection
