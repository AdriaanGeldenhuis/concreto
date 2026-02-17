@extends('layouts.admin')
@section('title', 'Bank Reconciliation')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Bank Reconciliation</h1>
            <small class="text-muted">Match bank transactions to system payments. Review auto-matched items and resolve unmatched ones.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.bank-reconciliation.rules.index') }}" class="btn btn-secondary">Rules</a>
            <form method="POST" action="{{ route('admin.bank-reconciliation.auto-match') }}" class="d-inline">
                @csrf
                @if($accountId)
                    <input type="hidden" name="account" value="{{ $accountId }}">
                @endif
                <button type="submit" class="btn btn-primary" onclick="return confirm('Run auto-matching on all unmatched transactions?');">
                    Run Auto-Match
                </button>
            </form>
            <a href="{{ route('admin.bank-reconciliation.statement', request()->only('account', 'from', 'to')) }}" class="btn btn-secondary">
                Statement
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Unmatched Credits</small>
                    <h5 class="mb-0 text-success">R {{ number_format($summary['unmatched_credits'], 2) }}</h5>
                    <small class="text-muted">{{ $summary['unmatched_count'] }} items</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Unmatched Debits</small>
                    <h5 class="mb-0 text-danger">R {{ number_format($summary['unmatched_debits'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Auto-Matched</small>
                    <h5 class="mb-0">{{ $summary['auto_matched_count'] }}</h5>
                    <small class="text-muted">awaiting review</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Fully Reconciled</small>
                    <h5 class="mb-0 text-success">{{ $summary['fully_reconciled_count'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Excluded</small>
                    <h5 class="mb-0 text-muted">{{ $summary['excluded_count'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card" style="border-left: 3px solid {{ ($summary['difference'] ?? 0) == 0 ? 'var(--success, #1cc88a)' : 'var(--danger, #e74a3b)' }};">
                <div class="card-body py-2">
                    <small class="text-muted d-block">Difference</small>
                    <h5 class="mb-0 {{ ($summary['difference'] ?? 0) == 0 ? 'text-success' : 'text-danger' }}">
                        {{ $summary['difference'] !== null ? 'R ' . number_format($summary['difference'], 2) : 'N/A' }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.bank-reconciliation.index') }}" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-md-2">
                    <label class="form-label mb-1" style="font-size: 0.85rem;">Account</label>
                    <select name="account" class="form-select form-select-sm">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>{{ $acc->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1" style="font-size: 0.85rem;">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1" style="font-size: 0.85rem;">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1" style="font-size: 0.85rem;">Search Description</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Filter by description...">
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.bank-reconciliation.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-3" style="border-bottom: 2px solid rgba(255,255,255,0.1);">
        @php
            $tabs = [
                'review' => 'To Review',
                'unmatched_income' => 'Unmatched Income',
                'unmatched_expenses' => 'Unmatched Expenses',
                'excluded' => 'Excluded',
                'reconciled' => 'Reconciled',
                'all' => 'All',
            ];
            $filterParams = array_filter(request()->only('account', 'from', 'to', 'search'));
        @endphp
        @foreach($tabs as $tabKey => $tabLabel)
            <a href="{{ route('admin.bank-reconciliation.index', array_merge($filterParams, ['tab' => $tabKey])) }}"
               class="d-inline-block px-3 py-2 text-decoration-none {{ $tab === $tabKey ? 'fw-bold' : 'text-muted' }}"
               style="{{ $tab === $tabKey ? 'border-bottom: 2px solid var(--primary, #4e73df); margin-bottom: -2px;' : '' }}">
                {{ $tabLabel }}
                @if($tab === $tabKey && $transactions->total() > 0)
                    <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ $transactions->total() }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <!-- Transaction Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 0.875rem;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Match</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr>
                                <td class="text-nowrap">{{ $tx->transaction_date->format('d M Y') }}</td>
                                <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                    title="{{ $tx->description }}">
                                    {{ $tx->description }}
                                </td>
                                <td><small>{{ $tx->reference ?? '-' }}</small></td>
                                <td class="text-end fw-bold {{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $tx->amount >= 0 ? '+' : '-' }}R {{ number_format(abs($tx->amount), 2) }}
                                </td>
                                <td>
                                    @if($tx->category)
                                        <span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $tx->category)) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'unmatched' => 'warning',
                                            'matched' => 'info',
                                            'manually_reconciled' => 'success',
                                            'excluded' => 'secondary',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$tx->reconciliation_status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $tx->reconciliation_status)) }}
                                    </span>
                                    @if($tx->reconciliationMatch?->confidence)
                                        <small class="text-muted">({{ number_format($tx->reconciliationMatch->confidence) }}%)</small>
                                    @endif
                                </td>
                                <td style="font-size: 0.8rem;">
                                    @if($tx->reconciliationMatch?->payment)
                                        @php $p = $tx->reconciliationMatch->payment; @endphp
                                        <a href="{{ $p->order ? route('admin.orders.show', $p->order) : '#' }}">
                                            {{ $p->order?->order_number ?? $p->reference }}
                                        </a>
                                        @if($p->order?->customer?->user)
                                            <br><small class="text-muted">{{ $p->order->customer->user->name }}</small>
                                        @endif
                                    @elseif($tx->reconciliationMatch?->invoice)
                                        {{ $tx->reconciliationMatch->invoice->invoice_no }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if($tx->reconciliation_status === 'unmatched')
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#matchModal{{ $tx->id }}">
                                            Match
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#excludeModal{{ $tx->id }}">
                                            Exclude
                                        </button>
                                    @elseif($tx->reconciliation_status === 'matched')
                                        <form method="POST" action="{{ route('admin.bank-reconciliation.match', $tx) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="payment_id" value="{{ $tx->reconciliationMatch?->payment_id }}">
                                            <input type="hidden" name="invoice_id" value="{{ $tx->reconciliationMatch?->invoice_id }}">
                                            <button type="submit" class="btn btn-success btn-sm" title="Confirm this auto-match">Confirm</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.bank-reconciliation.unmatch', $tx) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm" title="Undo this match">Undo</button>
                                        </form>
                                    @elseif($tx->reconciliation_status === 'excluded' || $tx->reconciliation_status === 'manually_reconciled')
                                        <form method="POST" action="{{ route('admin.bank-reconciliation.unmatch', $tx) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm" title="Revert to unmatched">Undo</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            {{-- Match Modal --}}
                            @if($tx->reconciliation_status === 'unmatched')
                            <div class="modal fade" id="matchModal{{ $tx->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Match Transaction</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            {{-- Transaction summary --}}
                                            <div class="mb-3 p-3" style="background: #f8f9fc; border-radius: 4px;">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <small class="text-muted d-block">Bank Transaction</small>
                                                        <strong>{{ $tx->transaction_date->format('d M Y') }}</strong> &mdash;
                                                        {{ $tx->description }}
                                                        @if($tx->reference)
                                                            <br><small class="text-muted">Ref: {{ $tx->reference }}</small>
                                                        @endif
                                                    </div>
                                                    <h4 class="mb-0 {{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                                        R {{ number_format(abs($tx->amount), 2) }}
                                                    </h4>
                                                </div>
                                            </div>

                                            {{-- Match to existing payment --}}
                                            <h6 class="mb-2">Match to Existing Payment</h6>
                                            <form method="POST" action="{{ route('admin.bank-reconciliation.match', $tx) }}" class="mb-4">
                                                @csrf
                                                <div class="row g-2">
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control form-control-sm" id="paymentSearch{{ $tx->id }}"
                                                               placeholder="Search by reference, order #, customer name..."
                                                               data-amount="{{ abs($tx->amount) }}" data-tx="{{ $tx->id }}">
                                                        <div id="paymentResults{{ $tx->id }}" class="mt-2" style="max-height: 200px; overflow-y: auto;"></div>
                                                        <input type="hidden" name="payment_id" id="paymentId{{ $tx->id }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes (optional)">
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm mt-2">Match to Payment</button>
                                            </form>

                                            @if($tx->type === 'credit')
                                            <hr>
                                            {{-- Create new payment --}}
                                            <h6 class="mb-2">Create New Payment</h6>
                                            <p class="text-muted" style="font-size: 0.85rem;">If no existing payment matches, create one from this bank transaction.</p>
                                            <form method="POST" action="{{ route('admin.bank-reconciliation.create-payment', $tx) }}">
                                                @csrf
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <label class="form-label mb-1" style="font-size: 0.8rem;">Payment Method</label>
                                                        <select name="provider" class="form-select form-select-sm" required>
                                                            <option value="eft">EFT</option>
                                                            <option value="cash">Cash</option>
                                                            <option value="card_manual">Card (Manual)</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label mb-1" style="font-size: 0.8rem;">Order ID</label>
                                                        <input type="text" name="order_id" class="form-control form-control-sm" placeholder="Optional">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label mb-1" style="font-size: 0.8rem;">Customer ID</label>
                                                        <input type="text" name="customer_id" class="form-control form-control-sm" placeholder="Optional">
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-success btn-sm mt-2">Create Payment (R {{ number_format(abs($tx->amount), 2) }})</button>
                                            </form>
                                            @endif

                                            <hr>
                                            {{-- Categorise & Exclude --}}
                                            <h6 class="mb-2">Exclude from Reconciliation</h6>
                                            <p class="text-muted" style="font-size: 0.85rem;">Use this for bank fees, salaries, transfers, and other non-order transactions.</p>
                                            <form method="POST" action="{{ route('admin.bank-reconciliation.exclude', $tx) }}">
                                                @csrf
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <select name="category" class="form-select form-select-sm" required>
                                                            <option value="bank_fee">Bank Fee</option>
                                                            <option value="salary">Salary</option>
                                                            <option value="supplier">Supplier Payment</option>
                                                            <option value="transfer">Own Account Transfer</option>
                                                            <option value="personal">Personal</option>
                                                            <option value="petty_cash">Petty Cash</option>
                                                            <option value="tax">Tax / SARS</option>
                                                            <option value="insurance">Insurance</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes (optional)">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <button type="submit" class="btn btn-secondary btn-sm w-100">Exclude</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Quick Exclude Modal --}}
                            <div class="modal fade" id="excludeModal{{ $tx->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.bank-reconciliation.exclude', $tx) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Exclude Transaction</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="p-3 mb-3" style="background: #f8f9fc; border-radius: 4px;">
                                                    <small class="text-muted d-block">{{ $tx->transaction_date->format('d M Y') }}</small>
                                                    <p class="mb-1">{{ $tx->description }}</p>
                                                    <strong class="{{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                                        R {{ number_format(abs($tx->amount), 2) }}
                                                    </strong>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Category *</label>
                                                    <select name="category" class="form-select" required>
                                                        <option value="bank_fee">Bank Fee</option>
                                                        <option value="salary">Salary</option>
                                                        <option value="supplier">Supplier Payment</option>
                                                        <option value="transfer">Own Account Transfer</option>
                                                        <option value="personal">Personal</option>
                                                        <option value="petty_cash">Petty Cash</option>
                                                        <option value="tax">Tax / SARS</option>
                                                        <option value="insurance">Insurance</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Exclude</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    @if($tab === 'review')
                                        No auto-matched transactions awaiting review. Run Auto-Match to detect new matches.
                                    @elseif($tab === 'unmatched_income')
                                        No unmatched income transactions. All credits have been matched or excluded.
                                    @elseif($tab === 'unmatched_expenses')
                                        No unmatched expense transactions.
                                    @elseif($tab === 'excluded')
                                        No excluded transactions.
                                    @elseif($tab === 'reconciled')
                                        No reconciled transactions yet.
                                    @else
                                        No transactions found for the selected filters.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($transactions->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $transactions->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
// Payment search for match modals
document.querySelectorAll('[id^="paymentSearch"]').forEach(input => {
    let timeout;
    input.addEventListener('input', function() {
        clearTimeout(timeout);
        const txId = this.dataset.tx;
        const amount = this.dataset.amount;
        const q = this.value;
        const resultsDiv = document.getElementById('paymentResults' + txId);

        if (q.length < 2) {
            resultsDiv.innerHTML = '';
            return;
        }

        timeout = setTimeout(() => {
            fetch(`{{ route('admin.bank-reconciliation.search-payments') }}?q=${encodeURIComponent(q)}&amount=${amount}`)
                .then(r => r.json())
                .then(payments => {
                    if (payments.length === 0) {
                        resultsDiv.innerHTML = '<small class="text-muted">No matching payments found. Try a different search term.</small>';
                        return;
                    }
                    resultsDiv.innerHTML = payments.map(p => {
                        const orderNum = p.order_number ? escapeHtml(p.order_number) : (p.reference ? escapeHtml(p.reference) : '-');
                        const customer = p.customer ? ' &mdash; ' + escapeHtml(p.customer) : '';
                        const invoice = p.invoice_no ? escapeHtml(p.invoice_no) : '';
                        return `
                            <div class="p-2 border rounded mb-1 d-flex justify-content-between align-items-center" style="cursor:pointer; font-size: 0.85rem;"
                                 onclick="document.getElementById('paymentId${txId}').value='${p.id}'; this.parentNode.querySelectorAll('.border').forEach(el => el.style.background=''); this.style.background='#d4edda';">
                                <div>
                                    <strong>${orderNum}</strong>${customer}
                                    <br><small class="text-muted">${escapeHtml(p.date)} &middot; ${invoice}</small>
                                </div>
                                <strong>R ${parseFloat(p.amount).toFixed(2)}</strong>
                            </div>
                        `;
                    }).join('');
                });
        }, 300);
    });
});

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection
