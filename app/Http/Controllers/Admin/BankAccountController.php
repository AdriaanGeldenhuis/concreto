<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::withCount(['transactions', 'importBatches'])
            ->withMax('importBatches', 'created_at')
            ->orderBy('is_primary', 'desc')
            ->orderBy('account_name')
            ->get();

        return view('admin.bank-accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('admin.bank-accounts.form', ['account' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:20',
            'branch_code' => 'nullable|string|max:10',
            'account_type' => 'required|in:cheque,savings,credit',
            'currency' => 'required|string|size:3',
            'opening_balance' => 'required|numeric',
            'is_primary' => 'boolean',
        ]);

        if (!empty($validated['is_primary'])) {
            BankAccount::ensureSinglePrimary();
        }

        $accountNumber = $validated['account_number'] ?? null;

        $account = BankAccount::create([
            'account_name' => $validated['account_name'],
            'bank_name' => $validated['bank_name'],
            'account_number_encrypted' => $accountNumber,
            'account_number_last4' => $accountNumber ? substr($accountNumber, -4) : null,
            'branch_code' => $validated['branch_code'],
            'account_type' => $validated['account_type'],
            'currency' => $validated['currency'],
            'opening_balance' => $validated['opening_balance'],
            'current_balance' => $validated['opening_balance'],
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        AuditLog::log('created', 'BankAccount', $account->id, ['name' => $account->account_name]);

        return redirect()->route('admin.bank-accounts.index')
            ->with('success', "Bank account '{$account->account_name}' created.");
    }

    public function edit(BankAccount $bankAccount)
    {
        return view('admin.bank-accounts.form', ['account' => $bankAccount]);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:20',
            'branch_code' => 'nullable|string|max:10',
            'account_type' => 'required|in:cheque,savings,credit',
            'currency' => 'required|string|size:3',
            'opening_balance' => 'required|numeric',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['is_primary'])) {
            BankAccount::ensureSinglePrimary($bankAccount->id);
        }

        $accountNumber = $validated['account_number'] ?? null;

        $bankAccount->update([
            'account_name' => $validated['account_name'],
            'bank_name' => $validated['bank_name'],
            'account_number_encrypted' => $accountNumber ?: $bankAccount->account_number_encrypted,
            'account_number_last4' => $accountNumber ? substr($accountNumber, -4) : $bankAccount->account_number_last4,
            'branch_code' => $validated['branch_code'],
            'account_type' => $validated['account_type'],
            'currency' => $validated['currency'],
            'opening_balance' => $validated['opening_balance'],
            'is_primary' => $validated['is_primary'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        AuditLog::log('updated', 'BankAccount', $bankAccount->id, ['name' => $bankAccount->account_name]);

        return redirect()->route('admin.bank-accounts.index')
            ->with('success', "Bank account '{$bankAccount->account_name}' updated.");
    }

    public function destroy(BankAccount $bankAccount)
    {
        if ($bankAccount->transactions()->exists()) {
            return redirect()->route('admin.bank-accounts.index')
                ->with('error', "Cannot delete bank account with imported transactions. Deactivate it instead.");
        }

        $name = $bankAccount->account_name;
        AuditLog::log('deleted', 'BankAccount', $bankAccount->id, ['name' => $name]);
        $bankAccount->delete();

        return redirect()->route('admin.bank-accounts.index')
            ->with('success', "Bank account '{$name}' deleted.");
    }
}
