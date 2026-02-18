@extends('layouts.admin')
@section('title', 'Accounts Receivable')

@section('content')
<div class="page-header">
    <h1>Accounts Receivable</h1>
    <a href="{{ route('admin.accounts-receivable.export') }}" class="btn btn-primary btn-sm">Export CSV</a>
</div>

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Outstanding</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($totalOutstanding, 2) }}</h3>
        <small class="text-muted">{{ $accountCount }} account{{ $accountCount !== 1 ? 's' : '' }} with balance</small>
    </div></div>
    <div class="card" style="border-left:4px solid var(--danger, #e74c3c);"><div class="card-body" style="padding:0.75rem;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Overdue (30+)</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--danger, #e74c3c);">R{{ number_format($totalOverdue, 2) }}</h3>
        <small class="text-muted">{{ $overdueCount }} overdue account{{ $overdueCount !== 1 ? 's' : '' }}</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Current (0-30 days)</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($totalCurrent, 2) }}</h3>
        <small class="text-muted">Within terms</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">90+ Days Overdue</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; {{ $totalOver90 > 0 ? 'color:var(--danger, #e74c3c);' : '' }}">R{{ number_format($totalOver90, 2) }}</h3>
        <small class="text-muted">Needs urgent attention</small>
    </div></div>
</div>

