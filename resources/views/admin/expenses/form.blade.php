@extends('layouts.admin')
@section('title', isset($expense) ? 'Edit Expense' : 'Add Expense')

@section('content')
<div class="container-fluid" style="max-width: 700px;">
    <div class="mb-4">
        <a href="{{ route('admin.expenses.index') }}" class="text-muted">&larr; Back to Expenses</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ isset($expense) ? 'Edit Expense' : 'Record Expense' }}</h5>
        </div>
        <div class="card-body">
            @if($categories->isEmpty())
                <div class="alert alert-warning">
                    No expense categories found. <a href="{{ route('admin.expense-categories.index') }}">Create a category first</a>.
                </div>
            @else
            <form method="POST" action="{{ isset($expense) ? route('admin.expenses.update', $expense) : route('admin.expenses.store') }}">
                @csrf
                @if(isset($expense)) @method('PUT') @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Category *</label>
                        <select name="expense_category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('expense_category_id', $expense->expense_category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('expense_category_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date *</label>
                        <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : date('Y-m-d')) }}" required max="{{ date('Y-m-d') }}">
                        @error('expense_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description', $expense->description ?? '') }}" required placeholder="e.g. Office rent February 2026">
                    @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Amount (excl. VAT) *</label>
                        <input type="number" name="amount" class="form-control" value="{{ old('amount', $expense->amount ?? '') }}" required min="0.01" step="0.01">
                        @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">VAT Amount</label>
                        <input type="number" name="vat_amount" class="form-control" value="{{ old('vat_amount', $expense->vat_amount ?? '0') }}" min="0" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $expense->supplier ?? '') }}" placeholder="e.g. Landlord Pty">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reference / Invoice #</label>
                    <input type="text" name="reference" class="form-control" value="{{ old('reference', $expense->reference ?? '') }}">
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="is_recurring"
                                {{ old('is_recurring', $expense->is_recurring ?? false) ? 'checked' : '' }}
                                onchange="document.getElementById('freq-wrapper').style.display = this.checked ? 'block' : 'none'">
                            <label class="form-check-label" for="is_recurring">This is a recurring expense</label>
                        </div>
                    </div>
                    <div class="col-md-6" id="freq-wrapper" style="display: {{ old('is_recurring', $expense->is_recurring ?? false) ? 'block' : 'none' }}">
                        <select name="recurring_frequency" class="form-select">
                            <option value="">Frequency</option>
                            <option value="weekly" {{ old('recurring_frequency', $expense->recurring_frequency ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('recurring_frequency', $expense->recurring_frequency ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('recurring_frequency', $expense->recurring_frequency ?? '') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ old('recurring_frequency', $expense->recurring_frequency ?? '') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $expense->notes ?? '') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">{{ isset($expense) ? 'Update Expense' : 'Record Expense' }}</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
