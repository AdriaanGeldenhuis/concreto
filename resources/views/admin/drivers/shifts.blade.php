@extends('layouts.admin')
@section('title', $driver->name . ' - Shifts & Salary')

@section('content')
<div class="page-header">
    <div class="breadcrumb"><a href="{{ route('admin.drivers.index') }}">Drivers</a> / {{ $driver->name }}</div>
    <h1>{{ $driver->name }} – Shifts & Salary</h1>
    <div style="display:flex; gap:0.5rem; align-items:center;">
        <span class="badge badge-{{ $driver->is_active ? 'success' : 'danger' }}">{{ $driver->is_active ? 'Active' : 'Inactive' }}</span>
        @if($driver->phone)<a href="tel:{{ $driver->phone }}" class="btn btn-sm btn-outline">Call {{ $driver->phone }}</a>@endif
        <a href="{{ route('admin.tracking.driver-detail', $driver) }}" class="btn btn-sm btn-outline">Track</a>
    </div>
</div>

{{-- Salary Configuration --}}
<div class="card mb-2">
    <div class="card-header">Salary Configuration</div>
    <div class="card-body">
        <form action="{{ route('admin.drivers.salary', $driver->id) }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pay Type</label>
                    <select class="form-control" name="pay_type" required>
                        <option value="hourly" {{ $config && $config->pay_type === 'hourly' ? 'selected' : '' }}>Hourly</option>
                        <option value="per_delivery" {{ $config && $config->pay_type === 'per_delivery' ? 'selected' : '' }}>Per Delivery</option>
                        <option value="fixed_monthly" {{ $config && $config->pay_type === 'fixed_monthly' ? 'selected' : '' }}>Fixed Monthly</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Rate (R)</label>
                    <input type="number" step="0.01" class="form-control" name="rate" value="{{ $config->rate ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Overtime Rate (R)</label>
                    <input type="number" step="0.01" class="form-control" name="overtime_rate" value="{{ $config->overtime_rate ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Bonus / Delivery (R)</label>
                    <input type="number" step="0.01" class="form-control" name="bonus_per_delivery" value="{{ $config->bonus_per_delivery ?? '' }}">
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end;">
                    <button type="submit" class="btn btn-primary btn-sm">Update Salary</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Shift History --}}
<div class="card">
    <div class="card-header"><span>Shift History</span><span class="badge badge-secondary">{{ $shifts->total() }} shifts</span></div>
    @if($shifts->isEmpty())
        <div class="card-body">
            <div class="empty-state">
                <div class="icon">&#9200;</div>
                <h3>No shifts recorded</h3>
                <p>Shifts are logged when the driver clocks in via the driver app.</p>
            </div>
        </div>
    @else
        <div class="table-responsive"><table><thead><tr>
            <th>Clock In</th>
            <th>Clock Out</th>
            <th class="text-right">Hours</th>
            <th class="text-right">Deliveries</th>
            <th class="text-right">Pay</th>
            <th>Notes</th>
        </tr></thead><tbody>
            @foreach($shifts as $shift)
            <tr>
                <td>{{ $shift->clock_in->format('d M Y H:i') }}</td>
                <td>
                    @if($shift->clock_out)
                        {{ $shift->clock_out->format('d M Y H:i') }}
                    @else
                        <span class="badge badge-success">Active</span>
                    @endif
                </td>
                <td class="text-right">{{ $shift->hours_worked ? number_format($shift->hours_worked, 2).'h' : '-' }}</td>
                <td class="text-right"><span class="badge badge-info">{{ $shift->deliveries_count ?? 0 }}</span></td>
                <td class="text-right font-semibold">
                    @php
                        $pay = 0;
                        if ($config && $shift->clock_out) {
                            if ($config->pay_type === 'hourly') { $pay = $shift->hours_worked * $config->rate; }
                            elseif ($config->pay_type === 'per_delivery') { $pay = $shift->deliveries_count * $config->rate; }
                            if ($config->bonus_per_delivery) { $pay += $shift->deliveries_count * $config->bonus_per_delivery; }
                        }
                    @endphp
                    @if($shift->clock_out) R{{ number_format($pay, 2) }} @else - @endif
                </td>
                <td><small class="text-muted">{{ $shift->notes ?? '-' }}</small></td>
            </tr>
            @endforeach
        </tbody>
        @if($shifts->total() > 0)
        <tfoot><tr style="font-weight:700;">
            <td colspan="2">Totals (this page)</td>
            <td class="text-right">{{ number_format($shifts->sum('hours_worked'), 2) }}h</td>
            <td class="text-right">{{ $shifts->sum('deliveries_count') }} del</td>
            <td class="text-right" style="color:var(--success, #27ae60);">
                @php
                    $totalPay = 0;
                    foreach($shifts as $shift) {
                        if ($config && $shift->clock_out) {
                            if ($config->pay_type === 'hourly') { $totalPay += $shift->hours_worked * $config->rate; }
                            elseif ($config->pay_type === 'per_delivery') { $totalPay += $shift->deliveries_count * $config->rate; }
                            if ($config->bonus_per_delivery) { $totalPay += $shift->deliveries_count * $config->bonus_per_delivery; }
                        }
                    }
                @endphp
                R{{ number_format($totalPay, 2) }}
            </td>
            <td></td>
        </tr></tfoot>
        @endif
        </table></div>
        @if($shifts->hasPages())
            <div class="pagination" style="padding:0.75rem;">{!! $shifts->links('pagination::simple-default') !!}</div>
        @endif
    @endif
</div>
@endsection
