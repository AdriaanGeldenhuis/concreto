@extends('layouts.admin')
@section('title', 'Import Bank Statement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Import Statement: {{ $bankAccount->account_name }}</h1>
        <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Upload CSV File</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.bank-accounts.import.preview', $bankAccount) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">CSV File *</label>
                            <input type="file" name="csv_file" class="form-control @error('csv_file') is-invalid @enderror"
                                   accept=".csv,.txt" required>
                            @error('csv_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Accepted: CSV or TXT files up to 10MB. Download your statement from {{ $bankAccount->bank_name }} internet banking.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Preview Transactions</button>
                    </form>
                </div>
            </div>

            @if($bankAccount->csv_column_map)
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="mb-2">Saved Column Mapping</h6>
                        <small class="text-muted">
                            This account has a custom column mapping saved from a previous import.
                            The system will use this mapping for CSV files it can't auto-detect.
                        </small>
                        <pre class="mt-2 mb-0" style="font-size: 0.8rem;">{{ json_encode($bankAccount->csv_column_map, JSON_PRETTY_PRINT) }}</pre>
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
                        <span class="text-muted">Balance:</span>
                        <strong>R {{ number_format($bankAccount->current_balance, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last import:</span>
                        <strong>{{ $bankAccount->last_import_date ?? 'Never' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
