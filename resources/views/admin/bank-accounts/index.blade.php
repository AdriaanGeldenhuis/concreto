@extends('layouts.admin')
@section('title', 'Bank Accounts')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Bank Accounts</h1>
        <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary">+ Add Bank Account</a>
    </div>

    @if($accounts->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <h5 class="text-muted">No bank accounts configured</h5>
                <p class="text-muted">Add a bank account to start importing statements and reconciling transactions.</p>
                <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary">Add Your First Bank Account</a>
            </div>
        </div>
    @else
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
                                <div>
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

                            <div class="d-flex justify-content-between text-muted mb-3" style="font-size: 0.85rem;">
                                <span>{{ $account->transactions_count ?? 0 }} transactions</span>
                                <span>Last import: {{ $account->last_import_date ?? 'Never' }}</span>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.bank-accounts.import.show', $account) }}" class="btn btn-primary btn-sm flex-fill">Import CSV</a>
                                <a href="{{ route('admin.bank-accounts.edit', $account) }}" class="btn btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.bank-accounts.destroy', $account) }}" onsubmit="return confirm('Delete this bank account and all its transactions?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Del</button>
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
