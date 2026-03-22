<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DieselLog;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\SupplierInvoice;
use App\Helpers\CsvHelper;
use Illuminate\Http\Request;

class VatReportController extends Controller
{
    public function index(Request $request)
    {
        // Default to current VAT period (bi-monthly for SA)
        $period = $request->input('period', 'month');
        $from = now()->startOfMonth()->format('Y-m-d');
        $to = now()->endOfMonth()->format('Y-m-d');

        if ($request->filled('from') && $request->filled('to')) {
            $from = $request->from;
            $to = $request->to;
        } elseif ($period === 'bimonth') {
            $month = now()->month;
            $biStart = $month % 2 === 0 ? $month - 1 : $month;
            $from = now()->month($biStart)->startOfMonth()->format('Y-m-d');
            $to = now()->month($biStart + 1)->endOfMonth()->format('Y-m-d');
        } elseif ($period === 'quarter') {
            $from = now()->firstOfQuarter()->format('Y-m-d');
            $to = now()->lastOfQuarter()->format('Y-m-d');
        } elseif ($period === 'year') {
            // SA tax year: March to February
            $year = now()->month >= 3 ? now()->year : now()->year - 1;
            $from = "{$year}-03-01";
            $to = ($year + 1) . "-02-28";
        }

        $fromDate = $from . ' 00:00:00';
        $toDate = $to . ' 23:59:59';

        // VAT rate from settings (default 15%)
        $vatRate = (float) (Setting::get('vat_rate', '15'));

        // Output VAT (VAT on sales) - from delivered orders
        $outputVat = Order::where('status', 'DELIVERED')
            ->whereBetween('updated_at', [$fromDate, $toDate])
            ->selectRaw('SUM(vat) as total_vat, SUM(subtotal) as total_excl_vat, SUM(total) as total_incl_vat, SUM(delivery_fee) as total_delivery, SUM(discount_amount) as total_discounts, COUNT(*) as invoice_count')
            ->first();

        // Monthly breakdown within the period
        $monthlyBreakdown = Order::where('status', 'DELIVERED')
            ->whereBetween('updated_at', [$fromDate, $toDate])
            ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m') as month, SUM(vat) as vat, SUM(subtotal) as excl_vat, SUM(total) as incl_vat, COUNT(*) as invoices")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Refund VAT adjustments
        $refundVat = Payment::where('status', 'completed')
            ->where('amount', '<', 0)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('SUM(ABS(amount)) as total_refunded, COUNT(*) as refund_count')
            ->first();

        // Calculate VAT on refunds (reverse calc: refund includes VAT)
        $refundedAmount = (float) ($refundVat->total_refunded ?? 0);
        $vatOnRefunds = round($refundedAmount - ($refundedAmount / (1 + $vatRate / 100)), 2);

        // Gross output VAT
        $grossOutputVat = (float) ($outputVat->total_vat ?? 0);

        // === INPUT VAT (VAT on purchases/expenses you can claim back) ===

        // 1. VAT on general expenses
        $expenseInputVat = Expense::whereBetween('expense_date', [$from, $to])
            ->selectRaw('SUM(vat_amount) as total_vat, SUM(amount) as total_excl_vat, COUNT(*) as count')
            ->first();

        // 2. VAT on diesel (diesel has VAT built in - reverse calculate)
        $dieselTotal = DieselLog::whereBetween('fill_date', [$from, $to])
            ->selectRaw('SUM(total_cost) as total')
            ->value('total') ?? 0;
        $dieselVat = round((float) $dieselTotal - ((float) $dieselTotal / (1 + $vatRate / 100)), 2);

        // 3. VAT on supplier invoices
        $supplierInputVat = SupplierInvoice::whereBetween('invoice_date', [$from, $to])
            ->selectRaw('SUM(vat_amount) as total_vat, SUM(amount) as total_excl_vat, COUNT(*) as count')
            ->first();

        // Total input VAT
        $totalInputVat = (float) ($expenseInputVat->total_vat ?? 0)
            + $dieselVat
            + (float) ($supplierInputVat->total_vat ?? 0);

        // Net VAT payable (output - refund adjustments - input)
        $netOutputVat = $grossOutputVat - $vatOnRefunds;
        $netVatPayable = $netOutputVat - $totalInputVat;

        return view('admin.vat-report.index', compact(
            'outputVat', 'monthlyBreakdown', 'refundVat', 'vatOnRefunds',
            'grossOutputVat', 'netVatPayable', 'vatRate',
            'expenseInputVat', 'dieselVat', 'dieselTotal', 'supplierInputVat',
            'totalInputVat', 'netOutputVat',
            'from', 'to', 'period'
        ));
    }

    public function export(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->endOfMonth()->format('Y-m-d'));
        $fromDate = $from . ' 00:00:00';
        $toDate = $to . ' 23:59:59';
        $vatRate = (float) (Setting::get('vat_rate', '15'));

        $filename = "vat-report-{$from}-to-{$to}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($from, $to, $fromDate, $toDate, $vatRate) {
            $handle = fopen('php://output', 'w');
            CsvHelper::writeBom($handle);

