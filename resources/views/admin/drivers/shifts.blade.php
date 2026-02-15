@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="text-light">{{ $driver->name }} - Shifts & Salary</h3>
                <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">Back to Drivers</a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark text-light">
                <div class="card-header">
                    <h5 class="mb-0">Salary Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.drivers.salary', $driver->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="pay_type" class="form-label">Pay Type</label>
                                <select class="form-control bg-dark text-light border-secondary" id="pay_type" name="pay_type" required>
                                    <option value="hourly" {{ $config && $config->pay_type === 'hourly' ? 'selected' : '' }}>Hourly</option>
                                    <option value="per_delivery" {{ $config && $config->pay_type === 'per_delivery' ? 'selected' : '' }}>Per Delivery</option>
                                    <option value="fixed_monthly" {{ $config && $config->pay_type === 'fixed_monthly' ? 'selected' : '' }}>Fixed Monthly</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="rate" class="form-label">Rate (R)</label>
                                <input type="number" step="0.01" class="form-control bg-dark text-light border-secondary" id="rate" name="rate" value="{{ $config->rate ?? '' }}" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="overtime_rate" class="form-label">Overtime Rate (R)</label>
                                <input type="number" step="0.01" class="form-control bg-dark text-light border-secondary" id="overtime_rate" name="overtime_rate" value="{{ $config->overtime_rate ?? '' }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="bonus_per_delivery" class="form-label">Bonus Per Delivery (R)</label>
                                <input type="number" step="0.01" class="form-control bg-dark text-light border-secondary" id="bonus_per_delivery" name="bonus_per_delivery" value="{{ $config->bonus_per_delivery ?? '' }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Salary Configuration</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card bg-dark text-light">
                <div class="card-header">
                    <h5 class="mb-0">Shift History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Hours Worked</th>
                                    <th>Deliveries</th>
                                    <th>Pay</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts as $shift)
                                <tr>
                                    <td>{{ $shift->clock_in->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if($shift->clock_out)
                                            {{ $shift->clock_out->format('Y-m-d H:i') }}
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($shift->hours_worked)
                                            {{ number_format($shift->hours_worked, 2) }} hours
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $shift->deliveries_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $pay = 0;
                                            if ($config && $shift->clock_out) {
                                                if ($config->pay_type === 'hourly') {
                                                    $pay = $shift->hours_worked * $config->rate;
                                                } elseif ($config->pay_type === 'per_delivery') {
                                                    $pay = $shift->deliveries_count * $config->rate;
                                                }
                                                if ($config->bonus_per_delivery) {
                                                    $pay += $shift->deliveries_count * $config->bonus_per_delivery;
                                                }
                                            }
                                        @endphp
                                        @if($shift->clock_out)
                                            <strong>R {{ number_format($pay, 2) }}</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $shift->notes ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No shifts found for this driver.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($shifts->total() > 0)
                            <tfoot>
                                <tr class="table-active">
                                    <td colspan="2"><strong>Totals:</strong></td>
                                    <td>
                                        <strong>{{ number_format($shifts->sum('hours_worked'), 2) }} hours</strong>
                                    </td>
                                    <td>
                                        <strong>{{ $shifts->sum('deliveries_count') }} deliveries</strong>
                                    </td>
                                    <td>
                                        @php
                                            $totalPay = 0;
                                            foreach($shifts as $shift) {
                                                if ($config && $shift->clock_out) {
                                                    if ($config->pay_type === 'hourly') {
                                                        $totalPay += $shift->hours_worked * $config->rate;
                                                    } elseif ($config->pay_type === 'per_delivery') {
                                                        $totalPay += $shift->deliveries_count * $config->rate;
                                                    }
                                                    if ($config->bonus_per_delivery) {
                                                        $totalPay += $shift->deliveries_count * $config->bonus_per_delivery;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <strong class="text-success">R {{ number_format($totalPay, 2) }}</strong>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $shifts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
