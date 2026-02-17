@extends('layouts.admin')
@section('title', 'Preview Import')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Preview Import: {{ $bankAccount->account_name }}</h1>
        <a href="{{ route('admin.bank-accounts.import.show', $bankAccount) }}" class="btn btn-secondary">Back</a>
    </div>

    @if(!empty($needsMapping))
        {{-- Column Mapping UI --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Column Mapping Required</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">The CSV format was not auto-detected. Please map the columns below:</p>

                <div class="mb-3 p-3" style="background: #f8f9fc; border-radius: 4px; overflow-x: auto;">
                    <small class="text-muted d-block mb-1">CSV Headers:</small>
                    <code>
                        @foreach($headers as $i => $h)
                            <span class="badge bg-secondary me-1">{{ $i }}: {{ $h }}</span>
                        @endforeach
                    </code>
                </div>

                <form method="POST" action="{{ route('admin.bank-accounts.import.save-mapping', $bankAccount) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date Column *</label>
                            <select name="col_date" class="form-select" required>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}">{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Description Column *</label>
                            <select name="col_description" class="form-select" required>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}" {{ $i === 1 ? 'selected' : '' }}>{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Amount Column</label>
                            <select name="col_amount" class="form-select">
                                <option value="">N/A (use debit/credit)</option>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}">{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Balance Column</label>
                            <select name="col_balance" class="form-select">
                                <option value="">Not available</option>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}">{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Debit Column</label>
                            <select name="col_debit" class="form-select">
                                <option value="">N/A</option>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}">{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Credit Column</label>
                            <select name="col_credit" class="form-select">
                                <option value="">N/A</option>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}">{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference Column</label>
                            <select name="col_reference" class="form-select">
                                <option value="">Not available</option>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}">{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Save Mapping & Re-upload</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @else
        {{-- Preview parsed transactions --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Total Rows</h6>
                        <h3 class="mb-0">{{ $result['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">New Transactions</h6>
                        <h3 class="mb-0 text-success">{{ $result['new_rows'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Duplicates (skip)</h6>
                        <h3 class="mb-0 text-warning">{{ $result['duplicates'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Detected Format</h6>
                        <h3 class="mb-0">{{ $result['format_name'] ?? 'Unknown' }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Transaction Preview</h5>
                <form method="POST" action="{{ route('admin.bank-accounts.import.confirm', $bankAccount) }}">
                    @csrf
                    <input type="hidden" name="temp_path" value="{{ $tempPath }}">
                    <input type="hidden" name="original_name" value="{{ $originalName }}">
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Import {{ $result['new_rows'] }} new transactions?');">
                        Confirm Import ({{ $result['new_rows'] }} rows)
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.875rem;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Reference</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Balance</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($result['rows'] as $i => $row)
                                <tr class="{{ $row['is_duplicate'] ? 'opacity-50' : '' }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row['date'] }}</td>
                                    <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $row['description'] }}
                                    </td>
                                    <td><small>{{ $row['reference'] ?? '-' }}</small></td>
                                    <td class="text-end fw-bold {{ $row['amount'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $row['amount'] >= 0 ? '' : '-' }}R {{ number_format(abs($row['amount']), 2) }}
                                    </td>
                                    <td class="text-end">{{ $row['balance'] !== null ? 'R ' . number_format($row['balance'], 2) : '-' }}</td>
                                    <td class="text-center">
                                        @if($row['is_duplicate'])
                                            <span class="badge bg-warning">Duplicate</span>
                                        @else
                                            <span class="badge bg-success">New</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