            // Header
            fputcsv($handle, ['VAT Report - Full VAT201 Summary']);
            fputcsv($handle, ["Period: {$from} to {$to}"]);
            fputcsv($handle, ["VAT Rate: {$vatRate}%"]);
            fputcsv($handle, []);

            // Output VAT summary
            $outputVat = Order::where('status', 'DELIVERED')
                ->whereBetween('updated_at', [$fromDate, $toDate])
                ->selectRaw('SUM(vat) as total_vat, SUM(subtotal) as total_excl_vat, SUM(total) as total_incl_vat, COUNT(*) as invoice_count')
                ->first();

            fputcsv($handle, ['OUTPUT VAT (on sales)', '']);
            fputcsv($handle, ['Description', 'Amount (ZAR)']);
            fputcsv($handle, ['Total Sales (excl. VAT)', $outputVat->total_excl_vat ?? 0]);
            fputcsv($handle, ["Output VAT ({$vatRate}%)", $outputVat->total_vat ?? 0]);
            fputcsv($handle, ['Total Sales (incl. VAT)', $outputVat->total_incl_vat ?? 0]);
            fputcsv($handle, ['Number of Invoices', $outputVat->invoice_count ?? 0]);

            // Refund adjustments
            $refundVat = Payment::where('status', 'completed')
                ->where('amount', '<', 0)
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->selectRaw('SUM(ABS(amount)) as total_refunded')
                ->first();
            $refundedAmount = (float) ($refundVat->total_refunded ?? 0);
            $vatOnRefunds = round($refundedAmount - ($refundedAmount / (1 + $vatRate / 100)), 2);
            $grossOutputVat = (float) ($outputVat->total_vat ?? 0);

            fputcsv($handle, ['Less: VAT on Refunds', -$vatOnRefunds]);
            fputcsv($handle, ['Net Output VAT', $grossOutputVat - $vatOnRefunds]);
            fputcsv($handle, []);

            // Input VAT
            fputcsv($handle, ['INPUT VAT (on purchases - claimable)', '']);
            fputcsv($handle, ['Description', 'Amount (ZAR)']);

            $expenseVat = Expense::whereBetween('expense_date', [$from, $to])
                ->selectRaw('SUM(vat_amount) as total_vat')
                ->value('total_vat') ?? 0;
            fputcsv($handle, ['VAT on General Expenses', $expenseVat]);

            $dieselTotal = DieselLog::whereBetween('fill_date', [$from, $to])
                ->selectRaw('SUM(total_cost) as total')
                ->value('total') ?? 0;
            $dieselVat = round((float) $dieselTotal - ((float) $dieselTotal / (1 + $vatRate / 100)), 2);
            fputcsv($handle, ['VAT on Diesel/Fuel', $dieselVat]);

            $supplierVat = SupplierInvoice::whereBetween('invoice_date', [$from, $to])
                ->selectRaw('SUM(vat_amount) as total_vat')
                ->value('total_vat') ?? 0;
            fputcsv($handle, ['VAT on Supplier Invoices', $supplierVat]);

            $totalInputVat = (float) $expenseVat + $dieselVat + (float) $supplierVat;
            fputcsv($handle, ['Total Input VAT', $totalInputVat]);
            fputcsv($handle, []);

            // Net VAT
            $netVat = ($grossOutputVat - $vatOnRefunds) - $totalInputVat;
            fputcsv($handle, ['NET VAT PAYABLE TO SARS', $netVat]);
            if ($netVat < 0) {
                fputcsv($handle, ['(Negative = VAT refund claimable from SARS)', '']);
            }
            fputcsv($handle, []);

            // Monthly breakdown
            fputcsv($handle, ['Monthly Breakdown - Output VAT']);
            fputcsv($handle, ['Month', 'Sales (excl. VAT)', 'Output VAT', 'Sales (incl. VAT)', 'Invoices']);

            $monthly = Order::where('status', 'DELIVERED')
                ->whereBetween('updated_at', [$fromDate, $toDate])
                ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m') as month, SUM(vat) as vat, SUM(subtotal) as excl_vat, SUM(total) as incl_vat, COUNT(*) as invoices")
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            foreach ($monthly as $m) {
                fputcsv($handle, [$m->month, $m->excl_vat, $m->vat, $m->incl_vat, $m->invoices]);
            }

            fputcsv($handle, []);

            // Per-invoice detail
            fputcsv($handle, ['Invoice Detail']);
            fputcsv($handle, ['Date', 'Order #', 'Invoice #', 'Customer', 'Excl. VAT', 'VAT', 'Incl. VAT']);

            Order::where('status', 'DELIVERED')
                ->whereBetween('updated_at', [$fromDate, $toDate])
                ->with(['customer.user', 'invoice'])
                ->orderBy('updated_at')
                ->chunk(200, function ($orders) use ($handle) {
                    foreach ($orders as $order) {
                        CsvHelper::safePutCsv($handle, [
                            $order->updated_at->format('Y-m-d'),
                            $order->order_number,
                            $order->invoice?->invoice_no ?? '-',
                            $order->customer?->user?->name ?? '-',
                            $order->subtotal,
                            $order->vat,
                            $order->total,
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }
}
