@extends('layouts.admin')
@section('title', 'Expenses')

@section('content')
<div class="page-header">
    <h1>Expenses</h1>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.expense-categories.index') }}" class="btn btn-outline btn-sm">Categories</a>
        <a href="{{ route('admin.expenses.export', request()->query()) }}" class="btn btn-outline btn-sm">Export CSV</a>
        <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary btn-sm">+ Add Expense</a>
    </div>
</div>

{{-- Summary Stats --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">This Month</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--danger, #e74c3c);">R{{ number_format($monthTotal, 0) }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Filtered (excl VAT)</h6><h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($summary->total_amount, 0) }}</h3><small class="text-muted">{{ $summary->expense_count }} expenses</small></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">VAT</h6><h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($summary->total_vat, 0) }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total (incl VAT)</h6><h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($summary->total_amount + $summary->total_vat, 0) }}</h3></div></div>
</div>

{{-- Category Breakdown --}}
@if($byCategory->count() > 0)
<div class="card mb-2">
    <div class="card-header">By Category</div>
    <div class="table-responsive"><table><tbody>
        @foreach($byCategory as $bc)
        <tr>
            <td style="width:24px;"><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:{{ $bc->color }}"></span></td>
            <td class="font-semibold">{{ $bc->name }}</td>
            <td class="text-muted">{{ $bc->count }} items</td>
            <td class="text-right font-semibold">R {{ number_format($bc->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody></table></div>
</div>
@endif

{{-- Filters --}}
<div class="card mb-2">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control">
                    <option value="">All</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="form-group">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="form-group" style="flex:2;">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Description, supplier, ref...">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['category_id', 'from', 'to', 'search']))
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Expenses Table --}}
<div class="card">
    <div class="card-header"><span>Expenses</span></div>
    @if($expenses->isEmpty())
        <div class="card-body">
            <div class="empty-state">
                <div class="icon">&#128181;</div>
                <h3>No expenses yet</h3>
                <p><a href="{{ route('admin.expenses.create') }}">Record the first expense</a> to start tracking costs.</p>
            </div>
        </div>
    @else
        <div class="table-responsive"><table><thead><tr>
            <th>Date</th>
            <th>Category</th>
            <th>Description</th>
            <th>Supplier</th>
            <th class="text-right">Amount</th>
            <th class="text-right">VAT</th>
            <th>Recurring</th>
            <th class="text-right">Actions</th>
        </tr></thead><tbody>
            @foreach($expenses as $exp)
            <tr>
                <td>{{ $exp->expense_date->format('d M Y') }}</td>
                <td>
                    <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:{{ $exp->category?->color ?? '#ccc' }};margin-right:4px;"></span>
                    {{ $exp->category?->name ?? '-' }}
                </td>
                <td>{{ Str::limit($exp->description, 40) }}</td>
                <td><small>{{ $exp->supplier ?? '-' }}</small></td>
                <td class="text-right font-semibold">R{{ number_format($exp->amount, 2) }}</td>
                <td class="text-right">R{{ number_format($exp->vat_amount, 2) }}</td>
                <td>
                    @if($exp->is_recurring)
                        <span class="badge badge-info">{{ ucfirst($exp->recurring_frequency) }}</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-right">
                    <div style="display:flex; gap:0.25rem; justify-content:flex-end;">
                        <a href="{{ route('admin.expenses.edit', $exp) }}" class="btn btn-sm btn-outline">Edit</a>
                        <form method="POST" action="{{ route('admin.expenses.destroy', $exp) }}" style="display:inline;" onsubmit="return confirm('Delete this expense?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Del</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>

@if($expenses->hasPages())
    <div class="pagination">{!! $expenses->appends(request()->query())->links('pagination::simple-default') !!}</div>
@endif
@endsection
