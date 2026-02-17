<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankReconciliationMatch;
use App\Models\BankReconciliationRule;
use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Collection;

class BankReconciliationService
{
    /**
     * Run auto-matching on all unmatched transactions for an account.
     */
    public function autoMatch(?int $accountId = null): array
    {
        $query = BankTransaction::unmatched();
        if ($accountId) {
            $query->forAccount($accountId);
        }

        $transactions = $query->get();
        $matched = 0;
        $results = [];

        foreach ($transactions as $transaction) {
            $result = $this->matchTransaction($transaction);
            if ($result) {
                $matched++;
                $results[] = $result;
            }
        }

        return [
            'total_processed' => $transactions->count(),
            'matched' => $matched,
            'unmatched' => $transactions->count() - $matched,
            'results' => $results,
        ];
    }

    /**
     * Attempt to match a single bank transaction using all strategies.
     */
    public function matchTransaction(BankTransaction $transaction): ?array
    {
        // Strategy 1: Rule-based match (check first to handle exclusions)
        $ruleResult = $this->matchByRules($transaction);
        if ($ruleResult) return $ruleResult;

        // Only try payment-matching strategies for credits (money in)
        if ($transaction->type === 'credit') {
            // Strategy 2: Reference match
            $refResult = $this->matchByReference($transaction);
            if ($refResult) return $refResult;

            // Strategy 3: Amount + Date match
            $amountResult = $this->matchByAmountDate($transaction);
            if ($amountResult) return $amountResult;

            // Strategy 4: Customer name match
            $nameResult = $this->matchByCustomerName($transaction);
            if ($nameResult) return $nameResult;
        }

        return null;
    }

    /**
     * Strategy 1: Match by reference number in description.
     */
    private function matchByReference(BankTransaction $transaction): ?array
    {
        $description = $transaction->description;
        $reference = $transaction->reference;

        $searchTerms = array_filter([$reference]);

        // Also try to extract references from description
        $importService = new BankImportService();
        $extracted = $importService->extractReference($description);
        if ($extracted && !in_array($extracted, $searchTerms)) {
            $searchTerms[] = $extracted;
        }

        if (empty($searchTerms)) return null;

        foreach ($searchTerms as $term) {
            // Match against payment references
            $payment = Payment::where('reference', $term)
                ->where('status', 'completed')
                ->where('amount', '>', 0)
                ->whereNull('bank_transaction_id')
                ->first();

            if ($payment) {
                return $this->createMatch($transaction, $payment, null, 'auto_reference', 95);
            }

            // Match against invoice numbers
            $invoice = Invoice::where('invoice_no', $term)->first();
            if ($invoice) {
                $payment = Payment::where('order_id', $invoice->order_id)
                    ->where('status', 'completed')
                    ->whereNull('bank_transaction_id')
                    ->first();

                return $this->createMatch($transaction, $payment, $invoice, 'auto_reference', 95);
            }

            // Match against order numbers
            $orderPayment = Payment::whereHas('order', fn($q) => $q->where('order_number', $term))
                ->where('status', 'completed')
                ->whereNull('bank_transaction_id')
                ->first();

            if ($orderPayment) {
                return $this->createMatch($transaction, $orderPayment, null, 'auto_reference', 90);
            }
        }

        return null;
    }

    /**
     * Strategy 2: Match by amount and date proximity.
     */
    private function matchByAmountDate(BankTransaction $transaction): ?array
    {
        $amount = abs($transaction->amount);
        $date = $transaction->transaction_date;

        // Find payments with exact amount within ±1 day
        $candidates = Payment::where('status', 'completed')
            ->whereNull('bank_transaction_id')
            ->where('amount', '>', 0)
            ->whereRaw('ABS(amount - ?) < 0.01', [$amount])
            ->whereBetween('created_at', [
                $date->copy()->subDay()->startOfDay(),
                $date->copy()->addDay()->endOfDay(),
            ])
            ->get();

        if ($candidates->count() === 1) {
            // Single match — high confidence
            $payment = $candidates->first();
            $sameDay = $payment->created_at->format('Y-m-d') === $date->format('Y-m-d');
            $confidence = $sameDay ? 85 : 75;

            return $this->createMatch($transaction, $payment, null, 'auto_amount_date', $confidence);
        }

        // Multiple candidates — don't auto-match, but we could flag for review
        return null;
    }

