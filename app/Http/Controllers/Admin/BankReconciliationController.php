<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\BankReconciliationService;
use Illuminate\Http\Request;

class BankReconciliationController extends Controller
{
    public function __construct(
        private BankReconciliationService $reconciliationService,
    ) {}

    public function index(Request $request)
    {
        $accounts = BankAccount::where('is_active', true)->orderBy('account_name')->get();
        $accountId = $request->input('account');
        $from = $request->input('from');
        $to = $request->input('to');
        $tab = $request->input('tab', 'review');

        $summary = $this->reconciliationService->getSummary($accountId, $from, $to);

        // Build transaction query
        $query = BankTransaction::with(['bankAccount', 'reconciliationMatch.payment.order.customer.user', 'reconciliationMatch.invoice']);

        if ($accountId) $query->forAccount($accountId);
        if ($from) $query->where('transaction_date', '>=', $from);
        if ($to) $query->where('transaction_date', '<=', $to);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('amount_min')) {
            $query->whereRaw('ABS(amount) >= ?', [$request->amount_min]);
        }
        if ($request->filled('amount_max')) {
            $query->whereRaw('ABS(amount) <= ?', [$request->amount_max]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        // Tab-specific filtering
        $transactions = match ($tab) {
            'review' => (clone $query)->where('reconciliation_status', 'matched')
                ->orderBy('transaction_date', 'desc')->paginate(30),
            'unmatched_income' => (clone $query)->unmatched()->credits()
                ->orderBy('transaction_date', 'desc')->paginate(30),
            'unmatched_expenses' => (clone $query)->unmatched()->debits()
                ->orderBy('transaction_date', 'desc')->paginate(30),
            'excluded' => (clone $query)->excluded()
                ->orderBy('transaction_date', 'desc')->paginate(30),
            'reconciled' => (clone $query)->whereIn('reconciliation_status', ['matched', 'manually_reconciled'])
                ->orderBy('transaction_date', 'desc')->paginate(30),
            'all' => (clone $query)->orderBy('transaction_date', 'desc')->paginate(30),
            default => (clone $query)->where('reconciliation_status', 'matched')
                ->orderBy('transaction_date', 'desc')->paginate(30),
        };

        return view('admin.bank-reconciliation.index', compact(
            'accounts', 'accountId', 'from', 'to', 'tab', 'summary', 'transactions'
        ));
    }

    public function match(Request $request, BankTransaction $bankTransaction)
    {
        $request->validate([
            'payment_id' => 'nullable|exists:payments,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment = $request->payment_id ? Payment::find($request->payment_id) : null;
        $invoice = $request->invoice_id ? Invoice::find($request->invoice_id) : null;

        $this->reconciliationService->createMatch(
            $bankTransaction,
            $payment,
            $invoice,
            'manual',
            100,
            auth()->user()->name,
            $request->notes,
        );

        return back()->with('success', 'Transaction matched successfully.');
    }

    public function unmatch(BankTransaction $bankTransaction)
    {
        $this->reconciliationService->unmatch($bankTransaction);

        return back()->with('success', 'Match removed. Transaction is now unmatched.');
    }

    public function exclude(Request $request, BankTransaction $bankTransaction)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->reconciliationService->exclude(
            $bankTransaction,
            $request->category,
            auth()->user()->name,
            $request->notes,
        );

        return back()->with('success', 'Transaction excluded from reconciliation.');
    }

    public function categorise(Request $request, BankTransaction $bankTransaction)
    {
        $request->validate([
            'category' => 'required|string|max:100',
        ]);

        $bankTransaction->update(['category' => $request->category]);

        return back()->with('success', 'Transaction categorised.');
    }

    public function createPayment(Request $request, BankTransaction $bankTransaction)
    {
        $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'customer_id' => 'nullable|exists:customers,id',
            'provider' => 'required|string|in:eft,cash,card_manual,other',
        ]);

        $payment = Payment::create([
            'order_id' => $request->order_id,
            'customer_id' => $request->customer_id,
            'provider' => $request->provider,
            'reference' => $bankTransaction->reference ?? $bankTransaction->description,
            'amount' => abs($bankTransaction->amount),
            'status' => 'completed',
            'bank_transaction_id' => $bankTransaction->id,
            'reconciled_at' => now(),
        ]);

        $this->reconciliationService->createMatch(
            $bankTransaction,
            $payment,
            null,
            'manual',
            100,
            auth()->user()->name,
            'Payment created from bank transaction',
        );

        return back()->with('success', 'Payment created and matched.');
    }

