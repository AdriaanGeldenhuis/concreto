<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Helpers\CsvHelper;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['user', 'company']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('is_active', $request->status === 'active');
            });
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('company', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                      ->orWhere('trading_as', 'like', "%{$search}%")
                      ->orWhere('vat_number', 'like', "%{$search}%");
                });
            });
        }

        // Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('dir', 'desc');

        if ($sortField === 'name') {
            $query->join('users', 'customers.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDir === 'desc' ? 'desc' : 'asc')
                  ->select('customers.*');
        } elseif (in_array($sortField, ['type', 'credit_limit', 'created_at'])) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $customers = $query->paginate(25);

        // Summary stats
        $stats = [
            'total' => Customer::count(),
            'cod' => Customer::where('type', 'COD')->count(),
            'account' => Customer::where('type', 'ACCOUNT')->count(),
            'active' => Customer::whereHas('user', fn($q) => $q->where('is_active', true))->count(),
            'total_outstanding' => (float) Order::whereHas('customer', fn($q) => $q->where('type', 'ACCOUNT'))
                ->where('status', 'DELIVERED')
                ->whereDoesntHave('payments', fn($q) => $q->where('status', 'completed'))
                ->sum('total'),
            'new_this_month' => Customer::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
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

        return back()->with('success', 'Customer settings updated.');
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
            'gps_lat' => 'nullable|numeric',
            'gps_lng' => 'nullable|numeric',
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

    public function storeAddress(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:50',
            'line1' => 'required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'gps_lat' => 'nullable|numeric',
            'gps_lng' => 'nullable|numeric',
        ]);

        if (!empty($data['gps_lat']) && !empty($data['gps_lng'])) {
            $data['gps_pinned'] = true;
        }
        $customer->addresses()->create($data);

        return back()->with('success', 'Delivery address added.');
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

    public function export(Request $request)
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
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            CsvHelper::writeBom($file);
            fputcsv($file, ['Name', 'Email', 'Phone', 'Company', 'VAT Number', 'Type', 'Credit Limit', 'Payment Terms', 'Outstanding Balance', 'Customer Since']);

            foreach ($customers as $c) {
                CsvHelper::safePutCsv($file, [
                    $c->user->name ?? '',
                    $c->user->email ?? '',
                    $c->user->phone ?? '',
                    $c->company->display_name ?? '',
                    $c->company->vat_number ?? '',
                    $c->type,
                    $c->credit_limit ?? '0.00',
                    $c->payment_terms ?? '',
                    number_format($c->outstanding_balance, 2),
                    $c->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
