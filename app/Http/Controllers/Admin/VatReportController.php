<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
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

        // Calculate VAT on refunds (reverse calc: refund includes VAT at 15%)
        $refundedAmount = (float) ($refundVat->total_refunded ?? 0);
        $vatOnRefunds = round($refundedAmount - ($refundedAmount / 1.15), 2);

        // Net VAT payable
        $grossOutputVat = (float) ($outputVat->total_vat ?? 0);
        $netVatPayable = $grossOutputVat - $vatOnRefunds;

        // VAT rate
        $vatRate = 15;

        return view('admin.vat-report.index', compact(
            'outputVat', 'monthlyBreakdown', 'refundVat', 'vatOnRefunds',
            'grossOutputVat', 'netVatPayable', 'vatRate',
            'from', 'to', 'period'
        ));
    }

    public function export(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->endOfMonth()->format('Y-m-d'));
        $fromDate = $from . ' 00:00:00';
        $toDate = $to . ' 23:59:59';

        $filename = "vat-report-{$from}-to-{$to}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($fromDate, $toDate) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['VAT Report - Output VAT']);
            fputcsv($handle, []);

            // Summary
            $outputVat = Order::where('status', 'DELIVERED')
                ->whereBetween('updated_at', [$fromDate, $toDate])
                ->selectRaw('SUM(vat) as total_vat, SUM(subtotal) as total_excl_vat, SUM(total) as total_incl_vat, COUNT(*) as invoice_count')
                ->first();

            fputcsv($handle, ['Description', 'Amount (ZAR)']);
            fputcsv($handle, ['Total Sales (excl. VAT)', $outputVat->total_excl_vat ?? 0]);
            fputcsv($handle, ['Output VAT (15%)', $outputVat->total_vat ?? 0]);
            fputcsv($handle, ['Total Sales (incl. VAT)', $outputVat->total_incl_vat ?? 0]);
            fputcsv($handle, ['Number of Invoices', $outputVat->invoice_count ?? 0]);
            fputcsv($handle, []);

            // Monthly breakdown
            fputcsv($handle, ['Monthly Breakdown']);
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
                        fputcsv($handle, [
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
