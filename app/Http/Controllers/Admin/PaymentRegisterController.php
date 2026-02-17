<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentRegisterController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['order.customer.user', 'order.invoice', 'bankTransaction']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('order', fn($q2) => $q2->where('order_number', 'like', "%{$search}%"))
                  ->orWhereHas('order.customer.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        if ($request->filled('type')) {
            if ($request->type === 'income') {
                $query->where('amount', '>', 0);
            } elseif ($request->type === 'refund') {
                $query->where('amount', '<', 0);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(30);

        // Summary stats for the filtered date range
        $statsQuery = Payment::where('status', 'completed');
        if ($request->filled('from')) {
            $statsQuery->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $statsQuery->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $totalIncome = (clone $statsQuery)->where('amount', '>', 0)->sum('amount');
        $totalRefunds = abs((clone $statsQuery)->where('amount', '<', 0)->sum('amount'));
        $netIncome = $totalIncome - $totalRefunds;
        $paymentCount = (clone $statsQuery)->count();

        // Provider breakdown
        $providerBreakdown = (clone $statsQuery)->where('amount', '>', 0)
            ->selectRaw('provider, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('provider')
            ->get();

        // Get distinct providers for filter
        $providers = Payment::distinct()->pluck('provider')->sort()->values();

        return view('admin.payment-register.index', compact(
            'payments', 'totalIncome', 'totalRefunds', 'netIncome', 'paymentCount',
            'providerBreakdown', 'providers'
        ));
    }

    public function export(Request $request)
    {
        $query = Payment::with(['order.customer.user', 'order.invoice']);

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }
        if ($request->filled('type')) {
            if ($request->type === 'income') $query->where('amount', '>', 0);
            elseif ($request->type === 'refund') $query->where('amount', '<', 0);
        }
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $filename = 'payment-register-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date', 'Reference', 'Provider', 'Order #', 'Invoice #',
                'Customer', 'Amount', 'Type', 'Status', 'Notes',
            ]);

            $query->orderBy('created_at', 'desc')->chunk(200, function ($payments) use ($handle) {
                foreach ($payments as $payment) {
                    fputcsv($handle, [
                        $payment->created_at->format('Y-m-d H:i'),
                        $payment->reference ?? '-',
                        $payment->provider,
                        $payment->order?->order_number ?? '-',
                        $payment->order?->invoice?->invoice_no ?? '-',
                        $payment->order?->customer?->user?->name ?? '-',
                        $payment->amount,
                        $payment->amount >= 0 ? 'Income' : 'Refund',
                        $payment->status,
                        $payment->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
