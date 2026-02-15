<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', '30');
        $from = now()->subDays((int) $period)->startOfDay();
        $to = now()->endOfDay();

        if ($request->filled('from') && $request->filled('to')) {
            $from = \Carbon\Carbon::parse($request->from)->startOfDay();
            $to = \Carbon\Carbon::parse($request->to)->endOfDay();
        }

        // Revenue stats
        $revenue = Order::where('status', 'DELIVERED')
            ->whereBetween('updated_at', [$from, $to])
            ->selectRaw('SUM(total) as total_revenue, SUM(subtotal) as total_subtotal, SUM(vat) as total_vat, SUM(delivery_fee) as total_delivery, SUM(discount_amount) as total_discounts, COUNT(*) as order_count')
            ->first();

        // Cost & profit (from products with cost_price)
        $costData = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'DELIVERED')
            ->whereBetween('orders.updated_at', [$from, $to])
            ->selectRaw('SUM(order_items.qty * COALESCE(products.cost_price, 0)) as total_cost')
            ->first();

        // Daily revenue chart data
        $dailyRevenue = Order::where('status', 'DELIVERED')
            ->whereBetween('updated_at', [$from, $to])
            ->selectRaw('DATE(updated_at) as date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top products
        $topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'DELIVERED')
            ->whereBetween('orders.updated_at', [$from, $to])
            ->selectRaw('products.name, products.unit, SUM(order_items.qty) as total_qty, SUM(order_items.line_total) as total_revenue')
            ->groupBy('products.id', 'products.name', 'products.unit')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get();

        // Top customers
        $topCustomers = Order::join('customers', 'orders.customer_id', '=', 'customers.id')
            ->join('users', 'customers.user_id', '=', 'users.id')
            ->where('orders.status', 'DELIVERED')
            ->whereBetween('orders.updated_at', [$from, $to])
            ->selectRaw('users.name, users.email, COUNT(orders.id) as order_count, SUM(orders.total) as total_spent')
            ->groupBy('customers.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->take(10)
            ->get();

        // Driver performance
        $driverPerformance = Order::join('users', 'orders.driver_id', '=', 'users.id')
            ->where('orders.status', 'DELIVERED')
            ->whereBetween('orders.updated_at', [$from, $to])
            ->selectRaw('users.name, COUNT(orders.id) as deliveries, SUM(orders.total) as total_value')
            ->groupBy('orders.driver_id', 'users.name')
            ->orderByDesc('deliveries')
            ->get();

        // Payment method breakdown
        $paymentBreakdown = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('provider, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('provider')
            ->get();

        // Order status distribution
        $statusDistribution = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return view('admin.reports.index', compact(
            'revenue', 'costData', 'dailyRevenue', 'topProducts', 'topCustomers',
            'driverPerformance', 'paymentBreakdown', 'statusDistribution',
            'from', 'to', 'period'
        ));
    }

    public function export(Request $request)
    {
        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));
        $type = $request->input('type', 'orders');

        $filename = "{$type}-export-{$from}-to-{$to}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        if ($type === 'orders') {
            return response()->stream(function () use ($from, $to) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Order #', 'Date', 'Customer', 'Email', 'Status', 'Subtotal', 'VAT', 'Delivery Fee', 'Discount', 'Total', 'Driver', 'Delivery Address']);

                Order::with(['customer.user', 'driver', 'deliveryAddress'])
                    ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
                    ->orderBy('created_at', 'desc')
                    ->chunk(200, function ($orders) use ($handle) {
                        foreach ($orders as $order) {
                            fputcsv($handle, [
                                $order->order_number,
                                $order->created_at->format('Y-m-d H:i'),
                                $order->customer?->user?->name,
                                $order->customer?->user?->email,
                                $order->status,
                                $order->subtotal,
                                $order->vat,
                                $order->delivery_fee,
                                $order->discount_amount ?? 0,
                                $order->total,
                                $order->driver?->name,
                                $order->deliveryAddress?->full_address,
                            ]);
                        }
                    });

                fclose($handle);
            }, 200, $headers);
        }

        if ($type === 'products') {
            return response()->stream(function () {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Name', 'Category', 'Unit', 'Price (excl VAT)', 'Cost Price', 'Margin %', 'Stock Qty', 'Active', 'In Stock']);

                Product::with('category')->orderBy('name')->chunk(200, function ($products) use ($handle) {
                    foreach ($products as $p) {
                        fputcsv($handle, [
                            $p->name, $p->category?->name, $p->unit, $p->price, $p->cost_price,
                            $p->profit_margin, $p->stock_qty, $p->is_active ? 'Yes' : 'No', $p->in_stock ? 'Yes' : 'No',
                        ]);
                    }
                });

                fclose($handle);
            }, 200, $headers);
        }

        if ($type === 'customers') {
            return response()->stream(function () {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Name', 'Email', 'Phone', 'Type', 'Credit Limit', 'Total Orders', 'Total Spent']);

                Customer::with('user')->get()->each(function ($c) use ($handle) {
                    $totalOrders = $c->orders()->count();
                    $totalSpent = $c->orders()->where('status', 'DELIVERED')->sum('total');
                    fputcsv($handle, [
                        $c->user->name, $c->user->email, $c->user->phone,
                        $c->type, $c->credit_limit, $totalOrders, $totalSpent,
                    ]);
                });

                fclose($handle);
            }, 200, $headers);
        }

        abort(404);
    }
}
