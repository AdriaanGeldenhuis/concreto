<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankReconciliationRule;
use Illuminate\Http\Request;

class BankReconciliationRuleController extends Controller
{
    public function index()
    {
        $rules = BankReconciliationRule::with('bankAccount')
            ->orderBy('name')
            ->get();

        $accounts = BankAccount::where('is_active', true)->orderBy('account_name')->get();

        return view('admin.bank-reconciliation.rules', compact('rules', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'match_field' => 'required|in:description,reference,amount',
            'match_type' => 'required|in:contains,starts_with,exact,regex',
            'match_value' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'auto_action' => 'required|in:categorise,exclude,match_customer',
            'target_customer_id' => 'nullable|exists:customers,id',
        ]);

        BankReconciliationRule::create($validated);

        return back()->with('success', "Rule '{$validated['name']}' created.");
    }

    public function edit(BankReconciliationRule $rule)
    {
        $accounts = BankAccount::where('is_active', true)->orderBy('account_name')->get();

        return view('admin.bank-reconciliation.rule-form', compact('rule', 'accounts'));
    }

    public function update(Request $request, BankReconciliationRule $rule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'match_field' => 'required|in:description,reference,amount',
            'match_type' => 'required|in:contains,starts_with,exact,regex',
            'match_value' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'auto_action' => 'required|in:categorise,exclude,match_customer',
            'target_customer_id' => 'nullable|exists:customers,id',
            'is_active' => 'boolean',
        ]);

        $rule->update($validated);

        return redirect()->route('admin.bank-reconciliation.rules.index')
            ->with('success', "Rule '{$rule->name}' updated.");
    }

    public function destroy(BankReconciliationRule $rule)
    {
        $name = $rule->name;
        $rule->delete();

        return back()->with('success', "Rule '{$name}' deleted.");
    }
}