    /**
     * Strategy 3: Match by rules (bank fees, recurring items, etc).
     */
    private function matchByRules(BankTransaction $transaction): ?array
    {
        $rules = BankReconciliationRule::where('is_active', true)
            ->where(function ($q) use ($transaction) {
                $q->whereNull('bank_account_id')
                  ->orWhere('bank_account_id', $transaction->bank_account_id);
            })
            ->get();

        foreach ($rules as $rule) {
            $fieldValue = match ($rule->match_field) {
                'description' => $transaction->description,
                'reference' => $transaction->reference ?? '',
                'amount' => (string) $transaction->amount,
                default => '',
            };

            if ($rule->matches($fieldValue)) {
                // Apply the rule
                $rule->increment('times_applied');

                if ($rule->auto_action === 'exclude') {
                    $transaction->update([
                        'reconciliation_status' => 'excluded',
                        'category' => $rule->category,
                        'matched_at' => now(),
                        'matched_by' => 'system (rule: ' . $rule->name . ')',
                    ]);

                    return [
                        'transaction_id' => $transaction->id,
                        'type' => 'excluded',
                        'rule' => $rule->name,
                        'category' => $rule->category,
                    ];
                }

                if ($rule->auto_action === 'categorise') {
                    $transaction->update([
                        'category' => $rule->category,
                    ]);
                    // Don't return — allow further matching strategies
                }

                if ($rule->auto_action === 'match_customer' && $rule->target_customer_id) {
                    // Try to find unmatched payment for this customer
                    $payment = Payment::where('customer_id', $rule->target_customer_id)
                        ->where('status', 'completed')
                        ->whereNull('bank_transaction_id')
                        ->where('amount', '>', 0)
                        ->whereRaw('ABS(amount - ?) < 0.01', [abs($transaction->amount)])
                        ->first();

                    if ($payment) {
                        return $this->createMatch($transaction, $payment, null, 'auto_reference', 80);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Strategy 4: Fuzzy match by customer name in description.
     */
    private function matchByCustomerName(BankTransaction $transaction): ?array
    {
        $description = strtoupper($transaction->description);

        // Get customers with recent unmatched payments
        $customers = Customer::whereHas('user')
            ->whereHas('payments', function ($q) {
                $q->where('status', 'completed')
                  ->whereNull('bank_transaction_id')
                  ->where('amount', '>', 0);
            })
            ->with('user')
            ->get();

        foreach ($customers as $customer) {
            $name = strtoupper($customer->user->name ?? '');
            if (strlen($name) < 3) continue;

            // Check if customer name appears in description
            $nameParts = explode(' ', $name);
            $matchedParts = 0;

            foreach ($nameParts as $part) {
                if (strlen($part) >= 3 && str_contains($description, $part)) {
                    $matchedParts++;
                }
            }

            // Need at least surname to match (last name part)
            if ($matchedParts > 0 && ($matchedParts >= count($nameParts) - 1 || $matchedParts >= 2)) {
                // Found potential customer — check if they have matching amount payment
                $payment = Payment::where('customer_id', $customer->id)
                    ->where('status', 'completed')
                    ->whereNull('bank_transaction_id')
                    ->where('amount', '>', 0)
                    ->whereRaw('ABS(amount - ?) < 0.01', [abs($transaction->amount)])
                    ->first();

                if ($payment) {
                    // Don't auto-match — too risky. Return as suggestion only.
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Create a match record and update transaction status.
     */
    public function createMatch(
        BankTransaction $transaction,
        ?Payment $payment,
        ?Invoice $invoice,
        string $matchType,
        float $confidence,
        string $matchedBy = 'system',
        ?string $notes = null,
    ): array {
        BankReconciliationMatch::create([
            'bank_transaction_id' => $transaction->id,
            'payment_id' => $payment?->id,
            'invoice_id' => $invoice?->id,
            'match_type' => $matchType,
            'confidence' => $confidence,
            'matched_by' => $matchedBy,
            'notes' => $notes,
        ]);

        $status = $matchedBy === 'system' ? 'matched' : 'manually_reconciled';

        $transaction->update([
            'reconciliation_status' => $status,
            'matched_at' => now(),
            'matched_by' => $matchedBy,
        ]);

        if ($payment) {
            $payment->update([
                'bank_transaction_id' => $transaction->id,
                'reconciled_at' => now(),
            ]);
        }

        return [
            'transaction_id' => $transaction->id,
            'payment_id' => $payment?->id,
            'invoice_id' => $invoice?->id,
            'match_type' => $matchType,
            'confidence' => $confidence,
        ];
    }

    /**
     * Remove a match (unmatch).
     */
    public function unmatch(BankTransaction $transaction): void
    {
        $match = $transaction->reconciliationMatch;

        if ($match) {
            if ($match->payment_id) {
                Payment::where('id', $match->payment_id)->update([
                    'bank_transaction_id' => null,
                    'reconciled_at' => null,
                ]);
            }
            $match->delete();
        }

        $transaction->update([
            'reconciliation_status' => 'unmatched',
            'matched_at' => null,
            'matched_by' => null,
        ]);
    }

    /**
     * Exclude a transaction from reconciliation.
     */
    public function exclude(BankTransaction $transaction, string $category, string $excludedBy, ?string $notes = null): void
    {
        $transaction->update([
            'reconciliation_status' => 'excluded',
            'category' => $category,
            'matched_at' => now(),
            'matched_by' => $excludedBy,
            'notes' => $notes,
        ]);
    }

    /**
     * Get reconciliation summary statistics.
     */
    public function getSummary(?int $accountId = null, ?string $from = null, ?string $to = null): array
    {
        $query = BankTransaction::query();
        if ($accountId) $query->forAccount($accountId);
        if ($from) $query->where('transaction_date', '>=', $from);
        if ($to) $query->where('transaction_date', '<=', $to);

        $unmatchedCredits = (clone $query)->unmatched()->credits()->sum('amount');
        $unmatchedDebits = abs((clone $query)->unmatched()->debits()->sum('amount'));
        $autoMatchedCount = (clone $query)->where('reconciliation_status', 'matched')->count();
        $manualMatchedCount = (clone $query)->where('reconciliation_status', 'manually_reconciled')->count();
        $excludedCount = (clone $query)->excluded()->count();
        $totalCount = (clone $query)->count();
        $unmatchedCount = (clone $query)->unmatched()->count();

        // Bank balance (latest transaction with running_balance)
        $bankBalance = null;
        if ($accountId) {
            $account = BankAccount::find($accountId);
            $bankBalance = $account?->current_balance;
        }

        // System balance (sum of completed payments)
        $systemPayments = Payment::where('status', 'completed')->where('amount', '>', 0);
        if ($from) $systemPayments->where('created_at', '>=', $from . ' 00:00:00');
        if ($to) $systemPayments->where('created_at', '<=', $to . ' 23:59:59');
        $systemBalance = $systemPayments->sum('amount');

        return [
            'unmatched_credits' => (float) $unmatchedCredits,
            'unmatched_debits' => (float) $unmatchedDebits,
            'auto_matched_count' => $autoMatchedCount,
            'manual_matched_count' => $manualMatchedCount,
            'fully_reconciled_count' => $autoMatchedCount + $manualMatchedCount,
            'excluded_count' => $excludedCount,
            'total_count' => $totalCount,
            'unmatched_count' => $unmatchedCount,
            'bank_balance' => $bankBalance,
            'system_balance' => (float) $systemBalance,
            'difference' => $bankBalance !== null ? (float) $bankBalance - (float) $systemBalance : null,
        ];
    }

    /**
     * Get suggested matches for a bank transaction.
     */
    public function getSuggestions(BankTransaction $transaction): Collection
    {
        $suggestions = collect();
        $amount = abs($transaction->amount);
        $date = $transaction->transaction_date;

        if ($transaction->type !== 'credit') {
            return $suggestions;
        }

        // Find payments matching by reference
        if ($transaction->reference) {
            $refMatches = Payment::where('status', 'completed')
                ->whereNull('bank_transaction_id')
                ->where('amount', '>', 0)
                ->where(function ($q) use ($transaction) {
                    $q->where('reference', $transaction->reference)
                      ->orWhereHas('order', fn($q2) => $q2->where('order_number', $transaction->reference))
                      ->orWhereHas('order.invoice', fn($q2) => $q2->where('invoice_no', $transaction->reference));
                })
                ->with(['order.customer.user', 'order.invoice'])
                ->get()
                ->map(fn($p) => (object) [
                    'payment' => $p,
                    'confidence' => 95,
                    'match_type' => 'reference',
                    'reason' => 'Reference match: ' . $transaction->reference,
                ]);

            $suggestions = $suggestions->merge($refMatches);
        }

        // Find payments matching by amount + date
        $amountMatches = Payment::where('status', 'completed')
            ->whereNull('bank_transaction_id')
            ->where('amount', '>', 0)
            ->whereRaw('ABS(amount - ?) < 0.01', [$amount])
            ->whereBetween('created_at', [
                $date->copy()->subDays(3)->startOfDay(),
                $date->copy()->addDays(3)->endOfDay(),
            ])
            ->with(['order.customer.user', 'order.invoice'])
            ->get()
            ->map(function ($p) use ($date) {
                $daysDiff = abs($p->created_at->diffInDays($date));
                $confidence = match (true) {
                    $daysDiff === 0 => 85,
                    $daysDiff === 1 => 75,
                    default => 65,
                };
                return (object) [
                    'payment' => $p,
                    'confidence' => $confidence,
                    'match_type' => 'amount_date',
                    'reason' => "Amount R " . number_format($p->amount, 2) . " on " . $p->created_at->format('d M Y'),
                ];
            });

        // Merge avoiding duplicates
        foreach ($amountMatches as $am) {
            if (!$suggestions->contains(fn($s) => $s->payment->id === $am->payment->id)) {
                $suggestions->push($am);
            }
        }

        return $suggestions->sortByDesc('confidence')->values();
    }

    /**
     * Generate reconciliation statement data.
     */
    public function generateStatement(int $accountId, string $from, string $to): array
    {
        $account = BankAccount::findOrFail($accountId);

        // Opening balance: get the running_balance of the last transaction before period
        $openingTransaction = BankTransaction::forAccount($accountId)
            ->where('transaction_date', '<', $from)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $openingBankBalance = $openingTransaction
            ? (float) $openingTransaction->running_balance
            : (float) $account->opening_balance;

        // Closing balance: get the running_balance of the last transaction in period
        $closingTransaction = BankTransaction::forAccount($accountId)
            ->where('transaction_date', '<=', $to)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $closingBankBalance = $closingTransaction
            ? (float) $closingTransaction->running_balance
            : $openingBankBalance;

        // Deposits not yet cleared (credits in system but not in bank)
        $depositsNotCleared = Payment::where('status', 'completed')
            ->where('amount', '>', 0)
            ->whereNull('bank_transaction_id')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->sum('amount');

        // Outstanding payments (payments in bank not in system)
        $outstandingPayments = BankTransaction::forAccount($accountId)
            ->inDateRange($from, $to)
            ->unmatched()
            ->debits()
            ->sum('amount');

        $adjustedBankBalance = $closingBankBalance + (float) $depositsNotCleared + (float) $outstandingPayments;

        // Book balance
        $bookBalance = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->sum('amount');

        // Unrecorded bank charges
        $bankCharges = BankTransaction::forAccount($accountId)
            ->inDateRange($from, $to)
            ->where(function ($q) {
                $q->where('category', 'bank_fee')
                  ->orWhere('category', 'bank_charge');
            })
            ->unmatched()
            ->sum('amount');

        // Unrecorded deposits
        $unrecordedDeposits = BankTransaction::forAccount($accountId)
            ->inDateRange($from, $to)
            ->unmatched()
            ->credits()
            ->sum('amount');

        $adjustedBookBalance = (float) $bookBalance + (float) $unrecordedDeposits + (float) $bankCharges;
        $difference = round($adjustedBankBalance - $adjustedBookBalance, 2);

        return [
            'account' => $account,
            'period_from' => $from,
            'period_to' => $to,
            'opening_bank_balance' => $openingBankBalance,
            'closing_bank_balance' => $closingBankBalance,
            'deposits_not_cleared' => (float) $depositsNotCleared,
            'outstanding_payments' => (float) $outstandingPayments,
            'adjusted_bank_balance' => $adjustedBankBalance,
            'book_balance' => (float) $bookBalance,
            'unrecorded_deposits' => (float) $unrecordedDeposits,
            'bank_charges' => (float) $bankCharges,
            'adjusted_book_balance' => $adjustedBookBalance,
            'difference' => $difference,
        ];
    }
}
