@extends('layouts.admin')
@section('title', 'VAT Report')

@section('content')
<div class="page-header">
    <h1>VAT Report</h1>
    <a href="{{ route('admin.vat-report.export', ['from' => $from, 'to' => $to]) }}" class="btn btn-primary btn-sm">Export CSV</a>
</div>

{{-- Period Selector --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" action="{{ route('admin.vat-report.index') }}" class="filters">
            <div class="form-group">
                <label class="form-label">Period</label>
                <select name="period" class="form-control" onchange="toggleVatDates(this)">
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="bimonth" {{ $period === 'bimonth' ? 'selected' : '' }}>Bi-Monthly (VAT201)</option>
                    <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Tax Year (Mar-Feb)</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            <div class="form-group" id="vat-from-wrapper" style="display:{{ $period === 'custom' ? 'block' : 'none' }};">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="form-group" id="vat-to-wrapper" style="display:{{ $period === 'custom' ? 'block' : 'none' }};">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Generate Report</button>
            </div>
        </form>
        <small class="text-muted" style="display:block; margin-top:0.5rem;">
            Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M Y') }} | VAT Rate: {{ $vatRate }}%
        </small>
    </div>
</div>

{{-- VAT Summary Cards --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card" style="border-left:4px solid var(--warning, #e67e22);"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Output VAT (Sales)</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($grossOutputVat, 2) }}</h3>
        <small class="text-muted">{{ number_format($outputVat->invoice_count ?? 0) }} invoices</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Less: VAT on Refunds</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--danger, #e74c3c);">-R{{ number_format($vatOnRefunds, 2) }}</h3>
        <small class="text-muted">{{ number_format($refundVat->refund_count ?? 0) }} refunds</small>
    </div></div>
    <div class="card" style="border-left:4px solid var(--success, #27ae60);"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Input VAT (Claimable)</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">R{{ number_format($totalInputVat, 2) }}</h3>
        <small class="text-muted">On expenses & purchases</small>
    </div></div>
    <div class="card" style="border-left:4px solid var(--primary, #3498db);"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Net VAT {{ $netVatPayable >= 0 ? 'Payable' : 'Refund' }}</h6>
        <h3 class="mb-0 font-semibold" style="margin-top:0.25rem; color:{{ $netVatPayable >= 0 ? 'inherit' : 'var(--success, #27ae60)' }};">
            {{ $netVatPayable >= 0 ? '' : '-' }}R{{ number_format(abs($netVatPayable), 2) }}
        </h3>
        <small class="text-muted">{{ $netVatPayable >= 0 ? 'Owe to SARS' : 'Claim from SARS' }}</small>
    </div></div>
</div>

{{-- VAT201 Summary Box --}}
<div class="card mb-2">
    <div class="card-header">SARS VAT201 Summary</div>
    <div class="card-body">
        <p class="text-muted" style="margin-bottom:0.75rem;">Use these figures to complete your VAT201 return on SARS eFiling.</p>
        <div class="table-responsive"><table style="max-width:700px;">
            <tbody>
                <tr><td colspan="2" class="font-semibold" style="padding-top:0.75rem; color:var(--warning, #e67e22);">OUTPUT TAX</td></tr>
                <tr>
                    <td style="width:60%;">Total value of supplies (excl. VAT)</td>
                    <td class="text-right">R{{ number_format($outputVat->total_excl_vat ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Output tax (VAT on supplies)</td>
                    <td class="text-right">R{{ number_format($grossOutputVat, 2) }}</td>
                </tr>
                <tr>
                    <td>Less: Adjustments (refunds/credit notes)</td>
                    <td class="text-right" style="color:var(--danger, #e74c3c);">-R{{ number_format($vatOnRefunds, 2) }}</td>
                </tr>
                <tr class="font-semibold" style="background:rgba(255,255,255,0.03);">
                    <td>Net output tax</td>
                    <td class="text-right">R{{ number_format($netOutputVat, 2) }}</td>
                </tr>

                <tr><td colspan="2" class="font-semibold" style="padding-top:1rem; color:var(--success, #27ae60);">INPUT TAX (Claimable)</td></tr>
                <tr>
                    <td>VAT on general expenses</td>
                    <td class="text-right">R{{ number_format($expenseInputVat->total_vat ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>VAT on diesel/fuel (R{{ number_format($dieselTotal, 2) }} total)</td>
                    <td class="text-right">R{{ number_format($dieselVat, 2) }}</td>
                </tr>
                <tr>
                    <td>VAT on supplier invoices</td>
                    <td class="text-right">R{{ number_format($supplierInputVat->total_vat ?? 0, 2) }}</td>
                </tr>
                <tr class="font-semibold" style="background:rgba(255,255,255,0.03);">
                    <td>Total input tax</td>
                    <td class="text-right">R{{ number_format($totalInputVat, 2) }}</td>
                </tr>

                <tr><td colspan="2" style="padding-top:0.75rem;"></td></tr>
                <tr class="font-semibold" style="background:rgba(255,255,255,0.06); font-size:1.1em;">
                    <td>{{ $netVatPayable >= 0 ? 'NET VAT PAYABLE TO SARS' : 'NET VAT REFUND FROM SARS' }}</td>
                    <td class="text-right" style="color:{{ $netVatPayable >= 0 ? 'var(--warning, #e67e22)' : 'var(--success, #27ae60)' }};">
                        {{ $netVatPayable >= 0 ? '' : '-' }}R{{ number_format(abs($netVatPayable), 2) }}
                    </td>
                </tr>

                <tr><td colspan="2" style="padding-top:1rem;"></td></tr>
                <tr>
                    <td class="font-semibold">Total value of supplies (incl. VAT)</td>
                    <td class="text-right">R{{ number_format($outputVat->total_incl_vat ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="font-semibold">Delivery fees collected</td>
                    <td class="text-right">R{{ number_format($outputVat->total_delivery ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="font-semibold">Discounts given</td>
                    <td class="text-right">R{{ number_format($outputVat->total_discounts ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table></div>
        @if($netVatPayable < 0)
        <div style="background:rgba(39,174,96,0.08); border-radius:var(--radius-sm, 6px); padding:0.75rem; margin-top:0.75rem; font-size:0.85rem;">
            <strong>VAT Refund:</strong> Your input VAT exceeds output VAT. You may be entitled to a refund of R{{ number_format(abs($netVatPayable), 2) }} from SARS.
        </div>
        @endif
    </div>
</div>

{{-- Input VAT Breakdown --}}
<div class="card mb-2">
    <div class="card-header">Input VAT Breakdown</div>
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Source</th>
            <th class="text-right">Excl. VAT</th>
            <th class="text-right">VAT Amount</th>
            <th class="text-right">Items</th>
        </tr></thead>
        <tbody>
            <tr>
                <td>General Expenses</td>
                <td class="text-right">R{{ number_format($expenseInputVat->total_excl_vat ?? 0, 2) }}</td>
                <td class="text-right font-semibold">R{{ number_format($expenseInputVat->total_vat ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($expenseInputVat->count ?? 0) }}</td>
            </tr>
            <tr>
                <td>Diesel / Fuel</td>
                <td class="text-right">R{{ number_format($dieselTotal, 2) }}</td>
                <td class="text-right font-semibold">R{{ number_format($dieselVat, 2) }}</td>
                <td class="text-right">-</td>
            </tr>
            <tr>
                <td>Supplier Invoices</td>
                <td class="text-right">R{{ number_format($supplierInputVat->total_excl_vat ?? 0, 2) }}</td>
                <td class="text-right font-semibold">R{{ number_format($supplierInputVat->total_vat ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($supplierInputVat->count ?? 0) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="font-semibold" style="background:rgba(255,255,255,0.03);">
                <td>TOTAL INPUT VAT</td>
                <td class="text-right"></td>
                <td class="text-right">R{{ number_format($totalInputVat, 2) }}</td>
                <td class="text-right"></td>
            </tr>
        </tfoot>
    </table></div>
</div>

{{-- Monthly Breakdown --}}
@if($monthlyBreakdown->count() > 0)
<div class="card mb-2">
    <div class="card-header">Monthly Breakdown - Output VAT</div>
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Month</th>
            <th class="text-right">Sales (excl. VAT)</th>
            <th class="text-right">Output VAT</th>
            <th class="text-right">Sales (incl. VAT)</th>
            <th class="text-right">Invoices</th>
        </tr></thead>
        <tbody>
            @foreach($monthlyBreakdown as $month)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($month->month . '-01')->format('F Y') }}</td>
                    <td class="text-right">R{{ number_format($month->excl_vat, 2) }}</td>
                    <td class="text-right font-semibold">R{{ number_format($month->vat, 2) }}</td>
                    <td class="text-right">R{{ number_format($month->incl_vat, 2) }}</td>
                    <td class="text-right">{{ number_format($month->invoices) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-semibold" style="background:rgba(255,255,255,0.03);">
                <td>TOTAL</td>
                <td class="text-right">R{{ number_format($monthlyBreakdown->sum('excl_vat'), 2) }}</td>
                <td class="text-right">R{{ number_format($monthlyBreakdown->sum('vat'), 2) }}</td>
                <td class="text-right">R{{ number_format($monthlyBreakdown->sum('incl_vat'), 2) }}</td>
                <td class="text-right">{{ number_format($monthlyBreakdown->sum('invoices')) }}</td>
            </tr>
        </tfoot>
    </table></div>
</div>
@endif

@push('scripts')
<script>
function toggleVatDates(select) {
    var fromW = document.getElementById('vat-from-wrapper');
    var toW = document.getElementById('vat-to-wrapper');
    if (select.value === 'custom') { fromW.style.display = 'block'; toW.style.display = 'block'; }
    else { fromW.style.display = 'none'; toW.style.display = 'none'; }
}
</script>
@endpush
@endsection
