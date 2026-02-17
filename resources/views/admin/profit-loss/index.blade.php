@extends('layouts.admin')
@section('title', 'Profit & Loss')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Profit & Loss Statement</h1>
        <a href="{{ route('admin.profit-loss.export', ['from' => $from, 'to' => $to]) }}" class="btn btn-primary">
            Export CSV
        </a>
    </div>

    <!-- Period Selector -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.profit-loss.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select" onchange="togglePlDates(this)">
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                        <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
                        <option value="taxyear" {{ $period === 'taxyear' ? 'selected' : '' }}>Tax Year (Mar-Feb)</option>
                        <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3" id="pl-from-wrapper" style="display: {{ $period === 'custom' ? 'block' : 'none' }}">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-3" id="pl-to-wrapper" style="display: {{ $period === 'custom' ? 'block' : 'none' }}">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
            <small class="text-muted mt-2 d-block">
                Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </small>
        </div>
    </div>

    <!-- P&L Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Revenue (excl. VAT)</h6>
                    <h3 class="mb-0">R {{ number_format($totalRevenue, 2) }}</h3>
                    <small class="text-muted">{{ number_format($orderCount) }} orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Gross Profit</h6>
                    <h3 class="mb-0 {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">R {{ number_format($grossProfit, 2) }}</h3>
                    <small class="text-muted">{{ number_format($grossMarginPct, 1) }}% margin</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Operating Expenses</h6>
                    <h3 class="mb-0 text-danger">R {{ number_format($totalOpex, 2) }}</h3>
                    <small class="text-muted">Wages + Refunds</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid {{ $netProfit >= 0 ? 'var(--success, #1cc88a)' : 'var(--danger, #e74a3b)' }};">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Net Profit</h6>
                    <h3 class="mb-0 fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">R {{ number_format($netProfit, 2) }}</h3>
                    <small class="text-muted">{{ number_format($netMarginPct, 1) }}% net margin</small>
                </div>
            </div>
        </div>
    </div>

    <!-- P&L Statement -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Income Statement</h5></div>
        <div class="card-body p-0">
            <table class="table mb-0" style="max-width: 700px;">
                <tbody>
                    <!-- Revenue Section -->
                    <tr style="background: #f8f9fc;">
                        <td colspan="2" class="fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.05em;">Revenue</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2rem;">Sales Revenue (excl. VAT)</td>
                        <td class="text-end">R {{ number_format($totalRevenue, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2rem;">Delivery Fees</td>
                        <td class="text-end">R {{ number_format($totalDeliveryFees, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2rem;">Less: Discounts</td>
                        <td class="text-end text-danger">- R {{ number_format($totalDiscounts, 2) }}</td>
                    </tr>
                    <tr class="fw-bold" style="border-top: 2px solid #dee2e6;">
                        <td style="padding-left: 2rem;">Net Revenue</td>
                        <td class="text-end">R {{ number_format($totalRevenue + $totalDeliveryFees - $totalDiscounts, 2) }}</td>
                    </tr>

                    <!-- COGS Section -->
                    <tr style="background: #f8f9fc;">
                        <td colspan="2" class="fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.05em;">Cost of Goods Sold</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2rem;">Product Cost (cost_price x qty sold)</td>
                        <td class="text-end text-danger">- R {{ number_format($totalCogs, 2) }}</td>
                    </tr>
                    <tr class="fw-bold" style="border-top: 2px solid #dee2e6; background: {{ $grossProfit >= 0 ? '#d4edda' : '#f8d7da' }};">
                        <td>GROSS PROFIT</td>
                        <td class="text-end">R {{ number_format($grossProfit, 2) }} <small>({{ number_format($grossMarginPct, 1) }}%)</small></td>
                    </tr>

                    <!-- Operating Expenses -->
                    <tr style="background: #f8f9fc;">
                        <td colspan="2" class="fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.05em;">Operating Expenses</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 2rem;">Driver Wages</td>
                        <td class="text-end text-danger">- R {{ number_format($totalDriverWages, 2) }}</td>
                    </tr>
                    @foreach($driverWageDetails as $dw)
                        <tr>
                            <td style="padding-left: 3rem;"><small class="text-muted">{{ $dw->name }} ({{ $dw->pay_type }}: {{ $dw->hours }}h / {{ $dw->deliveries }} del.)</small></td>
                            <td class="text-end"><small class="text-muted">R {{ number_format($dw->pay, 2) }}</small></td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="padding-left: 2rem;">Refunds Issued</td>
                        <td class="text-end text-danger">- R {{ number_format($totalRefunds, 2) }} <small>({{ $refundCount }})</small></td>
                    </tr>
                    @if(isset($totalBankFees) && $totalBankFees > 0)
                    <tr>
                        <td style="padding-left: 2rem;">Bank Fees & Charges</td>
                        <td class="text-end text-danger">- R {{ number_format($totalBankFees, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="fw-bold" style="border-top: 1px solid #dee2e6;">
                        <td style="padding-left: 2rem;">Total Operating Expenses</td>
                        <td class="text-end text-danger">- R {{ number_format($totalOpex, 2) }}</td>
                    </tr>

                    <!-- Net Profit -->
                    <tr class="fw-bold" style="border-top: 3px double #dee2e6; background: {{ $netProfit >= 0 ? '#d4edda' : '#f8d7da' }}; font-size: 1.1rem;">
                        <td>NET PROFIT</td>
                        <td class="text-end">R {{ number_format($netProfit, 2) }} <small>({{ number_format($netMarginPct, 1) }}%)</small></td>
                    </tr>

                    <!-- VAT Info -->
                    <tr style="background: #fff3cd;">
                        <td><small class="text-muted">VAT Collected (liability, not revenue)</small></td>
                        <td class="text-end"><small class="text-muted">R {{ number_format($totalVat, 2) }}</small></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Monthly Trend -->
    @if($monthlyTrend->count() > 0)
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Monthly Trend</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">COGS</th>
                            <th class="text-end">Gross Profit</th>
                            <th class="text-end">Margin</th>
                            <th class="text-center">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyTrend as $m)
                            @php $margin = $m->revenue > 0 ? ($m->gross_profit / $m->revenue) * 100 : 0; @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($m->month . '-01')->format('F Y') }}</td>
                                <td class="text-end">R {{ number_format($m->revenue, 2) }}</td>
                                <td class="text-end text-danger">R {{ number_format($m->cogs, 2) }}</td>
                                <td class="text-end {{ $m->gross_profit >= 0 ? 'text-success' : 'text-danger' }}">R {{ number_format($m->gross_profit, 2) }}</td>
                                <td class="text-end">{{ number_format($margin, 1) }}%</td>
                                <td class="text-center">{{ number_format($m->orders) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function togglePlDates(select) {
    const fromW = document.getElementById('pl-from-wrapper');
    const toW = document.getElementById('pl-to-wrapper');
    if (select.value === 'custom') {
        fromW.style.display = 'block';
        toW.style.display = 'block';
    } else {
        fromW.style.display = 'none';
        toW.style.display = 'none';
    }
}
</script>
@endsection
