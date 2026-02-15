@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Reports & Analytics</h1>
        <div class="btn-group">
            <a href="{{ route('admin.reports.export', ['type' => 'orders', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn btn-primary">
                <i class="fas fa-download"></i> Export Orders
            </a>
            <a href="{{ route('admin.reports.export', ['type' => 'products', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn btn-primary">
                <i class="fas fa-download"></i> Export Products
            </a>
            <a href="{{ route('admin.reports.export', ['type' => 'customers', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn btn-primary">
                <i class="fas fa-download"></i> Export Customers
            </a>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="period" class="form-label">Period</label>
                    <select name="period" id="period" class="form-select" onchange="toggleCustomDates(this)">
                        <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="365" {{ $period == '365' ? 'selected' : '' }}>Last 365 Days</option>
                        <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3" id="from-date-wrapper" style="display: {{ $period == 'custom' ? 'block' : 'none' }}">
                    <label for="from" class="form-label">From Date</label>
                    <input type="date" name="from" id="from" class="form-control" value="{{ request('from', $from->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3" id="to-date-wrapper" style="display: {{ $period == 'custom' ? 'block' : 'none' }}">
                    <label for="to" class="form-label">To Date</label>
                    <input type="date" name="to" id="to" class="form-control" value="{{ request('to', $to->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filter
                    </button>
                </div>
            </form>
            <div class="mt-2">
                <small class="text-muted">
                    Showing data from {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}
                </small>
            </div>
        </div>
    </div>

    <!-- Revenue Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Revenue</h6>
                            <h3 class="mb-0">R {{ number_format($revenue->total_revenue, 2) }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        Subtotal: R {{ number_format($revenue->total_subtotal, 2) }}<br>
                        VAT: R {{ number_format($revenue->total_vat, 2) }}<br>
                        Delivery: R {{ number_format($revenue->total_delivery, 2) }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Orders</h6>
                            <h3 class="mb-0">{{ number_format($revenue->order_count) }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-muted">Orders placed in period</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Avg Order Value</h6>
                            <h3 class="mb-0">
                                R {{ $revenue->order_count > 0 ? number_format($revenue->total_revenue / $revenue->order_count, 2) : '0.00' }}
                            </h3>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-muted">Average per order</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Gross Profit</h6>
                            <h3 class="mb-0">R {{ number_format($revenue->total_revenue - $costData->total_cost, 2) }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-piggy-bank fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        Revenue - Cost<br>
                        Cost: R {{ number_format($costData->total_cost, 2) }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Discounts</h6>
                            <h3 class="mb-0">R {{ number_format($revenue->total_discounts, 2) }}</h3>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-tags fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-muted">Discounts applied</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Revenue Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Daily Revenue Trend</h5>
        </div>
        <div class="card-body">
            @if($dailyRevenue->count() > 0)
                @php
                    $maxRevenue = $dailyRevenue->max('revenue');
                    $maxRevenue = $maxRevenue > 0 ? $maxRevenue : 1;
                @endphp
                <div class="revenue-chart">
                    @foreach($dailyRevenue as $day)
                        <div class="chart-bar-wrapper">
                            <div class="chart-bar" style="height: {{ ($day->revenue / $maxRevenue) * 200 }}px;" title="R {{ number_format($day->revenue, 2) }}">
                                <span class="chart-value">R {{ number_format($day->revenue / 1000, 1) }}k</span>
                            </div>
                            <div class="chart-label">
                                {{ \Carbon\Carbon::parse($day->date)->format('d/m') }}
                                <br>
                                <small class="text-muted">{{ $day->orders }} orders</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted text-center py-4">No revenue data available for this period</p>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <!-- Top Products -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top 10 Products</h5>
                </div>
                <div class="card-body p-0">
                    @if($topProducts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Unit</th>
                                        <th class="text-end">Qty Sold</th>
                                        <th class="text-end">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topProducts as $index => $product)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->unit }}</td>
                                            <td class="text-end">{{ number_format($product->total_qty, 2) }}</td>
                                            <td class="text-end">R {{ number_format($product->total_revenue, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No product data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top 10 Customers</h5>
                </div>
                <div class="card-body p-0">
                    @if($topCustomers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer</th>
                                        <th class="text-end">Orders</th>
                                        <th class="text-end">Total Spent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topCustomers as $index => $customer)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div>{{ $customer->name }}</div>
                                                <small class="text-muted">{{ $customer->email }}</small>
                                            </td>
                                            <td class="text-end">{{ number_format($customer->order_count) }}</td>
                                            <td class="text-end">R {{ number_format($customer->total_spent, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No customer data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Driver Performance -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Driver Performance</h5>
                </div>
                <div class="card-body p-0">
                    @if($driverPerformance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Driver</th>
                                        <th class="text-end">Deliveries</th>
                                        <th class="text-end">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($driverPerformance as $index => $driver)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $driver->name }}</td>
                                            <td class="text-end">{{ number_format($driver->deliveries) }}</td>
                                            <td class="text-end">R {{ number_format($driver->total_value, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No driver performance data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Method Breakdown -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Method Breakdown</h5>
                </div>
                <div class="card-body p-0">
                    @if($paymentBreakdown->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Payment Method</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentBreakdown as $payment)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ ucfirst($payment->provider) }}</span>
                                            </td>
                                            <td class="text-end">{{ number_format($payment->count) }}</td>
                                            <td class="text-end">R {{ number_format($payment->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No payment data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status Distribution -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Status Distribution</h5>
                </div>
                <div class="card-body">
                    @if($statusDistribution->count() > 0)
                        <div class="row">
                            @foreach($statusDistribution as $status)
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'info',
                                                    'processing' => 'primary',
                                                    'dispatched' => 'secondary',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'danger',
                                                    'failed' => 'danger',
                                                ];
                                                $badgeClass = $statusColors[strtolower($status->status)] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $badgeClass }} mb-2">{{ ucfirst($status->status) }}</span>
                                            <h3 class="mb-0">{{ number_format($status->count) }}</h3>
                                            <small class="text-muted">
                                                {{ $revenue->order_count > 0 ? number_format(($status->count / $revenue->order_count) * 100, 1) : '0' }}%
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No status data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .revenue-chart {
        display: flex;
        align-items: flex-end;
        justify-content: space-around;
        height: 250px;
        padding: 20px 0;
        gap: 5px;
    }

    .chart-bar-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
    }

    .chart-bar {
        width: 100%;
        background: linear-gradient(to top, #4e73df, #5a8cff);
        border-radius: 4px 4px 0 0;
        position: relative;
        transition: all 0.3s ease;
        min-height: 20px;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding-top: 5px;
    }

    .chart-bar:hover {
        background: linear-gradient(to top, #2e59d9, #4e73df);
        transform: translateY(-5px);
    }

    .chart-value {
        font-size: 10px;
        font-weight: bold;
        color: white;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }

    .chart-label {
        margin-top: 10px;
        font-size: 11px;
        text-align: center;
        width: 100%;
    }

    .card {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: none;
    }

    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
</style>

<script>
    function toggleCustomDates(select) {
        const fromWrapper = document.getElementById('from-date-wrapper');
        const toWrapper = document.getElementById('to-date-wrapper');

        if (select.value === 'custom') {
            fromWrapper.style.display = 'block';
            toWrapper.style.display = 'block';
        } else {
            fromWrapper.style.display = 'none';
            toWrapper.style.display = 'none';
        }
    }
</script>
@endsection
