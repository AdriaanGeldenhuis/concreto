<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        // Product performance
        $productPerformance = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.status', 'DELIVERED')
            ->whereBetween('orders.updated_at', [$from, $to . ' 23:59:59'])
            ->selectRaw('products.id, products.name, products.unit, products.price, products.cost_price, products.stock_qty, categories.name as category_name,
                SUM(order_items.qty) as total_qty,
                SUM(order_items.line_total) as total_revenue,
                SUM(order_items.qty * COALESCE(products.cost_price, 0)) as total_cost,
                COUNT(DISTINCT orders.id) as order_count')
            ->groupBy('products.id', 'products.name', 'products.unit', 'products.price', 'products.cost_price', 'products.stock_qty', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($p) {
                $p->gross_profit = $p->total_revenue - $p->total_cost;
                $p->margin_pct = $p->total_revenue > 0
                    ? round(($p->gross_profit / $p->total_revenue) * 100, 1)
                    : 0;
                return $p;
            });

        // Summary stats
        $totalRevenue = $productPerformance->sum('total_revenue');
        $totalCost = $productPerformance->sum('total_cost');
        $totalProfit = $totalRevenue - $totalCost;
        $overallMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0;
        $totalUnitsSold = $productPerformance->sum('total_qty');

        // Stock health
        $lowStock = Product::where('is_active', true)
            ->whereNotNull('stock_qty')
            ->whereNotNull('low_stock_threshold')
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->with('category')
            ->orderBy('stock_qty')
            ->get();

        $outOfStock = Product::where('is_active', true)
            ->where(function ($q) {
                $q->where('in_stock', false)
                  ->orWhere(fn($q2) => $q2->whereNotNull('stock_qty')->where('stock_qty', '<=', 0));
            })
            ->with('category')
            ->get();

        // Category breakdown
        $categoryBreakdown = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.status', 'DELIVERED')
            ->whereBetween('orders.updated_at', [$from, $to . ' 23:59:59'])
            ->selectRaw('COALESCE(categories.name, "Uncategorised") as category, SUM(order_items.line_total) as revenue, SUM(order_items.qty) as units, COUNT(DISTINCT products.id) as product_count')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->get();

        // Products with no sales in period
        $noSales = Product::where('is_active', true)
            ->whereNotIn('id', $productPerformance->pluck('id'))
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('admin.product-analytics.index', compact(
            'productPerformance', 'totalRevenue', 'totalCost', 'totalProfit',
            'overallMargin', 'totalUnitsSold', 'lowStock', 'outOfStock',
            'categoryBreakdown', 'noSales', 'from', 'to'
        ));
    }
}
