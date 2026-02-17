<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverSalaryConfig;
use App\Models\DriverShift;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class ProfitLossController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'month');
        $from = now()->startOfMonth()->format('Y-m-d');
        $to = now()->endOfMonth()->format('Y-m-d');

        if ($request->filled('from') && $request->filled('to')) {
            $from = $request->from;
            $to = $request->to;
        } elseif ($period === 'quarter') {
            $from = now()->firstOfQuarter()->format('Y-m-d');
            $to = now()->lastOfQuarter()->format('Y-m-d');
        } elseif ($period === 'year') {
            $from = now()->startOfYear()->format('Y-m-d');
            $to = now()->endOfYear()->format('Y-m-d');
        } elseif ($period === 'taxyear') {
            $year = now()->month >= 3 ? now()->year : now()->year - 1;
            $from = "{$year}-03-01";
            $to = ($year + 1) . "-02-28";
        }

        $fromDate = $from . ' 00:00:00';
        $toDate = $to . ' 23:59:59';

        // === REVENUE ===
        $revenue = Order::where('status', 'DELIVERED')
            ->whereBetween('updated_at', [$fromDate, $toDate])
            ->selectRaw('SUM(subtotal) as subtotal, SUM(vat) as vat, SUM(delivery_fee) as delivery_fees, SUM(total) as total, SUM(discount_amount) as discounts, COUNT(*) as order_count')
            ->first();

        $totalRevenue = (float) ($revenue->subtotal ?? 0); // Revenue excl. VAT
        $totalVat = (float) ($revenue->vat ?? 0);
        $totalDeliveryFees = (float) ($revenue->delivery_fees ?? 0);
        $totalDiscounts = (float) ($revenue->discounts ?? 0);
        $totalIncVat = (float) ($revenue->total ?? 0);
        $orderCount = (int) ($revenue->order_count ?? 0);

        // === COST OF GOODS SOLD (COGS) ===
        $cogs = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'DELIVERED')
            ->whereBetween('orders.updated_at', [$fromDate, $toDate])
            ->selectRaw('SUM(order_items.qty * COALESCE(products.cost_price, 0)) as total_cogs')
            ->first();

        $totalCogs = (float) ($cogs->total_cogs ?? 0);
        $grossProfit = $totalRevenue - $totalCogs;
        $grossMarginPct = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        // === OPERATING EXPENSES ===

        // Driver wages
        $drivers = User::where('role', 'driver')
            ->with('salaryConfig')
            ->get();

        $totalDriverWages = 0;
        $driverWageDetails = [];

        foreach ($drivers as $driver) {
            $config = $driver->salaryConfig;
            if (!$config) continue;

            $shifts = DriverShift::where('driver_id', $driver->id)
                ->where('clock_in', '>=', $fromDate)
                ->where('clock_in', '<=', $toDate)
                ->get();

            $hours = $shifts->sum('hours_worked');
            $deliveries = $shifts->sum('deliveries_count');

            $pay = match ($config->pay_type) {
                'hourly' => $hours * (float) $config->rate,
                'per_delivery' => $deliveries * (float) $config->rate,
                'fixed_monthly' => (float) $config->rate,
                default => 0,
            };

            if ($config->bonus_per_delivery) {
                $pay += $deliveries * (float) $config->bonus_per_delivery;
            }

            $pay = round($pay, 2);
            $totalDriverWages += $pay;

            $driverWageDetails[] = (object) [
                'name' => $driver->name,
                'pay_type' => $config->pay_type,
                'hours' => round($hours, 1),
                'deliveries' => $deliveries,
                'pay' => $pay,
            ];
        }

        // Refunds as expense
        $refunds = Payment::where('status', 'completed')
            ->where('amount', '<', 0)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('SUM(ABS(amount)) as total, COUNT(*) as count')
            ->first();

        $totalRefunds = (float) ($refunds->total ?? 0);
        $refundCount = (int) ($refunds->count ?? 0);

        // Bank expenses (from categorised bank transactions)
        $bankExpenses = \App\Models\BankTransaction::whereIn('category', ['bank_fee', 'bank_charge'])
            ->whereIn('reconciliation_status', ['excluded', 'matched', 'manually_reconciled'])
            ->where('type', 'debit')
            ->whereBetween('transaction_date', [$from, $to])
            ->selectRaw('category, SUM(ABS(amount)) as total, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        $totalBankFees = (float) $bankExpenses->sum('total');

        // Total operating expenses
        $totalOpex = $totalDriverWages + $totalRefunds + $totalBankFees;

        // === NET PROFIT ===
        $netProfit = $grossProfit - $totalOpex;
        $netMarginPct = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // === MONTHLY TREND ===
        $monthlyTrend = Order::where('status', 'DELIVERED')
            ->whereBetween('updated_at', [$fromDate, $toDate])
            ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m') as month, SUM(subtotal) as revenue, COUNT(*) as orders")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Add COGS per month
        $monthlyCogs = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'DELIVERED')
            ->whereBetween('orders.updated_at', [$fromDate, $toDate])
            ->selectRaw("DATE_FORMAT(orders.updated_at, '%Y-%m') as month, SUM(order_items.qty * COALESCE(products.cost_price, 0)) as cogs")
            ->groupBy('month')
            ->pluck('cogs', 'month');

        $monthlyTrend->transform(function ($month) use ($monthlyCogs) {
            $month->cogs = (float) ($monthlyCogs[$month->month] ?? 0);
            $month->gross_profit = (float) $month->revenue - $month->cogs;
            return $month;
        });

        return view('admin.profit-loss.index', compact(
            'totalRevenue', 'totalVat', 'totalDeliveryFees', 'totalDiscounts', 'totalIncVat', 'orderCount',
            'totalCogs', 'grossProfit', 'grossMarginPct',
            'totalDriverWages', 'driverWageDetails', 'totalRefunds', 'refundCount', 'totalBankFees', 'totalOpex',
            'netProfit', 'netMarginPct',
            'monthlyTrend',
            'from', 'to', 'period'
        ));
    }

    public function export(Request $request)
    {
        // Redirect to the index to get the data, then let the view handle it
        // For simplicity, build CSV inline
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->endOfMonth()->format('Y-m-d'));
        $fromDate = $from . ' 00:00:00';
        $toDate = $to . ' 23:59:59';

        $filename = "profit-loss-{$from}-to-{$to}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($fromDate, $toDate) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Profit & Loss Statement']);
            fputcsv($handle, []);

            // Revenue
            $rev = Order::where('status', 'DELIVERED')
                ->whereBetween('updated_at', [$fromDate, $toDate])
                ->selectRaw('SUM(subtotal) as subtotal, SUM(vat) as vat, SUM(delivery_fee) as delivery, SUM(discount_amount) as discounts, SUM(total) as total, COUNT(*) as orders')
                ->first();

            fputcsv($handle, ['REVENUE', '']);
            fputcsv($handle, ['Sales Revenue (excl. VAT)', $rev->subtotal ?? 0]);
            fputcsv($handle, ['Delivery Fees', $rev->delivery ?? 0]);
            fputcsv($handle, ['Less: Discounts', '-' . ($rev->discounts ?? 0)]);
            fputcsv($handle, ['VAT Collected', $rev->vat ?? 0]);
            fputcsv($handle, ['Total Revenue (incl. VAT)', $rev->total ?? 0]);
            fputcsv($handle, []);

            // COGS
            $cogs = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.status', 'DELIVERED')
                ->whereBetween('orders.updated_at', [$fromDate, $toDate])
                ->selectRaw('SUM(order_items.qty * COALESCE(products.cost_price, 0)) as total')
                ->value('total') ?? 0;

            fputcsv($handle, ['COST OF GOODS SOLD', '']);
            fputcsv($handle, ['Product Cost (cost_price x qty)', $cogs]);
            fputcsv($handle, []);
            fputcsv($handle, ['GROSS PROFIT', ($rev->subtotal ?? 0) - $cogs]);
            fputcsv($handle, []);

            // Operating expenses
            fputcsv($handle, ['OPERATING EXPENSES', '']);

            $totalWages = 0;
            $drivers = User::where('role', 'driver')->with('salaryConfig')->get();
            foreach ($drivers as $driver) {
                $config = $driver->salaryConfig;
                if (!$config) continue;
                $shifts = DriverShift::where('driver_id', $driver->id)
                    ->where('clock_in', '>=', $fromDate)->where('clock_in', '<=', $toDate)->get();
                $hours = $shifts->sum('hours_worked');
                $dels = $shifts->sum('deliveries_count');
                $pay = match ($config->pay_type) {
                    'hourly' => $hours * (float) $config->rate,
                    'per_delivery' => $dels * (float) $config->rate,
                    'fixed_monthly' => (float) $config->rate,
                    default => 0,
                };
                if ($config->bonus_per_delivery) $pay += $dels * (float) $config->bonus_per_delivery;
                $totalWages += round($pay, 2);
                fputcsv($handle, ["  Driver: {$driver->name}", round($pay, 2)]);
            }
            fputcsv($handle, ['Total Driver Wages', $totalWages]);

            $refunds = Payment::where('status', 'completed')->where('amount', '<', 0)
                ->whereBetween('created_at', [$fromDate, $toDate])->sum(\DB::raw('ABS(amount)'));
            fputcsv($handle, ['Refunds', $refunds]);
            fputcsv($handle, ['Total Operating Expenses', $totalWages + $refunds]);
            fputcsv($handle, []);

            $netProfit = (($rev->subtotal ?? 0) - $cogs) - ($totalWages + $refunds);
            fputcsv($handle, ['NET PROFIT', $netProfit]);

            fclose($handle);
        }, 200, $headers);
    }
}
