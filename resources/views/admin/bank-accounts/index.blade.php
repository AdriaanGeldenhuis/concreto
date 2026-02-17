@extends('layouts.admin')
@section('title', 'Bank Accounts')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Bank Accounts</h1>
            <small class="text-muted">Manage bank accounts, import statements, and track balances.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.bank-reconciliation.index') }}" class="btn btn-secondary">Reconciliation</a>
            <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary">+ Add Bank Account</a>
        </div>
    </div>

    @if($accounts->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <div style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;">&#127974;</div>
                <h5 class="text-muted">No bank accounts configured</h5>
                <p class="text-muted mb-3">Add a bank account to start importing statements and reconciling transactions.</p>
                <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary">Add Your First Bank Account</a>
            </div>
        </div>
    @else
        {{-- Summary bar --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">Total Accounts</small>
                        <h5 class="mb-0">{{ $accounts->count() }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">Active Accounts</small>
                        <h5 class="mb-0">{{ $accounts->where('is_active', true)->count() }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">Total Balance</small>
                        <h5 class="mb-0">R {{ number_format($accounts->where('is_active', true)->sum('current_balance'), 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block">Total Transactions</small>
                        <h5 class="mb-0">{{ number_format($accounts->sum('transactions_count')) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($accounts as $account)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card {{ !$account->is_active ? 'opacity-50' : '' }}" style="{{ $account->is_primary ? 'border-left: 4px solid var(--primary, #4e73df);' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="mb-1">{{ $account->account_name }}</h5>
                                    <small class="text-muted">{{ $account->bank_name }} &middot; {{ ucfirst($account->account_type) }} &middot; {{ $account->display_account_number }}</small>
                                </div>
                                <div class="d-flex gap-1">
                                    @if($account->is_primary)
                                        <span class="badge bg-primary">Primary</span>
                                    @endif
                                    @if(!$account->is_active)
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Current Balance</small>
                                <h4 class="mb-0">R {{ number_format($account->current_balance, 2) }}</h4>
                            </div>

                            <div class="d-flex justify-content-between text-muted mb-2" style="font-size: 0.85rem;">
                                <span>{{ number_format($account->transactions_count ?? 0) }} transactions</span>
                                <span>{{ $account->import_batches_count ?? 0 }} imports</span>
                            </div>
                            <div class="text-muted mb-3" style="font-size: 0.85rem;">
                                Last import: {{ $account->last_import_date ?? 'Never' }}
                            </div>

                            @if($account->branch_code)
                                <div class="text-muted mb-3" style="font-size: 0.8rem;">
                                    Branch: {{ $account->branch_code }}
                                </div>
                            @endif

                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.bank-accounts.import.show', $account) }}" class="btn btn-primary btn-sm flex-fill">Import CSV</a>
                                <a href="{{ route('admin.bank-reconciliation.index', ['account' => $account->id]) }}" class="btn btn-outline-primary btn-sm" title="View reconciliation">Reconcile</a>
                                <a href="{{ route('admin.bank-accounts.edit', $account) }}" class="btn btn-secondary btn-sm" title="Edit account">Edit</a>
                                <form method="POST" action="{{ route('admin.bank-accounts.destroy', $account) }}" onsubmit="return confirm('Delete this bank account and all its transactions? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete account">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
