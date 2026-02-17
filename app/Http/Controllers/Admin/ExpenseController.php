<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('category')
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }
        if ($request->filled('from')) {
            $query->where('expense_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('expense_date', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $expenses = $query->paginate(30)->withQueryString();
        $categories = ExpenseCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        // Summary
        $summaryQuery = Expense::query();
        if ($request->filled('from')) $summaryQuery->where('expense_date', '>=', $request->from);
        if ($request->filled('to')) $summaryQuery->where('expense_date', '<=', $request->to);
        if ($request->filled('category_id')) $summaryQuery->where('expense_category_id', $request->category_id);

        $summary = (object) [
            'total_amount' => $summaryQuery->sum('amount'),
            'total_vat' => $summaryQuery->sum('vat_amount'),
            'expense_count' => $summaryQuery->count(),
        ];

        // Monthly total
        $monthTotal = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        // By category breakdown
        $byCategory = Expense::query()
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id');

        if ($request->filled('from')) $byCategory->where('expense_date', '>=', $request->from);
        if ($request->filled('to')) $byCategory->where('expense_date', '<=', $request->to);

        $byCategory = $byCategory->selectRaw('expense_categories.name, expense_categories.color, SUM(expenses.amount) as total, COUNT(*) as count')
            ->groupBy('expense_categories.id', 'expense_categories.name', 'expense_categories.color')
            ->orderByDesc('total')
            ->get();

        return view('admin.expenses.index', compact('expenses', 'categories', 'summary', 'monthTotal', 'byCategory'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        return view('admin.expenses.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date|before_or_equal:today',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'vat_amount' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:150',
            'reference' => 'nullable|string|max:100',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|string|in:weekly,monthly,quarterly,yearly',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['vat_amount'] = $data['vat_amount'] ?? 0;
        $data['created_by'] = auth()->id();

        $expense = Expense::create($data);

        AuditLog::log('created_expense', 'Expense', $expense->id);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded - R ' . number_format($data['amount'], 2));
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        return view('admin.expenses.form', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date|before_or_equal:today',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'vat_amount' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:150',
            'reference' => 'nullable|string|max:100',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|string|in:weekly,monthly,quarterly,yearly',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['vat_amount'] = $data['vat_amount'] ?? 0;

        $expense->update($data);

        AuditLog::log('updated_expense', 'Expense', $expense->id);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        AuditLog::log('deleted_expense', 'Expense', $expense->id);
        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted.');
    }

    public function export(Request $request)
    {
        $query = Expense::with('category')
            ->orderBy('expense_date', 'desc');

        if ($request->filled('category_id')) $query->where('expense_category_id', $request->category_id);
        if ($request->filled('from')) $query->where('expense_date', '>=', $request->from);
        if ($request->filled('to')) $query->where('expense_date', '<=', $request->to);

        $expenses = $query->get();
        $filename = 'expenses-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Category', 'Description', 'Amount', 'VAT', 'Total', 'Supplier', 'Reference', 'Recurring', 'Notes']);

            foreach ($expenses as $exp) {
                fputcsv($handle, [
                    $exp->expense_date->format('Y-m-d'),
                    $exp->category?->name ?? '-',
                    $exp->description,
                    $exp->amount,
                    $exp->vat_amount,
                    $exp->total_with_vat,
                    $exp->supplier ?? '-',
                    $exp->reference ?? '-',
                    $exp->is_recurring ? $exp->recurring_frequency : 'No',
                    $exp->notes ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
