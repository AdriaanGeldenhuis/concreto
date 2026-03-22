<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SupplierInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceSheetController extends Controller
{
    public function index(Request $request)
    {
        $asAt = $request->input('as_at', now()->format('Y-m-d'));

        // === ASSETS ===

        // Cash & bank balances
        $bankAccounts = BankAccount::where('is_active', true)->get();
        $totalCash = (float) $bankAccounts->sum('current_balance');

        // Accounts receivable (what customers owe us)
        // All delivered orders that aren't fully paid
        $arData = Customer::where('type', 'ACCOUNT')
            ->with(['orders' => function ($q) use ($asAt) {
                $q->where('status', 'DELIVERED')
                    ->where('updated_at', '<=', $asAt . ' 23:59:59')
                    ->whereDoesntHave('payments', fn($q2) => $q2->where('status', 'completed'));
            }])
            ->get();

        $totalAR = $arData->sum(fn($c) => $c->orders->sum('total'));

        $totalAssets = $totalCash + $totalAR;

        // === LIABILITIES ===

        // Accounts payable (what we owe suppliers)
        $totalAP = SupplierInvoice::whereIn('status', ['unpaid', 'partial'])
            ->where('invoice_date', '<=', $asAt)
            ->sum(DB::raw('total - amount_paid'));

        // VAT liability (output VAT collected but not yet paid to SARS)
        // Simplified: current month's net VAT
        $currentMonthStart = now()->startOfMonth()->format('Y-m-d') . ' 00:00:00';
        $vatLiability = Order::where('status', 'DELIVERED')
            ->where('updated_at', '>=', $currentMonthStart)
            ->where('updated_at', '<=', $asAt . ' 23:59:59')
            ->sum('vat');

        $totalLiabilities = (float) $totalAP + (float) $vatLiability;

        // === EQUITY ===

        // Retained earnings = cumulative net profit (revenue - COGS - expenses)
        // We calculate this as total assets minus total liabilities
        $equity = $totalAssets - $totalLiabilities;

        return view('admin.balance-sheet.index', compact(
            'asAt', 'bankAccounts', 'totalCash', 'totalAR', 'totalAssets',
            'totalAP', 'vatLiability', 'totalLiabilities', 'equity'
        ));
    }
}
