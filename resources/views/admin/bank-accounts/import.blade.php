@extends('layouts.admin')
@section('title', 'Import Bank Statement')

@section('content')
<div class="page-header">
    <div class="breadcrumb"><a href="{{ route('admin.bank-accounts.index') }}">Bank Accounts</a> / {{ $bankAccount->account_name }} / Import</div>
    <h1>Import Statement: {{ $bankAccount->account_name }}</h1>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
    <div>
        <div class="card mb-2">
            <div class="card-header">Upload CSV File</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.bank-accounts.import.preview', $bankAccount) }}" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div id="dropZone" style="border:2px dashed rgba(255,255,255,0.15); border-radius:var(--radius, 8px); padding:2rem; text-align:center; cursor:pointer; transition:all 0.2s; margin-bottom:1rem;">
                        <div style="font-size:2rem; opacity:0.4; margin-bottom:0.5rem;">&#128196;</div>
                        <p style="margin-bottom:0.25rem;"><strong>Drag & drop your CSV file here</strong></p>
                        <p class="text-muted" style="font-size:0.85rem; margin-bottom:0.5rem;">or click to browse</p>
                        <input type="file" name="csv_file" id="csvFile" class="form-control" accept=".csv,.txt" required style="display:none;">
                        <span id="fileName" class="text-muted" style="font-size:0.85rem;"></span>
                        @error('csv_file') <small style="color:var(--danger); display:block;">{{ $message }}</small> @enderror
                    </div>
                    <small class="text-muted" style="display:block; margin-bottom:1rem;">Accepted: CSV or TXT up to 10MB. Download from {{ $bankAccount->bank_name }} internet banking.</small>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Preview Transactions</button>
                </form>
            </div>
        </div>

        @if($bankAccount->csv_column_map)
        <div class="card mb-2">
            <div class="card-body">
                <h6 class="font-semibold" style="margin-bottom:0.25rem;">Saved Column Mapping</h6>
                <small class="text-muted" style="display:block; margin-bottom:0.5rem;">Custom mapping from previous import.</small>
                <div style="display:flex; flex-wrap:wrap; gap:0.35rem;">
                    @foreach($bankAccount->csv_column_map as $field => $colIndex)
                        <span class="badge badge-secondary">{{ ucfirst($field) }}: Col {{ $colIndex }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if($bankAccount->importBatches->isNotEmpty())
        <div class="card">
            <div class="card-header">Import History</div>
            <div class="table-responsive"><table><thead><tr>
                <th>Date</th><th>File</th><th class="text-right">Imported</th><th class="text-right">Skipped</th><th class="text-right">Matched</th><th>Period</th><th>By</th>
            </tr></thead><tbody>
                @foreach($bankAccount->importBatches->sortByDesc('created_at')->take(10) as $batch)
                <tr>
                    <td style="white-space:nowrap;">{{ $batch->created_at->format('d M Y H:i') }}</td>
                    <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $batch->file_name }}">{{ $batch->file_name }}</td>
                    <td class="text-right">{{ $batch->rows_imported }}</td>
                    <td class="text-right">{{ $batch->rows_skipped }}</td>
                    <td class="text-right">{{ $batch->rows_auto_matched ?? '-' }}</td>
                    <td style="white-space:nowrap;">@if($batch->period_from && $batch->period_to){{ \Carbon\Carbon::parse($batch->period_from)->format('d M') }} – {{ \Carbon\Carbon::parse($batch->period_to)->format('d M Y') }}@else - @endif</td>
                    <td><small>{{ $batch->imported_by ?? '-' }}</small></td>
                </tr>
                @endforeach
            </tbody></table></div>
        </div>
        @endif
    </div>

    <div>
        <div class="card mb-2">
            <div class="card-header">Supported Banks</div>
            <div class="card-body" style="font-size:0.85rem;">
                <p class="text-muted" style="margin-bottom:0.5rem;">Auto-detects CSV from:</p>
                <ul style="margin:0; padding-left:1.25rem;">
                    <li><strong>FNB</strong> — Date, Description, Amount, Balance</li>
                    <li><strong>ABSA</strong> — Date, Ref, Description, Amount, Balance</li>
                    <li><strong>Nedbank</strong> — Date, Description, Amount, Balance</li>
                    <li><strong>Standard Bank</strong> — Date, Description, Amount, Balance</li>
                    <li><strong>Capitec</strong> — Date, Desc, Debit, Credit, Balance</li>
                </ul>
            </div>
        </div>
        <div class="card mb-2">
            <div class="card-header">Account Info</div>
            <div class="card-body" style="font-size:0.85rem;">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;"><span class="text-muted">Bank:</span><strong>{{ $bankAccount->bank_name }}</strong></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;"><span class="text-muted">Account:</span><strong>{{ $bankAccount->display_account_number }}</strong></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;"><span class="text-muted">Type:</span><strong>{{ ucfirst($bankAccount->account_type) }}</strong></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;"><span class="text-muted">Balance:</span><strong>R{{ number_format($bankAccount->current_balance, 2) }}</strong></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;"><span class="text-muted">Transactions:</span><strong>{{ number_format($bankAccount->transactions()->count()) }}</strong></div>
                <div style="display:flex; justify-content:space-between;"><span class="text-muted">Last import:</span><strong>{{ $bankAccount->last_import_date ?? 'Never' }}</strong></div>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="font-size:0.85rem;">
                <h6 class="font-semibold" style="margin-bottom:0.5rem;">Tips</h6>
                <ul style="margin:0; padding-left:1.25rem;">
                    <li>Duplicates auto-detected via hash and skipped.</li>
                    <li>Auto-matching runs after import.</li>
                    <li>Set up <a href="{{ route('admin.bank-reconciliation.rules.index') }}">recon rules</a> for recurring items.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var dz = document.getElementById('dropZone'), fi = document.getElementById('csvFile'), fn = document.getElementById('fileName'), sb = document.getElementById('submitBtn');
    dz.addEventListener('click', function() { fi.click(); });
    dz.addEventListener('dragover', function(e) { e.preventDefault(); dz.style.borderColor = 'var(--primary, #3498db)'; dz.style.background = 'rgba(52,152,219,0.05)'; });
    dz.addEventListener('dragleave', function() { dz.style.borderColor = 'rgba(255,255,255,0.15)'; dz.style.background = ''; });
    dz.addEventListener('drop', function(e) { e.preventDefault(); dz.style.borderColor = 'rgba(255,255,255,0.15)'; dz.style.background = ''; if (e.dataTransfer.files.length > 0) { fi.files = e.dataTransfer.files; upd(); } });
    fi.addEventListener('change', upd);
    function upd() { if (fi.files.length > 0) { var f = fi.files[0]; fn.innerHTML = '<strong>' + f.name + '</strong> (' + (f.size / 1048576).toFixed(2) + ' MB)'; sb.disabled = false; dz.style.borderColor = 'var(--success, #27ae60)'; } else { fn.textContent = ''; sb.disabled = true; } }
})();
</script>
@endpush
@endsection
