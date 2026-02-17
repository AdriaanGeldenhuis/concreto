@extends('layouts.admin')
@section('title', 'Accounts Receivable')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Accounts Receivable</h1>
        <a href="{{ route('admin.accounts-receivable.export') }}" class="btn btn-primary">
            Export CSV
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Outstanding</h6>
                    <h3 class="mb-0">R {{ number_format($totalOutstanding, 2) }}</h3>
                    <small class="text-muted">{{ $accountCount }} account{{ $accountCount !== 1 ? 's' : '' }} with balance</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid var(--danger, #e74a3b);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Overdue (30+ days)</h6>
                    <h3 class="mb-0 text-danger">R {{ number_format($totalOverdue, 2) }}</h3>
                    <small class="text-muted">{{ $overdueCount }} overdue account{{ $overdueCount !== 1 ? 's' : '' }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Current (0-30 days)</h6>
                    <h3 class="mb-0">R {{ number_format($totalCurrent, 2) }}</h3>
                    <small class="text-muted">Within terms</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">90+ Days Overdue</h6>
                    <h3 class="mb-0 {{ $totalOver90 > 0 ? 'text-danger' : '' }}">R {{ number_format($totalOver90, 2) }}</h3>
                    <small class="text-muted">Needs urgent attention</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Ageing Summary Bar -->
    @if($totalOutstanding > 0)
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Ageing Summary</h5></div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="p-2">
                        <div class="text-muted mb-1">Current (0-30)</div>
                        <h4>R {{ number_format($totalCurrent, 2) }}</h4>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $totalOutstanding > 0 ? ($totalCurrent / $totalOutstanding * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2">
                        <div class="text-muted mb-1">31-60 Days</div>
                        <h4>R {{ number_format($totalOver30, 2) }}</h4>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: {{ $totalOutstanding > 0 ? ($totalOver30 / $totalOutstanding * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2">
                        <div class="text-muted mb-1">61-90 Days</div>
                        <h4>R {{ number_format($totalOver60, 2) }}</h4>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: {{ $totalOutstanding > 0 ? ($totalOver60 / $totalOutstanding * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2">
                        <div class="text-muted mb-1">90+ Days</div>
                        <h4 class="{{ $totalOver90 > 0 ? 'text-danger' : '' }}">R {{ number_format($totalOver90, 2) }}</h4>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-dark" style="width: {{ $totalOutstanding > 0 ? ($totalOver90 / $totalOutstanding * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounts-receivable.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Customer, company..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="outstanding_balance" {{ request('sort', 'outstanding_balance') === 'outstanding_balance' ? 'selected' : '' }}>Balance (High to Low)</option>
                        <option value="days" {{ request('sort') === 'days' ? 'selected' : '' }}>Days Outstanding</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Customer Name</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <label class="form-check-label">
                            <input type="checkbox" name="overdue" value="1" class="form-check-input" {{ request('overdue') ? 'checked' : '' }}>
                            Overdue Only
                        </label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <label class="form-check-label">
                            <input type="checkbox" name="show_zero" value="1" class="form-check-input" {{ request('show_zero') ? 'checked' : '' }}>
                            Show Zero Balance
                        </label>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.accounts-receivable.index') }}" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- AR Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Company</th>
                            <th class="text-end">Credit Limit</th>
                            <th class="text-end">Outstanding</th>
                            <th class="text-end">Available Credit</th>
                            <th class="text-center">Unpaid</th>
                            <th class="text-center">Days</th>
                            <th class="text-end">Current</th>
                            <th class="text-end">31-60</th>
                            <th class="text-end">61-90</th>
                            <th class="text-end">90+</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arData as $item)
                            <tr class="{{ $item->is_overdue ? 'table-warning' : '' }} {{ $item->over90 > 0 ? 'table-danger' : '' }}">
                                <td>
                                    <a href="{{ route('admin.customers.show', $item->customer) }}">
                                        {{ $item->customer->user->name ?? 'N/A' }}
                                    </a>
                                    <br><small class="text-muted">{{ $item->customer->user->email ?? '' }}</small>
                                </td>
                                <td>{{ $item->customer->company?->name ?? '-' }}</td>
                                <td class="text-end">
                                    @if($item->credit_limit > 0)
                                        R {{ number_format($item->credit_limit, 2) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold {{ $item->outstanding_balance > 0 ? 'text-danger' : 'text-success' }}">
                                    R {{ number_format($item->outstanding_balance, 2) }}
                                </td>
                                <td class="text-end">
                                    @if($item->available_credit !== null)
                                        <span class="{{ $item->available_credit <= 0 ? 'text-danger fw-bold' : '' }}">
                                            R {{ number_format($item->available_credit, 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->unpaid_count }}</td>
                                <td class="text-center">
                                    @if($item->days_outstanding > 0)
                                        <span class="badge bg-{{ $item->days_outstanding > 90 ? 'dark' : ($item->days_outstanding > 60 ? 'danger' : ($item->days_outstanding > 30 ? 'warning' : 'success')) }}">
                                            {{ $item->days_outstanding }}d
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">{{ $item->current > 0 ? 'R ' . number_format($item->current, 2) : '-' }}</td>
                                <td class="text-end">{{ $item->over30 > 0 ? 'R ' . number_format($item->over30, 2) : '-' }}</td>
                                <td class="text-end">{{ $item->over60 > 0 ? 'R ' . number_format($item->over60, 2) : '-' }}</td>
                                <td class="text-end">{{ $item->over90 > 0 ? 'R ' . number_format($item->over90, 2) : '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.accounts-receivable.statement', ['customer' => $item->customer->id, 'from' => now()->subMonths(3)->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}"
                                           class="btn btn-outline-primary" title="Download Statement">
                                            PDF
                                        </a>
                                        <form method="POST" action="{{ route('admin.accounts-receivable.email-statement', $item->customer) }}" style="display:inline;">
                                            @csrf
                                            <input type="hidden" name="from" value="{{ now()->subMonths(3)->format('Y-m-d') }}">
                                            <input type="hidden" name="to" value="{{ now()->format('Y-m-d') }}">
                                            <button type="submit" class="btn btn-outline-success" title="Email Statement" onclick="return confirm('Email statement to {{ $item->customer->user->email ?? 'customer' }}?')">
                                                Email
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">No accounts receivable data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($arData->count() > 0)
                    <tfoot>
                        <tr class="fw-bold" style="background: var(--primary-subtle, #f0f4ff);">
                            <td colspan="3" class="text-end">TOTALS:</td>
                            <td class="text-end text-danger">R {{ number_format($totalOutstanding, 2) }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-end">R {{ number_format($totalCurrent, 2) }}</td>
                            <td class="text-end">R {{ number_format($totalOver30, 2) }}</td>
                            <td class="text-end">R {{ number_format($totalOver60, 2) }}</td>
                            <td class="text-end">R {{ number_format($totalOver90, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
