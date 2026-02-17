@extends('layouts.admin')
@section('title', 'Preview Import')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Preview Import: {{ $bankAccount->account_name }}</h1>
            <small class="text-muted">Review the parsed transactions before confirming the import.</small>
        </div>
        <a href="{{ route('admin.bank-accounts.import.show', $bankAccount) }}" class="btn btn-secondary">Back to Upload</a>
    </div>

    @if(!empty($needsMapping))
        {{-- Column Mapping UI --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-1">Column Mapping Required</h5>
                <small class="text-muted">The CSV format could not be auto-detected. Please map the columns below.</small>
            </div>
            <div class="card-body">
                <div class="mb-3 p-3" style="background: #f8f9fc; border-radius: 4px; overflow-x: auto;">
                    <small class="text-muted d-block mb-2">Detected CSV Headers:</small>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($headers as $i => $h)
                            <span class="badge bg-secondary" style="font-size: 0.8rem;">{{ $i }}: {{ $h }}</span>
                        @endforeach
                    </div>
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
                            <small class="text-muted">Transaction date</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Description Column *</label>
                            <select name="col_description" class="form-select" required>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}" {{ $i === 1 ? 'selected' : '' }}>{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Transaction description</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Amount Column</label>
                            <select name="col_amount" class="form-select">
                                <option value="">N/A (use debit/credit)</option>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}">{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Single amount column (+ / -)</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Balance Column</label>
                            <select name="col_balance" class="form-select">
                                <option value="">Not available</option>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}">{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Running balance</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Debit Column</label>
                            <select name="col_debit" class="form-select">
                                <option value="">N/A</option>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}">{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Separate debit column</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Credit Column</label>
                            <select name="col_credit" class="form-select">
                                <option value="">N/A</option>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}">{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Separate credit column</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference Column</label>
                            <select name="col_reference" class="form-select">
                                <option value="">Not available</option>
                                @foreach($headers as $i => $h)
                                    <option value="{{ $i }}">{{ $i }}: {{ $h }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Transaction reference</small>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Save Mapping & Re-upload</button>
                        </div>
                    </div>

                    <div class="mt-3 p-3" style="background: rgba(78, 115, 223, 0.05); border-radius: 4px; font-size: 0.85rem;">
                        <strong>Note:</strong> You must provide either a single Amount column or separate Debit/Credit columns.
                        The mapping will be saved to this account for future imports.
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
                        <h3 class="mb-0">{{ $result['format_name'] ?? 'Custom Mapping' }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Date range & totals summary --}}
        @php
            $dates = collect($result['rows'])->pluck('date')->filter();
            $amounts = collect($result['rows'])->where('is_duplicate', false)->pluck('amount');
            $totalCredits = $amounts->filter(fn($a) => $a >= 0)->sum();
            $totalDebits = $amounts->filter(fn($a) => $a < 0)->sum();
        @endphp
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">Date Range</small>
                        <strong>{{ $dates->min() ?? '-' }} to {{ $dates->max() ?? '-' }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">New Credits (Money In)</small>
                        <strong class="text-success">R {{ number_format($totalCredits, 2) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">New Debits (Money Out)</small>
                        <strong class="text-danger">R {{ number_format(abs($totalDebits), 2) }}</strong>
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
                            onclick="return confirm('Import {{ $result['new_rows'] }} new transactions into {{ $bankAccount->account_name }}?');">
                        Confirm Import ({{ $result['new_rows'] }} new rows)
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.875rem;">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
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
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td class="text-nowrap">{{ $row['date'] }}</td>
                                    <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $row['description'] }}">
                                        {{ $row['description'] }}
                                    </td>
                                    <td><small>{{ $row['reference'] ?? '-' }}</small></td>
                                    <td class="text-end fw-bold {{ $row['amount'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $row['amount'] >= 0 ? '+' : '-' }}R {{ number_format(abs($row['amount']), 2) }}
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
            <div class="card-footer text-muted" style="font-size: 0.85rem;">
                Showing {{ count($result['rows']) }} rows. {{ $result['new_rows'] }} new, {{ $result['duplicates'] }} duplicates.
                After import, auto-matching will run against existing payments.
            </div>
        </div>
    @endif
</div>
@endsection
