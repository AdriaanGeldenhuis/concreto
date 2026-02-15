<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['user', 'company']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('company', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                      ->orWhere('trading_as', 'like', "%{$search}%")
                      ->orWhere('vat_number', 'like', "%{$search}%");
                });
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $customer->load(['user', 'company', 'addresses', 'orders' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(10);
        }]);
        return view('admin.customers.show', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'type' => 'required|in:COD,ACCOUNT',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string',
            'pay_before_dispatch' => 'boolean',
        ]);

        $data['pay_before_dispatch'] = $request->boolean('pay_before_dispatch');
        $customer->update($data);
        AuditLog::log('updated', 'Customer', $customer->id, $data);

        return back()->with('success', 'Customer updated.');
    }
}
