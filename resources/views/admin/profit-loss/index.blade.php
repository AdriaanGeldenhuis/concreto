@extends('layouts.admin')
@section('title', 'Profit & Loss')

@section('content')
<div class="page-header">
    <h1>Profit & Loss Statement</h1>
    <a href="{{ route('admin.profit-loss.export', ['from' => $from, 'to' => $to]) }}" class="btn btn-primary btn-sm">Export CSV</a>
</div>

{{-- Period Selector --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" action="{{ route('admin.profit-loss.index') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Period</label>
                <select name="period" class="form-control" onchange="togglePlDates(this)">
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
                    <option value="taxyear" {{ $period === 'taxyear' ? 'selected' : '' }}>Tax Year (Mar-Feb)</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            <div class="form-group" id="pl-from-wrapper" style="display:{{ $period === 'custom' ? 'block' : 'none' }};">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="form-group" id="pl-to-wrapper" style="display:{{ $period === 'custom' ? 'block' : 'none' }};">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Generate</button>
            </div>
        </form>
        <small class="text-muted" style="display:block; margin-top:0.5rem;">
            Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        </small>
    </div>
</div>

{{-- P&L Summary Cards --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Revenue (excl. VAT)</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($totalRevenue, 2) }}</h3>
        <small class="text-muted">{{ number_format($orderCount) }} orders</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Gross Profit</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:{{ $grossProfit >= 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};">R{{ number_format($grossProfit, 2) }}</h3>
        <small class="text-muted">{{ number_format($grossMarginPct, 1) }}% margin</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Operating Expenses</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--danger, #e74c3c);">R{{ number_format($totalOpex, 2) }}</h3>
        <small class="text-muted">Wages + Diesel + Expenses + Refunds</small>
    </div></div>
    <div class="card" style="border-left:4px solid {{ $netProfit >= 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Net Profit</h6>
        <h3 class="mb-0 font-semibold" style="margin-top:0.25rem; color:{{ $netProfit >= 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};">R{{ number_format($netProfit, 2) }}</h3>
        <small class="text-muted">{{ number_format($netMarginPct, 1) }}% net margin</small>
    </div></div>
</div>

{{-- P&L Statement --}}
<div class="card mb-2">
    <div class="card-header">Income Statement</div>
    <div class="table-responsive"><table style="max-width:700px;">
        <tbody>
            {{-- Revenue Section --}}
            <tr style="background:rgba(255,255,255,0.03);">
                <td colspan="2" class="font-semibold" style="font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em;">Revenue</td>
            </tr>
            <tr>
                <td style="padding-left:2rem;">Sales Revenue (excl. VAT)</td>
                <td class="text-right">R{{ number_format($totalRevenue, 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left:2rem;">Delivery Fees</td>
                <td class="text-right">R{{ number_format($totalDeliveryFees, 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left:2rem;">Less: Discounts</td>
                <td class="text-right" style="color:var(--danger, #e74c3c);">-R{{ number_format($totalDiscounts, 2) }}</td>
            </tr>
            <tr class="font-semibold" style="border-top:2px solid rgba(255,255,255,0.15);">
                <td style="padding-left:2rem;">Net Revenue</td>
                <td class="text-right">R{{ number_format($totalRevenue + $totalDeliveryFees - $totalDiscounts, 2) }}</td>
            </tr>

            {{-- COGS Section --}}
            <tr style="background:rgba(255,255,255,0.03);">
                <td colspan="2" class="font-semibold" style="font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em;">Cost of Goods Sold</td>
            </tr>
            <tr>
                <td style="padding-left:2rem;">Product Cost (cost_price x qty sold)</td>
                <td class="text-right" style="color:var(--danger, #e74c3c);">-R{{ number_format($totalCogs, 2) }}</td>
            </tr>
            <tr class="font-semibold" style="border-top:2px solid rgba(255,255,255,0.15); background:{{ $grossProfit >= 0 ? 'rgba(39,174,96,0.1)' : 'rgba(231,76,60,0.1)' }};">
                <td>GROSS PROFIT</td>
                <td class="text-right">R{{ number_format($grossProfit, 2) }} <small>({{ number_format($grossMarginPct, 1) }}%)</small></td>
            </tr>

            {{-- Operating Expenses --}}
            <tr style="background:rgba(255,255,255,0.03);">
                <td colspan="2" class="font-semibold" style="font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em;">Operating Expenses</td>
            </tr>
            <tr>
                <td style="padding-left:2rem;">Driver Wages</td>
                <td class="text-right" style="color:var(--danger, #e74c3c);">-R{{ number_format($totalDriverWages, 2) }}</td>
            </tr>
            @foreach($driverWageDetails as $dw)
                <tr>
                    <td style="padding-left:3rem;"><small class="text-muted">{{ $dw->name }} ({{ $dw->pay_type }}: {{ $dw->hours }}h / {{ $dw->deliveries }} del.)</small></td>
                    <td class="text-right"><small class="text-muted">R{{ number_format($dw->pay, 2) }}</small></td>
                </tr>
            @endforeach
            <tr>
                <td style="padding-left:2rem;">Diesel / Fuel</td>
                <td class="text-right" style="color:var(--danger, #e74c3c);">-R{{ number_format($totalDiesel, 2) }} <small>({{ $dieselFills }} fills, {{ number_format($totalDieselLitres, 0) }}L)</small></td>
            </tr>
            @foreach($dieselByDriver as $dd)
                <tr>
                    <td style="padding-left:3rem;"><small class="text-muted">{{ $dd->name }} ({{ $dd->fills }} fills, {{ number_format($dd->litres, 0) }}L)</small></td>
                    <td class="text-right"><small class="text-muted">R{{ number_format($dd->total, 2) }}</small></td>
                </tr>
            @endforeach
            @if($totalExpenses > 0)
            <tr>
                <td style="padding-left:2rem;">General Expenses</td>
                <td class="text-right" style="color:var(--danger, #e74c3c);">-R{{ number_format($totalExpenses, 2) }}</td>
            </tr>
            @foreach($expensesByCategory as $ec)
                <tr>
                    <td style="padding-left:3rem;"><small class="text-muted"><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:{{ $ec->color }};margin-right:4px;"></span>{{ $ec->name }} ({{ $ec->count }})</small></td>
                    <td class="text-right"><small class="text-muted">R{{ number_format($ec->total, 2) }}</small></td>
                </tr>
            @endforeach
            @endif
            <tr>
                <td style="padding-left:2rem;">Refunds Issued</td>
                <td class="text-right" style="color:var(--danger, #e74c3c);">-R{{ number_format($totalRefunds, 2) }} <small>({{ $refundCount }})</small></td>
            </tr>
            @if(isset($totalBankFees) && $totalBankFees > 0)
            <tr>
                <td style="padding-left:2rem;">Bank Fees & Charges</td>
                <td class="text-right" style="color:var(--danger, #e74c3c);">-R{{ number_format($totalBankFees, 2) }}</td>
            </tr>
            @endif
            <tr class="font-semibold" style="border-top:1px solid rgba(255,255,255,0.15);">
                <td style="padding-left:2rem;">Total Operating Expenses</td>
                <td class="text-right" style="color:var(--danger, #e74c3c);">-R{{ number_format($totalOpex, 2) }}</td>
            </tr>

            {{-- Net Profit --}}
            <tr class="font-semibold" style="border-top:3px double rgba(255,255,255,0.2); background:{{ $netProfit >= 0 ? 'rgba(39,174,96,0.1)' : 'rgba(231,76,60,0.1)' }}; font-size:1.1rem;">
                <td>NET PROFIT</td>
                <td class="text-right">R{{ number_format($netProfit, 2) }} <small>({{ number_format($netMarginPct, 1) }}%)</small></td>
            </tr>

            {{-- VAT Info --}}
            <tr style="background:rgba(230,126,34,0.08);">
                <td><small class="text-muted">VAT Collected (liability, not revenue)</small></td>
                <td class="text-right"><small class="text-muted">R{{ number_format($totalVat, 2) }}</small></td>
            </tr>
        </tbody>
    </table></div>
</div>

{{-- Monthly Trend --}}
@if($monthlyTrend->count() > 0)
<div class="card mb-2">
    <div class="card-header">Monthly Trend</div>
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Month</th>
            <th class="text-right">Revenue</th>
            <th class="text-right">COGS</th>
            <th class="text-right">Gross Profit</th>
            <th class="text-right">Margin</th>
            <th class="text-right">Orders</th>
        </tr></thead>
        <tbody>
            @foreach($monthlyTrend as $m)
                @php $margin = $m->revenue > 0 ? ($m->gross_profit / $m->revenue) * 100 : 0; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($m->month . '-01')->format('F Y') }}</td>
                    <td class="text-right">R{{ number_format($m->revenue, 2) }}</td>
                    <td class="text-right" style="color:var(--danger, #e74c3c);">R{{ number_format($m->cogs, 2) }}</td>
                    <td class="text-right" style="color:{{ $m->gross_profit >= 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};">R{{ number_format($m->gross_profit, 2) }}</td>
                    <td class="text-right">{{ number_format($margin, 1) }}%</td>
                    <td class="text-right">{{ number_format($m->orders) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table></div>
</div>
@endif

@push('scripts')
<script>
function togglePlDates(select) {
    var fromW = document.getElementById('pl-from-wrapper');
    var toW = document.getElementById('pl-to-wrapper');
    if (select.value === 'custom') { fromW.style.display = 'block'; toW.style.display = 'block'; }
    else { fromW.style.display = 'none'; toW.style.display = 'none'; }
}
</script>
@endpush
@endsection
