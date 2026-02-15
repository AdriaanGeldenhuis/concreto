<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customer;

        $recentOrders = Order::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $activeOrders = Order::where('customer_id', $customer->id)
            ->whereNotIn('status', ['DELIVERED', 'CANCELLED', 'REFUNDED', 'DRAFT'])
            ->count();

        // Orders actively being delivered (for tracking banner)
        $liveOrders = Order::where('customer_id', $customer->id)
            ->whereIn('status', ['IN_TRANSIT', 'ARRIVED', 'LOADED', 'ASSIGNED', 'ACCEPTED'])
            ->with(['driver', 'deliveryAddress'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $pendingInvoices = Invoice::whereHas('order', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })->whereNull('emailed_at')->count();

        return view('customer.dashboard', compact('customer', 'recentOrders', 'activeOrders', 'pendingInvoices', 'liveOrders'));
    }
}
