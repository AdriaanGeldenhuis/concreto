@extends('layouts.admin')
@section('title', 'Reports & Analytics')

@section('content')
<div class="page-header">
    <h1>Reports & Analytics</h1>
    <div style="display:flex; gap:0.35rem;">
        <a href="{{ route('admin.reports.export', ['type' => 'orders', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn btn-primary btn-sm">Export Orders</a>
        <a href="{{ route('admin.reports.export', ['type' => 'products', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn btn-primary btn-sm">Export Products</a>
        <a href="{{ route('admin.reports.export', ['type' => 'customers', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn btn-primary btn-sm">Export Customers</a>
    </div>
</div>

{{-- Period Filter --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Period</label>
                <select name="period" class="form-control" onchange="toggleCustomDates(this)">
                    <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="365" {{ $period == '365' ? 'selected' : '' }}>Last 365 Days</option>
                    <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            <div class="form-group" id="from-date-wrapper" style="display:{{ $period == 'custom' ? 'block' : 'none' }};">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from', $from->format('Y-m-d')) }}">
            </div>
            <div class="form-group" id="to-date-wrapper" style="display:{{ $period == 'custom' ? 'block' : 'none' }};">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to', $to->format('Y-m-d')) }}">
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
            </div>
        </form>
        <small class="text-muted" style="display:block; margin-top:0.5rem;">
            Showing data from {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}
        </small>
    </div>
</div>

{{-- Revenue Summary Cards --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Revenue</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($revenue->total_revenue, 2) }}</h3>
        <small class="text-muted">Subtotal: R{{ number_format($revenue->total_subtotal, 2) }}<br>VAT: R{{ number_format($revenue->total_vat, 2) }}<br>Delivery: R{{ number_format($revenue->total_delivery, 2) }}</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Orders</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">{{ number_format($revenue->order_count) }}</h3>
        <small class="text-muted">Orders placed in period</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Avg Order Value</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ $revenue->order_count > 0 ? number_format($revenue->total_revenue / $revenue->order_count, 2) : '0.00' }}</h3>
        <small class="text-muted">Average per order</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Gross Profit</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($revenue->total_revenue - $costData->total_cost, 2) }}</h3>
        <small class="text-muted">Cost: R{{ number_format($costData->total_cost, 2) }}</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Discounts</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--danger, #e74c3c);">R{{ number_format($revenue->total_discounts, 2) }}</h3>
        <small class="text-muted">Discounts applied</small>
    </div></div>
</div>

{{-- Daily Revenue Chart --}}
<div class="card mb-2">
    <div class="card-header">Daily Revenue Trend</div>
    <div class="card-body">
        @if($dailyRevenue->count() > 0)
            @php $maxRevenue = max($dailyRevenue->max('revenue'), 1); @endphp
            <div style="display:flex; align-items:flex-end; justify-content:space-around; height:220px; padding:1rem 0; gap:3px;">
                @foreach($dailyRevenue as $day)
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end;">
                        <div style="width:100%; background:linear-gradient(to top, var(--primary, #3498db), #5dade2); border-radius:4px 4px 0 0; min-height:4px; height:{{ ($day->revenue / $maxRevenue) * 180 }}px; display:flex; align-items:flex-start; justify-content:center; padding-top:3px; transition:all 0.2s;" title="R{{ number_format($day->revenue, 2) }}">
                            <span style="font-size:9px; font-weight:600; color:#fff; text-shadow:0 1px 2px rgba(0,0,0,0.3);">R{{ number_format($day->revenue / 1000, 1) }}k</span>
                        </div>
                        <div style="margin-top:6px; font-size:10px; text-align:center; width:100%;">
                            {{ \Carbon\Carbon::parse($day->date)->format('d/m') }}
                            <br><small class="text-muted">{{ $day->orders }}ord</small>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state"><h3>No revenue data available for this period</h3></div>
        @endif
    </div>
</div>

