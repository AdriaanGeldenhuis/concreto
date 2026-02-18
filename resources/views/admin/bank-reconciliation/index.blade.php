@extends('layouts.admin')
@section('title', 'Bank Reconciliation')

@section('content')
<div class="page-header">
    <div>
        <h1>Bank Reconciliation</h1>
        <small class="text-muted">Match bank transactions to system payments. Review auto-matched items and resolve unmatched ones.</small>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.bank-reconciliation.rules.index') }}" class="btn btn-outline btn-sm">Rules</a>
        <form method="POST" action="{{ route('admin.bank-reconciliation.auto-match') }}" style="display:inline;">
            @csrf
            @if($accountId)
                <input type="hidden" name="account" value="{{ $accountId }}">
            @endif
            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Run auto-matching on all unmatched transactions?');">Run Auto-Match</button>
        </form>
        <a href="{{ route('admin.bank-reconciliation.statement', request()->only('account', 'from', 'to')) }}" class="btn btn-outline btn-sm">Statement</a>
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Unmatched Credits</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">R{{ number_format($summary['unmatched_credits'], 2) }}</h3>
        <small class="text-muted">{{ $summary['unmatched_count'] }} items</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Unmatched Debits</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--danger, #e74c3c);">R{{ number_format($summary['unmatched_debits'], 2) }}</h3>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Auto-Matched</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">{{ $summary['auto_matched_count'] }}</h3>
        <small class="text-muted">awaiting review</small>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Fully Reconciled</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $summary['fully_reconciled_count'] }}</h3>
    </div></div>
    <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Excluded</h6>
        <h3 class="mb-0" style="margin-top:0.25rem;">{{ $summary['excluded_count'] }}</h3>
    </div></div>
    <div class="card" style="border-left:3px solid {{ ($summary['difference'] ?? 0) == 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};"><div class="card-body" style="padding:0.75rem; text-align:center;">
        <h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Difference</h6>
        <h3 class="mb-0" style="margin-top:0.25rem; color:{{ ($summary['difference'] ?? 0) == 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};">
            {{ $summary['difference'] !== null ? 'R' . number_format($summary['difference'], 2) : 'N/A' }}
        </h3>
    </div></div>
</div>

