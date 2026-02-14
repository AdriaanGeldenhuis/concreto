<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_orders' => Order::count(),
            'active_orders' => Order::whereNotIn('status', ['DELIVERED', 'CANCELLED', 'REFUNDED', 'DRAFT'])->count(),
            'today_deliveries' => Order::where('status', 'DELIVERED')->whereDate('updated_at', today())->count(),
            'total_customers' => Customer::count(),
            'total_drivers' => User::where('role', 'driver')->count(),
            'pending_payments' => Order::where('status', 'PENDING_PAYMENT')->count(),
        ];

        $recentOrders = Order::with(['customer.user', 'driver'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
