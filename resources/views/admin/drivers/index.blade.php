@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <h3 class="text-light">Drivers Management</h3>
        </div>
    </div>

    <div class="row">
        @forelse($drivers as $driver)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card bg-dark text-light h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $driver->name }}</h5>
                    @if($driver->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Contact Information</h6>
                        <p class="mb-1"><strong>Phone:</strong> {{ $driver->phone }}</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Current Shift</h6>
                        @if($driver->current_shift)
                            <span class="badge bg-success">On Shift</span>
                            <p class="mb-0 mt-2 small">Started: {{ $driver->current_shift->clock_in->format('H:i') }}</p>
                        @else
                            <span class="badge bg-warning">Off Shift</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Today's Statistics</h6>
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1 small"><strong>Deliveries:</strong></p>
                                <h4 class="text-primary">{{ $driver->today_deliveries }}</h4>
                            </div>
                            <div class="col-6">
                                <p class="mb-1 small"><strong>Total:</strong></p>
                                <h4 class="text-info">{{ $driver->total_deliveries }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Month Statistics</h6>
                        <div class="row">
                            <div class="col-4">
                                <p class="mb-1 small"><strong>Hours:</strong></p>
                                <p class="mb-0">{{ number_format($driver->month_hours, 1) }}h</p>
                            </div>
                            <div class="col-4">
                                <p class="mb-1 small"><strong>Deliveries:</strong></p>
                                <p class="mb-0">{{ $driver->month_deliveries }}</p>
                            </div>
                            <div class="col-4">
                                <p class="mb-1 small"><strong>Pay:</strong></p>
                                <p class="mb-0">R {{ number_format($driver->month_pay, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    @if($driver->salaryConfig)
                    <div class="mb-3">
                        <h6 class="text-muted">Salary Configuration</h6>
                        <p class="mb-1 small">
                            <strong>Type:</strong>
                            <span class="badge bg-info">{{ ucfirst($driver->salaryConfig->pay_type) }}</span>
                        </p>
                        <p class="mb-1 small">
                            <strong>Rate:</strong>
                            @if($driver->salaryConfig->pay_type === 'hourly')
                                R {{ number_format($driver->salaryConfig->rate, 2) }}/hour
                            @else
                                R {{ number_format($driver->salaryConfig->rate, 2) }}/delivery
                            @endif
                        </p>
                        @if($driver->salaryConfig->bonus_per_delivery)
                        <p class="mb-1 small">
                            <strong>Bonus:</strong> R {{ number_format($driver->salaryConfig->bonus_per_delivery, 2) }}/delivery
                        </p>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.drivers.shifts', $driver->id) }}" class="btn btn-primary btn-sm w-100">
                        View Shifts & Salary
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card bg-dark text-light">
                <div class="card-body text-center">
                    <p class="mb-0">No drivers found.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
