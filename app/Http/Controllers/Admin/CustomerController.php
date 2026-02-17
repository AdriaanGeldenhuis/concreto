<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
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
        }, 'orders.payments']);

        // 360-view stats
        $allOrders = $customer->orders();
        $stats = [
            'total_orders' => $allOrders->count(),
            'total_spent' => (float) $customer->orders()->where('status', 'DELIVERED')->sum('total'),
            'avg_order_value' => (float) $customer->orders()->where('status', 'DELIVERED')->avg('total'),
            'first_order' => $customer->orders()->oldest()->first()?->created_at,
            'last_order' => $customer->orders()->latest()->first()?->created_at,
            'delivered_count' => $customer->orders()->where('status', 'DELIVERED')->count(),
            'cancelled_count' => $customer->orders()->where('status', 'CANCELLED')->count(),
            'outstanding_balance' => $customer->outstanding_balance,
            'available_credit' => $customer->available_credit,
        ];

        // Monthly spend trend (last 12 months)
        $monthlySpend = $customer->orders()
            ->where('status', 'DELIVERED')
            ->where('updated_at', '>=', now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(updated_at, "%Y-%m") as month, SUM(total) as total, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Order status breakdown
        $statusBreakdown = $customer->orders()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Top products ordered
        $topProducts = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.customer_id', $customer->id)
            ->where('orders.status', 'DELIVERED')
            ->selectRaw('products.name, products.unit, SUM(order_items.qty) as total_qty, SUM(order_items.line_total) as total_value')
            ->groupBy('products.id', 'products.name', 'products.unit')
            ->orderByDesc('total_value')
            ->take(10)
            ->get();

        // Order templates and recurring
        $templates = $customer->orderTemplates()->with('deliveryAddress')->get();
        $recurringOrders = $customer->recurringOrders()->with('template')->get();

        // Wishlist count
        $wishlistCount = $customer->wishlists()->count();

        // Reviews
        $reviewCount = $customer->reviews()->count();

        return view('admin.customers.show', compact(
            'customer', 'stats', 'monthlySpend', 'statusBreakdown',
            'topProducts', 'templates', 'recurringOrders', 'wishlistCount', 'reviewCount'
        ));
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

    public function updateCompany(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'trading_as' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]);

        if ($customer->company) {
            $customer->company->update($data);
        } else {
            $company = Company::create($data);
            $customer->update(['company_id' => $company->id]);
        }

        AuditLog::log('updated', 'Company', $customer->company_id, $data);

        return back()->with('success', 'Company details updated.');
    }

    public function updateContact(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $customer->user->update($data);
        AuditLog::log('updated', 'User', $customer->user_id, $data);

        return back()->with('success', 'Contact information updated.');
    }
}