    public function runAutoMatch(Request $request)
    {
        $accountId = $request->input('account');
        $result = $this->reconciliationService->autoMatch($accountId ?: null);

        return back()->with('success', sprintf(
            'Auto-matching complete. Matched %d of %d transactions.',
            $result['matched'],
            $result['total_processed'],
        ));
    }

    public function suggestions(BankTransaction $bankTransaction)
    {
        $suggestions = $this->reconciliationService->getSuggestions($bankTransaction);

        return response()->json($suggestions);
    }

    public function statement(Request $request)
    {
        $accounts = BankAccount::where('is_active', true)->orderBy('account_name')->get();
        $accountId = $request->input('account', $accounts->first()?->id);
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->endOfMonth()->format('Y-m-d'));

        $statement = null;
        if ($accountId) {
            $statement = $this->reconciliationService->generateStatement($accountId, $from, $to);
        }

        return view('admin.bank-reconciliation.statement', compact('accounts', 'accountId', 'from', 'to', 'statement'));
    }

    public function exportStatement(Request $request)
    {
        $accountId = $request->input('account');
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->endOfMonth()->format('Y-m-d'));

        if (!$accountId) {
            return back()->with('error', 'Please select a bank account.');
        }

        $statement = $this->reconciliationService->generateStatement($accountId, $from, $to);
        $account = $statement['account'];

        $filename = sprintf('bank-reconciliation-%s-%s-to-%s.csv', str_replace(' ', '-', $account->account_name), $from, $to);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($statement, $from, $to) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Bank Reconciliation Statement']);
            fputcsv($handle, ['Account: ' . $statement['account']->account_name]);
            fputcsv($handle, ['Period: ' . $from . ' to ' . $to]);
            fputcsv($handle, []);

            fputcsv($handle, ['BANK STATEMENT', '']);
            fputcsv($handle, ['Opening bank balance', number_format($statement['opening_bank_balance'], 2)]);
            fputcsv($handle, ['Closing bank balance', number_format($statement['closing_bank_balance'], 2)]);
            fputcsv($handle, ['+ Deposits not yet cleared', number_format($statement['deposits_not_cleared'], 2)]);
            fputcsv($handle, ['- Outstanding payments', number_format($statement['outstanding_payments'], 2)]);
            fputcsv($handle, ['= Adjusted bank balance', number_format($statement['adjusted_bank_balance'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['BOOK BALANCE', '']);
            fputcsv($handle, ['Book balance (per system)', number_format($statement['book_balance'], 2)]);
            fputcsv($handle, ['+ Unrecorded deposits', number_format($statement['unrecorded_deposits'], 2)]);
            fputcsv($handle, ['- Bank charges', number_format($statement['bank_charges'], 2)]);
            fputcsv($handle, ['= Adjusted book balance', number_format($statement['adjusted_book_balance'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['DIFFERENCE', number_format($statement['difference'], 2)]);

            fclose($handle);
        }, 200, $headers);
    }

    public function searchPayments(Request $request)
    {
        $search = $request->input('q', '');
        $amount = $request->input('amount');

        $query = Payment::where('status', 'completed')
            ->whereNull('bank_transaction_id')
            ->where('amount', '>', 0)
            ->with(['order.customer.user', 'order.invoice']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('order', fn($q2) => $q2->where('order_number', 'like', "%{$search}%"))
                  ->orWhereHas('order.customer.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('order.invoice', fn($q2) => $q2->where('invoice_no', 'like', "%{$search}%"));
            });
        }

        // Order by amount closeness when amount is provided, show exact + near matches
        if ($amount) {
            $query->orderByRaw('ABS(amount - ?) ASC', [$amount]);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $payments = $query->limit(20)->get();

        return response()->json($payments->map(fn($p) => [
            'id' => $p->id,
            'reference' => $p->reference,
            'amount' => $p->amount,
            'date' => $p->created_at->format('d M Y'),
            'order_number' => $p->order?->order_number,
            'invoice_no' => $p->order?->invoice?->invoice_no,
            'customer' => $p->order?->customer?->user?->name,
        ]));
    }
}
