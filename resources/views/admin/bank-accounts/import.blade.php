@extends('layouts.admin')
@section('title', 'Import Bank Statement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Import Statement: {{ $bankAccount->account_name }}</h1>
            <small class="text-muted">Upload a CSV bank statement to import transactions for reconciliation.</small>
        </div>
        <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary">Back to Accounts</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Upload CSV File</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.bank-accounts.import.preview', $bankAccount) }}" enctype="multipart/form-data" id="importForm">
                        @csrf

                        {{-- Drag and drop area --}}
                        <div id="dropZone" class="mb-3 p-4 text-center" style="border: 2px dashed rgba(255,255,255,0.2); border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                            <div style="font-size: 2rem; opacity: 0.4; margin-bottom: 0.5rem;">&#128196;</div>
                            <p class="mb-1"><strong>Drag & drop your CSV file here</strong></p>
                            <p class="text-muted mb-2" style="font-size: 0.85rem;">or click to browse</p>
                            <input type="file" name="csv_file" id="csvFile" class="form-control @error('csv_file') is-invalid @enderror"
                                   accept=".csv,.txt" required style="display: none;">
                            <span id="fileName" class="text-muted" style="font-size: 0.85rem;"></span>
                            @error('csv_file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <small class="text-muted d-block mb-3">Accepted formats: CSV or TXT files up to 10MB. Download your statement from {{ $bankAccount->bank_name }} internet banking.</small>

                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Preview Transactions</button>
                    </form>
                </div>
            </div>

            @if($bankAccount->csv_column_map)
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Saved Column Mapping</h6>
                                <small class="text-muted">
                                    This account has a custom column mapping saved from a previous import.
                                    The system will use this mapping for CSV files it cannot auto-detect.
                                </small>
                            </div>
                        </div>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            @foreach($bankAccount->csv_column_map as $field => $colIndex)
                                <span class="badge bg-secondary">{{ ucfirst($field) }}: Column {{ $colIndex }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Import History --}}
            @if($bankAccount->importBatches->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header"><h5 class="mb-0">Import History</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size: 0.875rem;">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>File</th>
                                        <th class="text-center">Imported</th>
                                        <th class="text-center">Skipped</th>
                                        <th class="text-center">Auto-Matched</th>
                                        <th>Period</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bankAccount->importBatches->sortByDesc('created_at')->take(10) as $batch)
                                        <tr>
                                            <td class="text-nowrap">{{ $batch->created_at->format('d M Y H:i') }}</td>
                                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $batch->file_name }}">
                                                {{ $batch->file_name }}
                                            </td>
                                            <td class="text-center">{{ $batch->rows_imported }}</td>
                                            <td class="text-center">{{ $batch->rows_skipped }}</td>
                                            <td class="text-center">{{ $batch->rows_auto_matched ?? '-' }}</td>
                                            <td class="text-nowrap">
                                                @if($batch->period_from && $batch->period_to)
                                                    {{ \Carbon\Carbon::parse($batch->period_from)->format('d M') }} - {{ \Carbon\Carbon::parse($batch->period_to)->format('d M Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $batch->imported_by ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Supported Banks</h5></div>
                <div class="card-body">
                    <p class="text-muted" style="font-size: 0.9rem;">The system auto-detects CSV formats from these banks:</p>
                    <ul class="mb-3" style="font-size: 0.9rem;">
                        <li><strong>FNB</strong> — Date, Description, Amount, Balance</li>
                        <li><strong>ABSA</strong> — Date, Reference, Description, Amount, Balance</li>
                        <li><strong>Nedbank</strong> — Date, Transaction Description, Amount, Balance</li>
                        <li><strong>Standard Bank</strong> — Date, Description, Amount, Balance</li>
                        <li><strong>Capitec</strong> — Date, Transaction, Description, Debit, Credit, Balance</li>
                    </ul>
                    <p class="text-muted" style="font-size: 0.85rem;">
                        For other formats, you'll be asked to map columns on first import.
                        The mapping will be saved for future imports.
                    </p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><h5 class="mb-0">Account Info</h5></div>
                <div class="card-body" style="font-size: 0.9rem;">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Bank:</span>
                        <strong>{{ $bankAccount->bank_name }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Account:</span>
                        <strong>{{ $bankAccount->display_account_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Type:</span>
                        <strong>{{ ucfirst($bankAccount->account_type) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Balance:</span>
                        <strong>R {{ number_format($bankAccount->current_balance, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Transactions:</span>
                        <strong>{{ number_format($bankAccount->transactions()->count()) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last import:</span>
                        <strong>{{ $bankAccount->last_import_date ?? 'Never' }}</strong>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body" style="font-size: 0.85rem;">
                    <h6 class="mb-2">Tips</h6>
                    <ul class="mb-0 ps-3">
                        <li>Duplicate transactions are detected automatically via hash and will be skipped.</li>
                        <li>After importing, the system runs auto-matching to find corresponding payments.</li>
                        <li>You can set up <a href="{{ route('admin.bank-reconciliation.rules.index') }}">reconciliation rules</a> to auto-categorise recurring items.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('csvFile');
    const fileName = document.getElementById('fileName');
    const submitBtn = document.getElementById('submitBtn');

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = 'var(--primary, #4e73df)';
        dropZone.style.background = 'rgba(78, 115, 223, 0.05)';
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = 'rgba(255,255,255,0.2)';
        dropZone.style.background = '';
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = 'rgba(255,255,255,0.2)';
        dropZone.style.background = '';

        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            updateFileName();
        }
    });

    fileInput.addEventListener('change', updateFileName);

    function updateFileName() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            fileName.innerHTML = '<strong>' + file.name + '</strong> (' + sizeMB + ' MB)';
            submitBtn.disabled = false;
            dropZone.style.borderColor = 'var(--success, #1cc88a)';
        } else {
            fileName.textContent = '';
            submitBtn.disabled = true;
        }
    }
})();
</script>
@endpush
@endsection
