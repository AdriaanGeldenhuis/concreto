@extends('layouts.admin')
@section('title', 'Bank Accounts')

@section('content')
<div class="page-header">
    <h1>Bank Accounts</h1>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.bank-reconciliation.index') }}" class="btn btn-outline btn-sm">Reconciliation</a>
        <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary btn-sm">+ Add Bank Account</a>
    </div>
</div>

@if($accounts->isEmpty())
    <div class="card"><div class="card-body">
        <div class="empty-state">
            <div class="icon">&#127974;</div>
            <h3>No bank accounts configured</h3>
            <p>Add a bank account to start importing statements and reconciling transactions.</p>
            <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary btn-sm">Add Your First Bank Account</a>
        </div>
    </div></div>
@else
    {{-- Summary --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Accounts</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $accounts->count() }}</h3></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Active</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $accounts->where('is_active', true)->count() }}</h3></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total Balance</h6><h3 class="mb-0" style="margin-top:0.25rem;">R{{ number_format($accounts->where('is_active', true)->sum('current_balance'), 2) }}</h3></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Transactions</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ number_format($accounts->sum('transactions_count')) }}</h3></div></div>
    </div>

    {{-- Accounts Table --}}
    <div class="card">
        <div class="card-header"><span>All Accounts</span><span class="badge badge-secondary">{{ $accounts->count() }}</span></div>
        <div class="table-responsive"><table><thead><tr>
            <th>Account</th>
            <th>Bank</th>
            <th>Type</th>
            <th class="text-right">Balance</th>
            <th class="text-right">Transactions</th>
            <th>Last Import</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        </tr></thead><tbody>
            @foreach($accounts as $account)
            <tr style="{{ !$account->is_active ? 'opacity:0.5;' : '' }}">
                <td>
                    <div class="font-semibold">{{ $account->account_name }}</div>
                    <small class="text-muted">{{ $account->display_account_number }}</small>
                </td>
                <td>{{ $account->bank_name }}</td>
                <td>{{ ucfirst($account->account_type) }}</td>
                <td class="text-right font-semibold">R{{ number_format($account->current_balance, 2) }}</td>
                <td class="text-right">{{ number_format($account->transactions_count ?? 0) }}</td>
                <td><small>{{ $account->last_import_date ?? 'Never' }}</small></td>
                <td>
                    @if($account->is_primary)<span class="badge badge-primary">Primary</span>@endif
                    <span class="badge badge-{{ $account->is_active ? 'success' : 'secondary' }}">{{ $account->is_active ? 'Active' : 'Inactive' }}</span>
                </td>
                <td class="text-right">
                    <div style="display:flex; gap:0.25rem; justify-content:flex-end;">
                        <a href="{{ route('admin.bank-accounts.import.show', $account) }}" class="btn btn-sm btn-primary">Import</a>
                        <a href="{{ route('admin.bank-reconciliation.index', ['account' => $account->id]) }}" class="btn btn-sm btn-outline">Reconcile</a>
                        <a href="{{ route('admin.bank-accounts.edit', $account) }}" class="btn btn-sm btn-outline">Edit</a>
                        <form method="POST" action="{{ route('admin.bank-accounts.destroy', $account) }}" style="display:inline;" onsubmit="return confirm('Delete this bank account and all transactions?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Del</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody></table></div>
    </div>
@endif
@endsection
