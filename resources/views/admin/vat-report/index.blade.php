@extends('layouts.admin')
@section('title', 'VAT Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">VAT Report (Output VAT)</h1>
        <a href="{{ route('admin.vat-report.export', ['from' => $from, 'to' => $to]) }}" class="btn btn-primary">
            Export CSV
        </a>
    </div>

    <!-- Period Selector -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.vat-report.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select" onchange="toggleVatDates(this)">
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="bimonth" {{ $period === 'bimonth' ? 'selected' : '' }}>Bi-Monthly (VAT201)</option>
                        <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                        <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Tax Year (Mar-Feb)</option>
                        <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3" id="vat-from-wrapper" style="display: {{ $period === 'custom' ? 'block' : 'none' }}">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-3" id="vat-to-wrapper" style="display: {{ $period === 'custom' ? 'block' : 'none' }}">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
            <div class="mt-2">
                <small class="text-muted">
                    Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                    | VAT Rate: {{ $vatRate }}%
                </small>
            </div>
        </div>
    </div>

    <!-- VAT Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Sales (excl. VAT)</h6>
                    <h3 class="mb-0">R {{ number_format($outputVat->total_excl_vat ?? 0, 2) }}</h3>
                    <small class="text-muted">{{ number_format($outputVat->invoice_count ?? 0) }} invoices</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid var(--warning, #f6c23e);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Output VAT Collected</h6>
                    <h3 class="mb-0">R {{ number_format($grossOutputVat, 2) }}</h3>
                    <small class="text-muted">At {{ $vatRate }}% rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">VAT on Refunds</h6>
                    <h3 class="mb-0 text-danger">- R {{ number_format($vatOnRefunds, 2) }}</h3>
                    <small class="text-muted">{{ number_format($refundVat->refund_count ?? 0) }} refunds (R {{ number_format($refundVat->total_refunded ?? 0, 2) }})</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid var(--primary, #4e73df);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Net VAT Payable to SARS</h6>
                    <h3 class="mb-0 fw-bold">R {{ number_format($netVatPayable, 2) }}</h3>
                    <small class="text-muted">Output VAT - Refund VAT</small>
                </div>
            </div>
        </div>
    </div>

    <!-- VAT201 Summary Box -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">SARS VAT201 Summary</h5></div>
        <div class="card-body">
            <p class="text-muted mb-3">Use these figures to complete your VAT201 return on SARS eFiling.</p>
            <div class="table-responsive">
                <table class="table table-bordered mb-0" style="max-width: 600px;">
                    <tbody>
                        <tr>
                            <td class="fw-bold" style="width: 60%;">Total value of supplies (excl. VAT)</td>
                            <td class="text-end">R {{ number_format($outputVat->total_excl_vat ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Output tax (VAT on supplies)</td>
                            <td class="text-end">R {{ number_format($grossOutputVat, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Less: Adjustments (refunds/credit notes)</td>
                            <td class="text-end text-danger">- R {{ number_format($vatOnRefunds, 2) }}</td>
                        </tr>
                        <tr style="background: var(--primary-subtle, #f0f4ff);">
                            <td class="fw-bold">Net output tax payable</td>
                            <td class="text-end fw-bold">R {{ number_format($netVatPayable, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Total value of supplies (incl. VAT)</td>
                            <td class="text-end">R {{ number_format($outputVat->total_incl_vat ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Delivery fees collected</td>
                            <td class="text-end">R {{ number_format($outputVat->total_delivery ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Discounts given</td>
                            <td class="text-end">R {{ number_format($outputVat->total_discounts ?? 0, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-muted mt-3 mb-0">
                <small><strong>Note:</strong> This report shows output VAT only. Input VAT (VAT on purchases/expenses) must be tracked separately for your full VAT201 submission. Consult your accountant for the complete return.</small>
            </p>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    @if($monthlyBreakdown->count() > 0)
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Monthly Breakdown</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Sales (excl. VAT)</th>
                            <th class="text-end">Output VAT</th>
                            <th class="text-end">Sales (incl. VAT)</th>
                            <th class="text-center">Invoices</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyBreakdown as $month)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($month->month . '-01')->format('F Y') }}</td>
                                <td class="text-end">R {{ number_format($month->excl_vat, 2) }}</td>
                                <td class="text-end fw-bold">R {{ number_format($month->vat, 2) }}</td>
                                <td class="text-end">R {{ number_format($month->incl_vat, 2) }}</td>
                                <td class="text-center">{{ number_format($month->invoices) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold" style="background: var(--primary-subtle, #f0f4ff);">
                            <td>TOTAL</td>
                            <td class="text-end">R {{ number_format($monthlyBreakdown->sum('excl_vat'), 2) }}</td>
                            <td class="text-end">R {{ number_format($monthlyBreakdown->sum('vat'), 2) }}</td>
                            <td class="text-end">R {{ number_format($monthlyBreakdown->sum('incl_vat'), 2) }}</td>
                            <td class="text-center">{{ number_format($monthlyBreakdown->sum('invoices')) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function toggleVatDates(select) {
    const fromW = document.getElementById('vat-from-wrapper');
    const toW = document.getElementById('vat-to-wrapper');
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
