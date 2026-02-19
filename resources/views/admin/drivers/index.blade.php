@extends('layouts.admin')
@section('title', 'Driver Management')

@section('content')
<div class="page-header">
    <h1>Drivers Management</h1>
    <div style="display:flex; gap:0.5rem; align-items:center;">
        <a href="{{ route('admin.tracking.drivers') }}" class="btn btn-outline btn-sm">Track Drivers</a>
        <a href="{{ route('admin.users.index', ['role' => 'driver']) }}" class="btn btn-outline btn-sm">User Accounts</a>
    </div>
</div>

{{-- Stats --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Drivers</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $drivers->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Active</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $drivers->where('is_active', true)->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">On Shift</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $drivers->filter(fn($d) => $d->current_shift)->count() }}</h3></div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Today's Deliveries</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $drivers->sum('today_deliveries') }}</h3></div></div>
</div>

{{-- Drivers Table --}}
<div class="card">
    <div class="card-header"><span>All Drivers</span><span class="badge badge-secondary">{{ $drivers->count() }}</span></div>
    @if($drivers->isEmpty())
        <div class="card-body">
            <div class="empty-state">
                <div class="icon">&#128666;</div>
                <h3>No drivers found</h3>
                <p>Add driver users from the <a href="{{ route('admin.users.create') }}">Users</a> page.</p>
            </div>
        </div>
    @else
        <div class="table-responsive"><table><thead><tr>
            <th>Driver</th>
            <th>Status</th>
            <th>Shift</th>
            <th class="text-right">Today</th>
            <th class="text-right">Total</th>
            <th class="text-right">Month Hours</th>
            <th class="text-right">Month Deliveries</th>
            <th class="text-right">Month Pay</th>
            <th>Pay Type</th>
            <th class="text-right">Actions</th>
        </tr></thead><tbody>
            @foreach($drivers as $driver)
            <tr style="{{ !$driver->is_active ? 'opacity:0.5;' : '' }}">
                <td>
                    <div class="font-semibold">{{ $driver->name }}</div>
                    @if($driver->phone)<small class="text-muted"><a href="tel:{{ $driver->phone }}">{{ $driver->phone }}</a></small>@endif
                </td>
                <td><span class="badge badge-{{ $driver->is_active ? 'success' : 'danger' }}">{{ $driver->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>
                    @if($driver->current_shift)
                        <span class="badge badge-success">On Shift</span>
                        <br><small class="text-muted">Since {{ $driver->current_shift->clock_in->format('H:i') }}</small>
                    @else
                        <span class="badge badge-secondary">Off</span>
                    @endif
                </td>
                <td class="text-right font-semibold">{{ $driver->today_deliveries }}</td>
                <td class="text-right">{{ $driver->total_deliveries }}</td>
                <td class="text-right">{{ number_format($driver->month_hours, 1) }}h</td>
                <td class="text-right">{{ $driver->month_deliveries }}</td>
                <td class="text-right font-semibold">R{{ number_format($driver->month_pay, 2) }}</td>
                <td>
                    @if($driver->salaryConfig)
                        <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $driver->salaryConfig->pay_type)) }}</span>
                        <br><small class="text-muted">R{{ number_format($driver->salaryConfig->rate, 2) }}{{ $driver->salaryConfig->pay_type === 'hourly' ? '/hr' : ($driver->salaryConfig->pay_type === 'per_delivery' ? '/del' : '/mo') }}</small>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-right">
                    <div style="display:flex; gap:0.25rem; justify-content:flex-end;">
                        <a href="{{ route('admin.tracking.driver-detail', $driver->id) }}" class="btn btn-sm btn-outline">Track</a>
                        <a href="{{ route('admin.drivers.shifts', $driver->id) }}" class="btn btn-sm btn-primary">Shifts</a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>
@endsection
