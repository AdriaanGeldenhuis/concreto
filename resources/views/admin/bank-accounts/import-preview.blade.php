@extends('layouts.admin')
@section('title', 'Preview Import')

@section('content')
<div class="page-header">
    <div class="breadcrumb"><a href="{{ route('admin.bank-accounts.index') }}">Bank Accounts</a> / <a href="{{ route('admin.bank-accounts.import.show', $bankAccount) }}">{{ $bankAccount->account_name }}</a> / Preview</div>
    <h1>Preview Import: {{ $bankAccount->account_name }}</h1>
</div>

@if(!empty($needsMapping))
    <div class="card">
        <div class="card-header"><span>Column Mapping Required</span><span class="badge badge-warning">Manual</span></div>
        <div class="card-body">
            <p class="text-muted" style="margin-bottom:0.75rem;">CSV format not auto-detected. Map columns below.</p>
            <div style="background:rgba(255,255,255,0.03); border-radius:var(--radius-sm, 6px); padding:0.75rem; margin-bottom:1rem; overflow-x:auto;">
                <small class="text-muted" style="display:block; margin-bottom:0.35rem;">Detected Headers:</small>
                <div style="display:flex; flex-wrap:wrap; gap:0.35rem;">
                    @foreach($headers as $i => $h)<span class="badge badge-secondary">{{ $i }}: {{ $h }}</span>@endforeach
                </div>
            </div>
            <form method="POST" action="{{ route('admin.bank-accounts.import.save-mapping', $bankAccount) }}">
                @csrf
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Date *</label><select name="col_date" class="form-control" required>@foreach($headers as $i => $h)<option value="{{ $i }}">{{ $i }}: {{ $h }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label">Description *</label><select name="col_description" class="form-control" required>@foreach($headers as $i => $h)<option value="{{ $i }}" {{ $i === 1 ? 'selected' : '' }}>{{ $i }}: {{ $h }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label">Amount</label><select name="col_amount" class="form-control"><option value="">N/A</option>@foreach($headers as $i => $h)<option value="{{ $i }}">{{ $i }}: {{ $h }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label">Balance</label><select name="col_balance" class="form-control"><option value="">N/A</option>@foreach($headers as $i => $h)<option value="{{ $i }}">{{ $i }}: {{ $h }}</option>@endforeach</select></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Debit</label><select name="col_debit" class="form-control"><option value="">N/A</option>@foreach($headers as $i => $h)<option value="{{ $i }}">{{ $i }}: {{ $h }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label">Credit</label><select name="col_credit" class="form-control"><option value="">N/A</option>@foreach($headers as $i => $h)<option value="{{ $i }}">{{ $i }}: {{ $h }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label">Reference</label><select name="col_reference" class="form-control"><option value="">N/A</option>@foreach($headers as $i => $h)<option value="{{ $i }}">{{ $i }}: {{ $h }}</option>@endforeach</select></div>
                    <div class="form-group" style="display:flex; align-items:flex-end;"><button type="submit" class="btn btn-primary btn-sm">Save & Re-upload</button></div>
                </div>
                <div style="background:rgba(52,152,219,0.05); border-radius:var(--radius-sm, 6px); padding:0.75rem; margin-top:0.75rem; font-size:0.85rem;">
                    <strong>Note:</strong> Provide either Amount or Debit/Credit columns. Mapping is saved for future imports.
                </div>
            </form>
        </div>
    </div>
@else
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Rows</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $result['total'] }}</h3></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">New</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $result['new_rows'] }}</h3></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Duplicates</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--warning, #e67e22);">{{ $result['duplicates'] }}</h3></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Format</h6><h3 class="mb-0" style="margin-top:0.25rem; font-size:1rem;">{{ $result['format_name'] ?? 'Custom' }}</h3></div></div>
    </div>

    @php
        $dates = collect($result['rows'])->pluck('date')->filter();
        $amounts = collect($result['rows'])->where('is_duplicate', false)->pluck('amount');
        $totalCredits = $amounts->filter(fn($a) => $a >= 0)->sum();
        $totalDebits = $amounts->filter(fn($a) => $a < 0)->sum();
    @endphp
    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1.25rem;">
        <div class="card"><div class="card-body" style="padding:0.75rem;"><small class="text-muted">Date Range</small><br><strong>{{ $dates->min() ?? '-' }} to {{ $dates->max() ?? '-' }}</strong></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem;"><small class="text-muted">Credits (Money In)</small><br><strong style="color:var(--success, #27ae60);">R{{ number_format($totalCredits, 2) }}</strong></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem;"><small class="text-muted">Debits (Money Out)</small><br><strong style="color:var(--danger, #e74c3c);">R{{ number_format(abs($totalDebits), 2) }}</strong></div></div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Transaction Preview</span>
            <form method="POST" action="{{ route('admin.bank-accounts.import.confirm', $bankAccount) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="temp_path" value="{{ $tempPath }}">
                <input type="hidden" name="original_name" value="{{ $originalName }}">
                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Import {{ $result['new_rows'] }} new transactions?');">Confirm Import ({{ $result['new_rows'] }} new)</button>
            </form>
        </div>
        <div class="table-responsive"><table><thead><tr>
            <th style="width:40px;">#</th><th>Date</th><th>Description</th><th>Reference</th><th class="text-right">Amount</th><th class="text-right">Balance</th><th>Status</th>
        </tr></thead><tbody>
            @foreach($result['rows'] as $i => $row)
            <tr style="{{ $row['is_duplicate'] ? 'opacity:0.5;' : '' }}">
                <td class="text-muted">{{ $i + 1 }}</td>
                <td style="white-space:nowrap;">{{ $row['date'] }}</td>
                <td style="max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $row['description'] }}">{{ $row['description'] }}</td>
                <td><small>{{ $row['reference'] ?? '-' }}</small></td>
                <td class="text-right font-semibold" style="color:{{ $row['amount'] >= 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};">{{ $row['amount'] >= 0 ? '+' : '-' }}R{{ number_format(abs($row['amount']), 2) }}</td>
                <td class="text-right">{{ $row['balance'] !== null ? 'R'.number_format($row['balance'], 2) : '-' }}</td>
                <td>@if($row['is_duplicate'])<span class="badge badge-warning">Duplicate</span>@else<span class="badge badge-success">New</span>@endif</td>
            </tr>
            @endforeach
        </tbody></table></div>
        <div style="padding:0.75rem; font-size:0.85rem;" class="text-muted">{{ count($result['rows']) }} rows. {{ $result['new_rows'] }} new, {{ $result['duplicates'] }} duplicates. Auto-matching runs after import.</div>
    </div>
@endif
@endsection
