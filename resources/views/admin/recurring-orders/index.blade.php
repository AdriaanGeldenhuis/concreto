@extends('layouts.admin')
@section('title', 'Recurring Orders')

@section('content')
<div class="page-header">
    <h1>Recurring Orders</h1>
    <a href="{{ route('admin.recurring-orders.export', request()->query()) }}" class="btn btn-primary btn-sm">Export CSV</a>
</div>

{{-- Stats --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Active</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $totalActive }}</h3>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Paused</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--warning, #e67e22);">{{ $totalPaused }}</h3>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Due This Week</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--info, #3498db);">{{ $dueThisWeek }}</h3>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Frequency Breakdown</h6>
        <div style="margin-top:0.35rem;">
            @foreach($frequencies as $freq => $count)
                <span class="badge badge-secondary">{{ ucfirst($freq) }}: {{ $count }}</span>
            @endforeach
        </div>
    </div></div>
</div>

{{-- Filters --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" class="filters">
            <div class="form-group" style="flex:2;">
                <label class="form-label">Search Customer</label>
                <input type="text" name="search" class="form-control" placeholder="Name or email..." value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Frequency</label>
                <select name="frequency" class="form-control">
                    <option value="">All</option>
                    <option value="weekly" {{ request('frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="biweekly" {{ request('frequency') === 'biweekly' ? 'selected' : '' }}>Biweekly</option>
                    <option value="monthly" {{ request('frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end; gap:0.35rem;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.recurring-orders.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Customer</th>
            <th>Template</th>
            <th>Frequency</th>
            <th>Next Run</th>
            <th>Last Run</th>
            <th class="text-right">Items</th>
            <th>Status</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
            @forelse($recurringOrders as $ro)
                <tr>
                    <td>
                        <a href="{{ route('admin.customers.show', $ro->customer) }}">{{ $ro->customer?->user?->name ?? 'N/A' }}</a>
                        <br><small class="text-muted">{{ $ro->customer?->company?->name ?? '' }}</small>
                    </td>
                    <td>{{ $ro->template?->name ?? '-' }}</td>
                    <td><span class="badge badge-info">{{ ucfirst($ro->frequency) }}</span></td>
                    <td>
                        @if($ro->next_run_date)
                            <span style="{{ $ro->next_run_date->isPast() ? 'color:var(--danger, #e74c3c); font-weight:600;' : '' }}">
                                {{ $ro->next_run_date->format('d M Y') }}
                            </span>
                            @if($ro->next_run_date->isPast() && $ro->is_active)
                                <br><small style="color:var(--danger, #e74c3c);">Overdue!</small>
                            @endif
                        @else - @endif
                    </td>
                    <td>{{ $ro->last_run_date?->format('d M Y') ?? 'Never' }}</td>
                    <td class="text-right">{{ is_array($ro->items) ? count($ro->items) : 0 }}</td>
                    <td>
                        <span class="badge badge-{{ $ro->is_active ? 'success' : 'warning' }}">{{ $ro->is_active ? 'Active' : 'Paused' }}</span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('admin.recurring-orders.show', $ro) }}" class="btn btn-outline btn-sm">View</a>
                        @if($ro->is_active)
                            <form method="POST" action="{{ route('admin.recurring-orders.pause', $ro) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm" style="background:var(--warning, #e67e22); color:#fff;" onclick="return confirm('Pause this recurring order?')">Pause</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.recurring-orders.resume', $ro) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm" style="background:var(--success, #27ae60); color:#fff;">Resume</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <h3>No recurring orders found</h3>
                            <p>Recurring orders are created by customers from their portal.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@if($recurringOrders->hasPages())
    <div style="display:flex; justify-content:center; margin-top:1rem;">
        {{ $recurringOrders->appends(request()->query())->links() }}
    </div>
@endif
@endsection