{{-- Filters --}}
<div class="card mb-2">
    <div class="card-body" style="padding:0.75rem;">
        <form method="GET" action="{{ route('admin.bank-reconciliation.index') }}" class="filters">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="form-group">
                <label class="form-label">Account</label>
                <select name="account" class="form-control">
                    <option value="">All Accounts</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>{{ $acc->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="form-group">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="form-group">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Filter by description...">
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end; gap:0.35rem;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.bank-reconciliation.index') }}" class="btn btn-outline btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Tabs --}}
<div style="display:flex; gap:0; border-bottom:2px solid rgba(255,255,255,0.1); margin-bottom:1rem;">
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
           style="padding:0.5rem 0.75rem; text-decoration:none; font-size:0.85rem; {{ $tab === $tabKey ? 'font-weight:600; border-bottom:2px solid var(--primary, #3498db); margin-bottom:-2px; color:var(--primary, #3498db);' : 'color:var(--text-muted);' }}">
            {{ $tabLabel }}
            @if($tab === $tabKey && $transactions->total() > 0)
                <span class="badge badge-secondary" style="font-size:0.7rem;">{{ $transactions->total() }}</span>
            @endif
        </a>
    @endforeach
</div>

{{-- Transaction Table --}}
<div class="card">
    <div class="table-responsive"><table>
        <thead><tr>
            <th>Date</th>
            <th>Description</th>
            <th>Reference</th>
            <th class="text-right">Amount</th>
            <th>Category</th>
            <th>Status</th>
            <th>Match</th>
            <th class="text-right">Actions</th>
        </tr></thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td style="white-space:nowrap;">{{ $tx->transaction_date->format('d M Y') }}</td>
                    <td style="max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $tx->description }}">{{ $tx->description }}</td>
                    <td><small>{{ $tx->reference ?? '-' }}</small></td>
                    <td class="text-right font-semibold" style="color:{{ $tx->amount >= 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};">
                        {{ $tx->amount >= 0 ? '+' : '-' }}R{{ number_format(abs($tx->amount), 2) }}
                    </td>
                    <td>
                        @if($tx->category)
                            <span class="badge badge-secondary">{{ ucwords(str_replace('_', ' ', $tx->category)) }}</span>
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
                        <span class="badge badge-{{ $statusColors[$tx->reconciliation_status] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $tx->reconciliation_status)) }}
                        </span>
                        @if($tx->reconciliationMatch?->confidence)
                            <small class="text-muted">({{ number_format($tx->reconciliationMatch->confidence) }}%)</small>
                        @endif
                    </td>
                    <td style="font-size:0.8rem;">
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
                    <td class="text-right" style="white-space:nowrap;">
                        @if($tx->reconciliation_status === 'unmatched')
                            <button type="button" class="btn btn-primary btn-sm" onclick="togglePanel('match-{{ $tx->id }}')">Match</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="togglePanel('exclude-{{ $tx->id }}')">Exclude</button>
                        @elseif($tx->reconciliation_status === 'matched')
                            <form method="POST" action="{{ route('admin.bank-reconciliation.match', $tx) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="payment_id" value="{{ $tx->reconciliationMatch?->payment_id }}">
                                <input type="hidden" name="invoice_id" value="{{ $tx->reconciliationMatch?->invoice_id }}">
                                <button type="submit" class="btn btn-sm" style="background:var(--success, #27ae60); color:#fff;" title="Confirm this auto-match">Confirm</button>
                            </form>
                            <form method="POST" action="{{ route('admin.bank-reconciliation.unmatch', $tx) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline" title="Undo this match">Undo</button>
                            </form>
                        @elseif($tx->reconciliation_status === 'excluded' || $tx->reconciliation_status === 'manually_reconciled')
                            <form method="POST" action="{{ route('admin.bank-reconciliation.unmatch', $tx) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline" title="Revert to unmatched">Undo</button>
                            </form>
                        @endif
                    </td>
                </tr>

                {{-- Inline Match Panel --}}
                @if($tx->reconciliation_status === 'unmatched')
                <tr id="match-{{ $tx->id }}" style="display:none;">
                    <td colspan="8" style="padding:1rem; background:rgba(255,255,255,0.02);">
                        <div style="display:grid; grid-template-columns:1fr; gap:1rem;">
                            {{-- Transaction summary --}}
                            <div style="background:rgba(255,255,255,0.03); border-radius:var(--radius-sm, 6px); padding:0.75rem; display:flex; justify-content:space-between; align-items:start;">
                                <div>
                                    <small class="text-muted" style="display:block;">Bank Transaction</small>
                                    <strong>{{ $tx->transaction_date->format('d M Y') }}</strong> &mdash; {{ $tx->description }}
                                    @if($tx->reference)<br><small class="text-muted">Ref: {{ $tx->reference }}</small>@endif
                                </div>
                                <h4 class="mb-0" style="color:{{ $tx->amount >= 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};">R{{ number_format(abs($tx->amount), 2) }}</h4>
                            </div>

                            {{-- Match to existing payment --}}
                            <div>
                                <h6 class="font-semibold" style="margin-bottom:0.5rem;">Match to Existing Payment</h6>
                                <form method="POST" action="{{ route('admin.bank-reconciliation.match', $tx) }}">
                                    @csrf
                                    <div class="form-row">
                                        <div class="form-group" style="flex:2;">
                                            <input type="text" class="form-control" id="paymentSearch{{ $tx->id }}" placeholder="Search by reference, order #, customer name..." data-amount="{{ abs($tx->amount) }}" data-tx="{{ $tx->id }}">
                                            <div id="paymentResults{{ $tx->id }}" style="max-height:200px; overflow-y:auto; margin-top:0.5rem;"></div>
                                            <input type="hidden" name="payment_id" id="paymentId{{ $tx->id }}">
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="notes" class="form-control" placeholder="Notes (optional)">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Match to Payment</button>
                                </form>
                            </div>

                            @if($tx->type === 'credit')
                            {{-- Create new payment --}}
                            <div style="border-top:1px solid rgba(255,255,255,0.08); padding-top:0.75rem;">
                                <h6 class="font-semibold" style="margin-bottom:0.25rem;">Create New Payment</h6>
                                <p class="text-muted" style="font-size:0.85rem; margin-bottom:0.5rem;">If no existing payment matches, create one from this bank transaction.</p>
                                <form method="POST" action="{{ route('admin.bank-reconciliation.create-payment', $tx) }}">
                                    @csrf
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Payment Method</label>
                                            <select name="provider" class="form-control" required>
                                                <option value="eft">EFT</option>
                                                <option value="cash">Cash</option>
                                                <option value="card_manual">Card (Manual)</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Order ID</label>
                                            <input type="text" name="order_id" class="form-control" placeholder="Optional">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Customer ID</label>
                                            <input type="text" name="customer_id" class="form-control" placeholder="Optional">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-sm" style="background:var(--success, #27ae60); color:#fff;">Create Payment (R{{ number_format(abs($tx->amount), 2) }})</button>
                                </form>
                            </div>
                            @endif

                            {{-- Exclude section --}}
                            <div style="border-top:1px solid rgba(255,255,255,0.08); padding-top:0.75rem;">
                                <h6 class="font-semibold" style="margin-bottom:0.25rem;">Exclude from Reconciliation</h6>
                                <p class="text-muted" style="font-size:0.85rem; margin-bottom:0.5rem;">Use for bank fees, salaries, transfers, and other non-order transactions.</p>
                                <form method="POST" action="{{ route('admin.bank-reconciliation.exclude', $tx) }}">
                                    @csrf
                                    <div class="form-row">
                                        <div class="form-group">
                                            <select name="category" class="form-control" required>
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
                                        <div class="form-group" style="flex:2;">
                                            <input type="text" name="notes" class="form-control" placeholder="Notes (optional)">
                                        </div>
                                        <div class="form-group" style="display:flex; align-items:flex-end;">
                                            <button type="submit" class="btn btn-outline btn-sm">Exclude</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div style="text-align:right; margin-top:0.5rem;">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="togglePanel('match-{{ $tx->id }}')">Close</button>
                        </div>
                    </td>
                </tr>

                {{-- Inline Exclude Panel --}}
                <tr id="exclude-{{ $tx->id }}" style="display:none;">
                    <td colspan="8" style="padding:1rem; background:rgba(255,255,255,0.02);">
                        <div style="background:rgba(255,255,255,0.03); border-radius:var(--radius-sm, 6px); padding:0.75rem; margin-bottom:0.75rem;">
                            <small class="text-muted" style="display:block;">{{ $tx->transaction_date->format('d M Y') }}</small>
                            <p style="margin-bottom:0.25rem;">{{ $tx->description }}</p>
                            <strong style="color:{{ $tx->amount >= 0 ? 'var(--success, #27ae60)' : 'var(--danger, #e74c3c)' }};">R{{ number_format(abs($tx->amount), 2) }}</strong>
                        </div>
                        <form method="POST" action="{{ route('admin.bank-reconciliation.exclude', $tx) }}">
                            @csrf
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Category *</label>
                                    <select name="category" class="form-control" required>
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
                                <div class="form-group" style="flex:2;">
                                    <label class="form-label">Notes</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                                </div>
                                <div class="form-group" style="display:flex; align-items:flex-end; gap:0.35rem;">
                                    <button type="submit" class="btn btn-primary btn-sm">Exclude</button>
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="togglePanel('exclude-{{ $tx->id }}')">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </td>
                </tr>
                @endif
            @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            @if($tab === 'review')
                                <h3>No auto-matched transactions awaiting review</h3>
                                <p>Run Auto-Match to detect new matches.</p>
                            @elseif($tab === 'unmatched_income')
                                <h3>No unmatched income transactions</h3>
                                <p>All credits have been matched or excluded.</p>
                            @elseif($tab === 'unmatched_expenses')
                                <h3>No unmatched expense transactions</h3>
                            @elseif($tab === 'excluded')
                                <h3>No excluded transactions</h3>
                            @elseif($tab === 'reconciled')
                                <h3>No reconciled transactions yet</h3>
                            @else
                                <h3>No transactions found</h3>
                                <p>Try adjusting your filters.</p>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@if($transactions->hasPages())
    <div style="display:flex; justify-content:center; margin-top:1rem;">
        {{ $transactions->appends(request()->query())->links() }}
    </div>
@endif

@push('scripts')
<script>
function togglePanel(id) {
    var row = document.getElementById(id);
    if (!row) return;
    var isHidden = row.style.display === 'none';
    // Close all open panels first
    document.querySelectorAll('tr[id^="match-"], tr[id^="exclude-"]').forEach(function(el) {
        el.style.display = 'none';
    });
    if (isHidden) row.style.display = '';
}

// Payment search for match panels
document.querySelectorAll('[id^="paymentSearch"]').forEach(function(input) {
    var timeout;
    input.addEventListener('input', function() {
        clearTimeout(timeout);
        var txId = this.dataset.tx;
        var amount = this.dataset.amount;
        var q = this.value;
        var resultsDiv = document.getElementById('paymentResults' + txId);

        if (q.length < 2) {
            resultsDiv.innerHTML = '';
            return;
        }

        timeout = setTimeout(function() {
            fetch('{{ route("admin.bank-reconciliation.search-payments") }}?q=' + encodeURIComponent(q) + '&amount=' + amount)
                .then(function(r) { return r.json(); })
                .then(function(payments) {
                    if (payments.length === 0) {
                        resultsDiv.innerHTML = '<small class="text-muted">No matching payments found.</small>';
                        return;
                    }
                    resultsDiv.innerHTML = payments.map(function(p) {
                        var orderNum = p.order_number ? escapeHtml(p.order_number) : (p.reference ? escapeHtml(p.reference) : '-');
                        var customer = p.customer ? ' &mdash; ' + escapeHtml(p.customer) : '';
                        var invoice = p.invoice_no ? escapeHtml(p.invoice_no) : '';
                        return '<div style="padding:0.5rem; border:1px solid rgba(255,255,255,0.1); border-radius:var(--radius-sm, 6px); margin-bottom:0.35rem; cursor:pointer; font-size:0.85rem; display:flex; justify-content:space-between; align-items:center;" onclick="document.getElementById(\'paymentId' + txId + '\').value=\'' + p.id + '\'; this.parentNode.querySelectorAll(\'div\').forEach(function(el){ el.style.background=\'\'; }); this.style.background=\'rgba(39,174,96,0.1)\';"><div><strong>' + orderNum + '</strong>' + customer + '<br><small class="text-muted">' + escapeHtml(p.date) + ' &middot; ' + invoice + '</small></div><strong>R' + parseFloat(p.amount).toFixed(2) + '</strong></div>';
                    }).join('');
                });
        }, 300);
    });
});

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection
