@extends('layouts.admin')
@section('title', 'Expenses')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Expenses</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.expenses.export', request()->query()) }}" class="btn btn-secondary">Export CSV</a>
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">+ Add Expense</a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">This Month</h6>
                    <h3 class="mb-0 text-danger">R {{ number_format($monthTotal, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Filtered Total (excl. VAT)</h6>
                    <h3 class="mb-0">R {{ number_format($summary->total_amount, 2) }}</h3>
                    <small class="text-muted">{{ $summary->expense_count }} expenses | VAT: R {{ number_format($summary->total_vat, 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Filtered Total (incl. VAT)</h6>
                    <h3 class="mb-0">R {{ number_format($summary->total_amount + $summary->total_vat, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Breakdown -->
    @if($byCategory->count() > 0)
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">By Category</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <tbody>
                    @foreach($byCategory as $bc)
                    <tr>
                        <td style="width: 30px;"><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:{{ $bc->color }}"></span></td>
                        <td>{{ $bc->name }}</td>
                        <td class="text-center">{{ $bc->count }} items</td>
                        <td class="text-end fw-bold">R {{ number_format($bc->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.expenses.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Description, supplier, ref...">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Supplier</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">VAT</th>
                            <th class="text-center">Recurring</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $exp)
                        <tr>
                            <td>{{ $exp->expense_date->format('d M Y') }}</td>
                            <td>
                                <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:{{ $exp->category?->color ?? '#ccc' }};margin-right:4px;"></span>
                                {{ $exp->category?->name ?? '-' }}
                            </td>
                            <td>{{ Str::limit($exp->description, 40) }}</td>
                            <td>{{ $exp->supplier ?? '-' }}</td>
                            <td class="text-end fw-bold">R {{ number_format($exp->amount, 2) }}</td>
                            <td class="text-end">R {{ number_format($exp->vat_amount, 2) }}</td>
                            <td class="text-center">
                                @if($exp->is_recurring)
                                    <span class="badge bg-info">{{ ucfirst($exp->recurring_frequency) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.expenses.edit', $exp) }}" class="btn btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.expenses.destroy', $exp) }}" style="display:inline" onsubmit="return confirm('Delete this expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Del</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No expenses yet. <a href="{{ route('admin.expenses.create') }}">Record the first expense</a>.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $expenses->links() }}</div>
</div>
@endsection
