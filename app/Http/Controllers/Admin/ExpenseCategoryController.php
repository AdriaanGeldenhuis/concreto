<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.expense-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $category = ExpenseCategory::create($data);

        AuditLog::log('created_expense_category', 'ExpenseCategory', $category->id);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $expenseCategory->update($data);

        AuditLog::log('updated_expense_category', 'ExpenseCategory', $expenseCategory->id);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->exists()) {
            return back()->with('error', 'Cannot delete a category that has expenses. Deactivate it instead.');
        }

        AuditLog::log('deleted_expense_category', 'ExpenseCategory', $expenseCategory->id);
        $expenseCategory->delete();

        return back()->with('success', 'Category deleted.');
    }
}
