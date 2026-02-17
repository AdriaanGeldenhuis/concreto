<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Date range: today, 7d, 30d, custom
        $range = $request->input('range', 'today');
        $from = null;
        $to = now()->endOfDay();

        switch ($range) {
            case '7d':
                $from = now()->subDays(7)->startOfDay();
                break;
            case '30d':
                $from = now()->subDays(30)->startOfDay();
                break;
            case 'custom':
                $from = $request->filled('from') ? \Carbon\Carbon::parse($request->from)->startOfDay() : now()->startOfDay();
                $to = $request->filled('to') ? \Carbon\Carbon::parse($request->to)->endOfDay() : now()->endOfDay();
                break;
            default: // today
                $from = now()->startOfDay();
                break;
        }

        $rangeLabel = match ($range) {
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
            'custom' => $from->format('d M') . ' - ' . $to->format('d M Y'),
            default => 'Today',
        };

        // Period stats
        $periodOrders = Order::whereBetween('created_at', [$from, $to]);
        $periodDelivered = Order::where('status', 'DELIVERED')->whereBetween('updated_at', [$from, $to]);

        $stats = [
            'total_orders' => Order::count(),
            'active_orders' => Order::whereNotIn('status', ['DELIVERED', 'CANCELLED', 'REFUNDED', 'DRAFT'])->count(),
            'period_orders' => (clone $periodOrders)->count(),
            'period_deliveries' => (clone $periodDelivered)->count(),
            'total_customers' => Customer::count(),
            'total_drivers' => User::where('role', 'driver')->count(),
            'pending_payments' => Order::where('status', 'PENDING_PAYMENT')->count(),
            'period_revenue' => (clone $periodDelivered)->sum('total'),
            'month_revenue' => Order::where('status', 'DELIVERED')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->sum('total'),
            'low_stock_count' => Product::where('is_active', true)->whereNotNull('stock_qty')->whereNotNull('low_stock_threshold')->whereColumn('stock_qty', '<=', 'low_stock_threshold')->count(),
        ];

        // Previous period comparison
        $periodDays = $from->diffInDays($to) ?: 1;
        $prevFrom = (clone $from)->subDays($periodDays);
        $prevTo = (clone $from)->subSecond();
        $prevRevenue = Order::where('status', 'DELIVERED')->whereBetween('updated_at', [$prevFrom, $prevTo])->sum('total');
        $prevOrders = Order::whereBetween('created_at', [$prevFrom, $prevTo])->count();
        $revenueChange = $prevRevenue > 0 ? round((($stats['period_revenue'] - $prevRevenue) / $prevRevenue) * 100, 1) : null;
        $ordersChange = $prevOrders > 0 ? round((($stats['period_orders'] - $prevOrders) / $prevOrders) * 100, 1) : null;

        $recentOrders = Order::with(['customer.user', 'driver'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Revenue chart: adapt to range
        $chartDays = match ($range) {
            '7d' => 7,
            '30d' => 30,
            'custom' => min((int) $from->diffInDays($to), 30),
            default => 7,
        };
        $weeklyRevenue = collect();
        for ($i = $chartDays - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyRevenue->push([
                'date' => $chartDays > 14 ? $date->format('d') : $date->format('D'),
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

        // Bank reconciliation stats
        $unreconciledCount = BankTransaction::unmatched()->count();

        // Low stock products
        $lowStockProducts = Product::where('is_active', true)
            ->whereNotNull('stock_qty')
            ->whereNotNull('low_stock_threshold')
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->orderBy('stock_qty')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'weeklyRevenue', 'activeDrivers', 'lowStockProducts',
            'unreconciledCount', 'range', 'rangeLabel', 'revenueChange', 'ordersChange',
            'from', 'to'
        ));
    }
}