{{-- Top Products & Customers --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
    <div class="card">
        <div class="card-header">Top 10 Products</div>
        @if($topProducts->count() > 0)
        <div class="table-responsive"><table>
            <thead><tr><th>#</th><th>Product</th><th>Unit</th><th class="text-right">Qty Sold</th><th class="text-right">Revenue</th></tr></thead>
            <tbody>
                @foreach($topProducts as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->unit }}</td>
                    <td class="text-right">{{ number_format($product->total_qty, 2) }}</td>
                    <td class="text-right">R{{ number_format($product->total_revenue, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @else
        <div class="card-body"><div class="empty-state"><h3>No product data available</h3></div></div>
        @endif
    </div>
    <div class="card">
        <div class="card-header">Top 10 Customers</div>
        @if($topCustomers->count() > 0)
        <div class="table-responsive"><table>
            <thead><tr><th>#</th><th>Customer</th><th class="text-right">Orders</th><th class="text-right">Total Spent</th></tr></thead>
            <tbody>
                @foreach($topCustomers as $index => $customer)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div>{{ $customer->name }}</div>
                        <small class="text-muted">{{ $customer->email }}</small>
                    </td>
                    <td class="text-right">{{ number_format($customer->order_count) }}</td>
                    <td class="text-right">R{{ number_format($customer->total_spent, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @else
        <div class="card-body"><div class="empty-state"><h3>No customer data available</h3></div></div>
        @endif
    </div>
</div>

{{-- Driver Performance & Payment Breakdown --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
    <div class="card">
        <div class="card-header">Driver Performance</div>
        @if($driverPerformance->count() > 0)
        <div class="table-responsive"><table>
            <thead><tr><th>#</th><th>Driver</th><th class="text-right">Deliveries</th><th class="text-right">Total Value</th></tr></thead>
            <tbody>
                @foreach($driverPerformance as $index => $driver)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $driver->name }}</td>
                    <td class="text-right">{{ number_format($driver->deliveries) }}</td>
                    <td class="text-right">R{{ number_format($driver->total_value, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @else
        <div class="card-body"><div class="empty-state"><h3>No driver data available</h3></div></div>
        @endif
    </div>
    <div class="card">
        <div class="card-header">Payment Method Breakdown</div>
        @if($paymentBreakdown->count() > 0)
        <div class="table-responsive"><table>
            <thead><tr><th>Method</th><th class="text-right">Count</th><th class="text-right">Total</th></tr></thead>
            <tbody>
                @foreach($paymentBreakdown as $payment)
                <tr>
                    <td><span class="badge badge-primary">{{ ucfirst($payment->provider) }}</span></td>
                    <td class="text-right">{{ number_format($payment->count) }}</td>
                    <td class="text-right">R{{ number_format($payment->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @else
        <div class="card-body"><div class="empty-state"><h3>No payment data available</h3></div></div>
        @endif
    </div>
</div>

{{-- Order Status Distribution --}}
<div class="card">
    <div class="card-header">Order Status Distribution</div>
    <div class="card-body">
        @if($statusDistribution->count() > 0)
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(120px, 1fr)); gap:0.75rem;">
                @foreach($statusDistribution as $status)
                    @php
                        $statusColors = ['pending' => 'warning', 'confirmed' => 'info', 'processing' => 'primary', 'dispatched' => 'secondary', 'delivered' => 'success', 'cancelled' => 'danger', 'failed' => 'danger'];
                        $badgeClass = $statusColors[strtolower($status->status)] ?? 'secondary';
                    @endphp
                    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
                        <span class="badge badge-{{ $badgeClass }}" style="margin-bottom:0.35rem;">{{ ucfirst($status->status) }}</span>
                        <h3 class="mb-0">{{ number_format($status->count) }}</h3>
                        <small class="text-muted">{{ $revenue->order_count > 0 ? number_format(($status->count / $revenue->order_count) * 100, 1) : '0' }}%</small>
                    </div></div>
                @endforeach
            </div>
        @else
            <div class="empty-state"><h3>No status data available</h3></div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function toggleCustomDates(select) {
    var fromW = document.getElementById('from-date-wrapper');
    var toW = document.getElementById('to-date-wrapper');
    if (select.value === 'custom') { fromW.style.display = 'block'; toW.style.display = 'block'; }
    else { fromW.style.display = 'none'; toW.style.display = 'none'; }
}
</script>
@endpush
@endsection