{{-- Ageing Summary --}}
@if($totalOutstanding > 0)
<div class="card mb-2">
    <div class="card-header">Ageing Summary</div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:1rem; text-align:center;">
            <div>
                <div class="text-muted" style="margin-bottom:0.25rem; font-size:0.85rem;">Current (0-30)</div>
                <h4 class="mb-0">R{{ number_format($totalCurrent, 2) }}</h4>
                <div style="height:8px; background:rgba(255,255,255,0.05); border-radius:4px; margin-top:0.5rem; overflow:hidden;">
                    <div style="height:100%; width:{{ $totalOutstanding > 0 ? ($totalCurrent / $totalOutstanding * 100) : 0 }}%; background:var(--success, #27ae60); border-radius:4px;"></div>
                </div>
            </div>
            <div>
                <div class="text-muted" style="margin-bottom:0.25rem; font-size:0.85rem;">31-60 Days</div>
                <h4 class="mb-0">R{{ number_format($totalOver30, 2) }}</h4>
                <div style="height:8px; background:rgba(255,255,255,0.05); border-radius:4px; margin-top:0.5rem; overflow:hidden;">
                    <div style="height:100%; width:{{ $totalOutstanding > 0 ? ($totalOver30 / $totalOutstanding * 100) : 0 }}%; background:var(--warning, #e67e22); border-radius:4px;"></div>
                </div>
            </div>
            <div>
                <div class="text-muted" style="margin-bottom:0.25rem; font-size:0.85rem;">61-90 Days</div>
                <h4 class="mb-0">R{{ number_format($totalOver60, 2) }}</h4>
                <div style="height:8px; background:rgba(255,255,255,0.05); border-radius:4px; margin-top:0.5rem; overflow:hidden;">
                    <div style="height:100%; width:{{ $totalOutstanding > 0 ? ($totalOver60 / $totalOutstanding * 100) : 0 }}%; background:var(--danger, #e74c3c); border-radius:4px;"></div>
                </div>
            </div>
            <div>
                <div class="text-muted" style="margin-bottom:0.25rem; font-size:0.85rem;">90+ Days</div>
                <h4 class="mb-0" style="{{ $totalOver90 > 0 ? 'color:var(--danger, #e74c3c);' : '' }}">R{{ number_format($totalOver90, 2) }}</h4>
                <div style="height:8px; background:rgba(255,255,255,0.05); border-radius:4px; margin-top:0.5rem; overflow:hidden;">
                    <div style="height:100%; width:{{ $totalOutstanding > 0 ? ($totalOver90 / $totalOutstanding * 100) : 0 }}%; background:#555; border-radius:4px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Filters --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" action="{{ route('admin.accounts-receivable.index') }}" class="filters">
            <div class="form-group" style="flex:2;">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Customer, company..." value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Sort By</label>
                <select name="sort" class="form-control">
                    <option value="outstanding_balance" {{ request('sort', 'outstanding_balance') === 'outstanding_balance' ? 'selected' : '' }}>Balance (High to Low)</option>
                    <option value="days" {{ request('sort') === 'days' ? 'selected' : '' }}>Days Outstanding</option>
                    <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Customer Name</option>
                </select>
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end; gap:0.75rem;">
                <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer; white-space:nowrap;">
                    <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }}>
                    Overdue Only
                </label>
                <label style="display:flex; align-items:center; gap:0.35rem; cursor:pointer; white-space:nowrap;">
                    <input type="checkbox" name="show_zero" value="1" {{ request('show_zero') ? 'checked' : '' }}>
                    Show Zero
                </label>
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end; gap:0.35rem;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.accounts-receivable.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- AR Table --}}
<div class="card">
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Customer</th>
            <th>Company</th>
            <th class="text-right">Credit Limit</th>
            <th class="text-right">Outstanding</th>
            <th class="text-right">Available</th>
            <th class="text-right">Unpaid</th>
            <th class="text-right">Days</th>
            <th class="text-right">Current</th>
            <th class="text-right">31-60</th>
            <th class="text-right">61-90</th>
            <th class="text-right">90+</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
            @forelse($arData as $item)
                <tr style="{{ $item->over90 > 0 ? 'background:rgba(231,76,60,0.05);' : ($item->is_overdue ? 'background:rgba(230,126,34,0.05);' : '') }}">
                    <td>
                        <a href="{{ route('admin.customers.show', $item->customer) }}">{{ $item->customer->user->name ?? 'N/A' }}</a>
                        <br><small class="text-muted">{{ $item->customer->user->email ?? '' }}</small>
                    </td>
                    <td>{{ $item->customer->company?->name ?? '-' }}</td>
                    <td class="text-right">
                        @if($item->credit_limit > 0)
                            R{{ number_format($item->credit_limit, 2) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-right font-semibold" style="color:{{ $item->outstanding_balance > 0 ? 'var(--danger, #e74c3c)' : 'var(--success, #27ae60)' }};">
                        R{{ number_format($item->outstanding_balance, 2) }}
                    </td>
                    <td class="text-right">
                        @if($item->available_credit !== null)
                            <span style="{{ $item->available_credit <= 0 ? 'color:var(--danger, #e74c3c); font-weight:600;' : '' }}">
                                R{{ number_format($item->available_credit, 2) }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-right">{{ $item->unpaid_count }}</td>
                    <td class="text-right">
                        @if($item->days_outstanding > 0)
                            <span class="badge badge-{{ $item->days_outstanding > 90 ? 'secondary' : ($item->days_outstanding > 60 ? 'danger' : ($item->days_outstanding > 30 ? 'warning' : 'success')) }}">
                                {{ $item->days_outstanding }}d
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">{{ $item->current > 0 ? 'R' . number_format($item->current, 2) : '-' }}</td>
                    <td class="text-right">{{ $item->over30 > 0 ? 'R' . number_format($item->over30, 2) : '-' }}</td>
                    <td class="text-right">{{ $item->over60 > 0 ? 'R' . number_format($item->over60, 2) : '-' }}</td>
                    <td class="text-right">{{ $item->over90 > 0 ? 'R' . number_format($item->over90, 2) : '-' }}</td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('admin.accounts-receivable.statement', ['customer' => $item->customer->id, 'from' => now()->subMonths(3)->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}" class="btn btn-outline btn-sm" title="Download Statement">PDF</a>
                        <form method="POST" action="{{ route('admin.accounts-receivable.email-statement', $item->customer) }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="from" value="{{ now()->subMonths(3)->format('Y-m-d') }}">
                            <input type="hidden" name="to" value="{{ now()->format('Y-m-d') }}">
                            <button type="submit" class="btn btn-sm" style="background:var(--success, #27ae60); color:#fff;" title="Email Statement" onclick="return confirm('Email statement to {{ $item->customer->user->email ?? 'customer' }}?')">Email</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12">
                        <div class="empty-state">
                            <h3>No accounts receivable data found</h3>
                            <p>No account customers with outstanding balances.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($arData->count() > 0)
        <tfoot>
            <tr class="font-semibold" style="background:rgba(255,255,255,0.03);">
                <td colspan="3" class="text-right">TOTALS:</td>
                <td class="text-right" style="color:var(--danger, #e74c3c);">R{{ number_format($totalOutstanding, 2) }}</td>
                <td></td><td></td><td></td>
                <td class="text-right">R{{ number_format($totalCurrent, 2) }}</td>
                <td class="text-right">R{{ number_format($totalOver30, 2) }}</td>
                <td class="text-right">R{{ number_format($totalOver60, 2) }}</td>
                <td class="text-right">R{{ number_format($totalOver90, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table></div>
</div>
@endsection
