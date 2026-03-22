@extends('layouts.admin')
@section('title', 'AP Aging Report')

@section('content')
<div class="page-header">
    <h1>Accounts Payable Aging</h1>
    <a href="{{ route('admin.supplier-invoices.index') }}" class="btn btn-secondary btn-sm">Back to Invoices</a>
</div>

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Owed</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($totalOwed, 2) }}</h3>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Current</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($totalCurrent, 2) }}</h3>
    </div></div>
    <div class="card" style="border-left:4px solid var(--warning, #e67e22);"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">31-60 Days</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($totalOver30, 2) }}</h3>
    </div></div>
    <div class="card" style="border-left:4px solid var(--danger, #e74c3c);"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">61-90 Days</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($totalOver60, 2) }}</h3>
    </div></div>
    <div class="card" style="border-left:4px solid #8e44ad;"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">90+ Days</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($totalOver90, 2) }}</h3>
    </div></div>
</div>

{{-- Aging Table --}}
<div class="card">
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Vendor</th>
            <th class="text-right">Total Owed</th>
            <th class="text-right">Current</th>
            <th class="text-right">31-60 Days</th>
            <th class="text-right">61-90 Days</th>
            <th class="text-right">90+ Days</th>
            <th class="text-right">Invoices</th>
            <th>Oldest Due</th>
        </tr></thead>
        <tbody>
            @forelse($apData as $row)
                <tr>
                    <td class="font-semibold">{{ $row->vendor->name }}</td>
                    <td class="text-right font-semibold">R{{ number_format($row->total_owed, 2) }}</td>
                    <td class="text-right">R{{ number_format($row->current, 2) }}</td>
                    <td class="text-right" style="{{ $row->over30 > 0 ? 'color:var(--warning, #e67e22);' : '' }}">R{{ number_format($row->over30, 2) }}</td>
                    <td class="text-right" style="{{ $row->over60 > 0 ? 'color:var(--danger, #e74c3c);' : '' }}">R{{ number_format($row->over60, 2) }}</td>
                    <td class="text-right" style="{{ $row->over90 > 0 ? 'color:#8e44ad;' : '' }}">R{{ number_format($row->over90, 2) }}</td>
                    <td class="text-right">{{ $row->invoice_count }}</td>
                    <td>
                        @if($row->oldest_due)
                            {{ $row->oldest_due->format('d M Y') }}
                            @if($row->days_overdue > 0)
                                <br><small style="color:var(--danger, #e74c3c);">{{ $row->days_overdue }}d overdue</small>
                            @endif
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-muted text-center" style="padding:2rem;">No outstanding payables.</td></tr>
            @endforelse
        </tbody>
        @if($apData->count())
        <tfoot>
            <tr class="font-semibold" style="background:rgba(255,255,255,0.03);">
                <td>TOTAL</td>
                <td class="text-right">R{{ number_format($totalOwed, 2) }}</td>
                <td class="text-right">R{{ number_format($totalCurrent, 2) }}</td>
                <td class="text-right">R{{ number_format($totalOver30, 2) }}</td>
                <td class="text-right">R{{ number_format($totalOver60, 2) }}</td>
                <td class="text-right">R{{ number_format($totalOver90, 2) }}</td>
                <td class="text-right">{{ $apData->sum('invoice_count') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table></div>
</div>
@endsection
