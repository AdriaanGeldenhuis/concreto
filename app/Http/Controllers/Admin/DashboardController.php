<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
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
            'today_revenue' => Order::where('status', 'DELIVERED')->whereDate('updated_at', today())->sum('total'),
            'month_revenue' => Order::where('status', 'DELIVERED')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->sum('total'),
            'low_stock_count' => Product::where('is_active', true)->whereNotNull('stock_qty')->whereNotNull('low_stock_threshold')->whereColumn('stock_qty', '<=', 'low_stock_threshold')->count(),
        ];

        $recentOrders = Order::with(['customer.user', 'driver'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Weekly revenue chart (last 7 days)
        $weeklyRevenue = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyRevenue->push([
                'date' => $date->format('D'),
                'revenue' => (float) Order::where('status', 'DELIVERED')
                    ->whereDate('updated_at', $date)
                    ->sum('total'),
            ]);
        }

        // Active drivers with last known location
        $activeDrivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->get()
            ->map(function ($driver) {
                $lastLocation = $driver->driverLocations()
                    ->orderBy('recorded_at', 'desc')
                    ->first();
                $activeOrder = $driver->driverOrders()
                    ->whereIn('status', ['ASSIGNED', 'ACCEPTED', 'LOADED', 'IN_TRANSIT', 'ARRIVED'])
                    ->with('customer.user', 'deliveryAddress')
                    ->first();
                $driver->last_location = $lastLocation;
                $driver->active_order = $activeOrder;
                return $driver;
            });

        // Low stock products
        $lowStockProducts = Product::where('is_active', true)
            ->whereNotNull('stock_qty')
            ->whereNotNull('low_stock_threshold')
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->orderBy('stock_qty')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'weeklyRevenue', 'activeDrivers', 'lowStockProducts'
        ));
    }
}
