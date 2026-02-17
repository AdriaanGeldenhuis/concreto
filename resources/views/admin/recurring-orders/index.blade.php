@extends('layouts.admin')
@section('title', 'Recurring Orders')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Recurring Orders</h1>
        <a href="{{ route('admin.recurring-orders.export', request()->query()) }}" class="btn btn-primary">Export CSV</a>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Active</h6>
                    <h3 class="mb-0 text-success">{{ $totalActive }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Paused</h6>
                    <h3 class="mb-0 text-warning">{{ $totalPaused }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Due This Week</h6>
                    <h3 class="mb-0 text-info">{{ $dueThisWeek }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Frequency Breakdown</h6>
                    <div class="text-small">
                        @foreach($frequencies as $freq => $count)
                            <span class="badge bg-secondary me-1">{{ ucfirst($freq) }}: {{ $count }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search Customer</label>
                    <input type="text" name="search" class="form-control" placeholder="Name or email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Frequency</label>
                    <select name="frequency" class="form-select">
                        <option value="">All</option>
                        <option value="weekly" {{ request('frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="fortnightly" {{ request('frequency') === 'fortnightly' ? 'selected' : '' }}>Fortnightly</option>
                        <option value="monthly" {{ request('frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.recurring-orders.index') }}" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Template</th>
                            <th>Frequency</th>
                            <th>Next Run</th>
                            <th>Last Run</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recurringOrders as $ro)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.customers.show', $ro->customer) }}">
                                        {{ $ro->customer?->user?->name ?? 'N/A' }}
                                    </a>
                                    <br><small class="text-muted">{{ $ro->customer?->company?->name ?? '' }}</small>
                                </td>
                                <td>{{ $ro->template?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($ro->frequency) }}</span>
                                </td>
                                <td>
                                    @if($ro->next_run_date)
                                        <span class="{{ $ro->next_run_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                            {{ $ro->next_run_date->format('d M Y') }}
                                        </span>
                                        @if($ro->next_run_date->isPast() && $ro->is_active)
                                            <br><small class="text-danger">Overdue!</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $ro->last_run_date?->format('d M Y') ?? 'Never' }}</td>
                                <td>{{ is_array($ro->items) ? count($ro->items) : 0 }} item(s)</td>
                                <td>
                                    @if($ro->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-warning">Paused</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.recurring-orders.show', $ro) }}" class="btn btn-outline-primary">View</a>
                                        @if($ro->is_active)
                                            <form method="POST" action="{{ route('admin.recurring-orders.pause', $ro) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Pause this recurring order?')">Pause</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.recurring-orders.resume', $ro) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success">Resume</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No recurring orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($recurringOrders->hasPages())
        <div class="mt-3">{!! $recurringOrders->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
</div>
@endsection
