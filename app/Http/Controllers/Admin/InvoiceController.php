<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $query = Invoice::with(['order.customer.user', 'order.payments']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('order', fn($q2) => $q2->where('order_number', 'like', "%{$search}%"))
                  ->orWhereHas('order.customer.user', fn($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                $query->whereHas('order', function ($q) {
                    $q->whereHas('payments', fn($q2) => $q2->where('status', 'completed'));
                });
            } elseif ($request->status === 'unpaid') {
                $query->whereHas('order', function ($q) {
                    $q->whereDoesntHave('payments', fn($q2) => $q2->where('status', 'completed'));
                });
            } elseif ($request->status === 'overdue') {
                $query->whereHas('order', function ($q) {
                    $q->where('status', 'DELIVERED')
                      ->whereDoesntHave('payments', fn($q2) => $q2->where('status', 'completed'))
                      ->where('updated_at', '<', now()->subDays(30));
                });
            }
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(25);

        // Compute payment status for each invoice
        $invoices->getCollection()->transform(function ($invoice) {
            $order = $invoice->order;
            $totalPaid = $order?->payments()->where('status', 'completed')->where('amount', '>', 0)->sum('amount') ?? 0;
            $totalRefunded = abs($order?->payments()->where('status', 'completed')->where('amount', '<', 0)->sum('amount') ?? 0);
            $orderTotal = (float) ($order?->total ?? 0);

            $invoice->payment_status = 'unpaid';
            if ($totalPaid >= $orderTotal && $orderTotal > 0) {
                $invoice->payment_status = 'paid';
            } elseif ($totalPaid > 0) {
                $invoice->payment_status = 'partial';
            }

            if ($invoice->payment_status !== 'paid' && $order && $order->updated_at->lt(now()->subDays(30))) {
                $invoice->payment_status = 'overdue';
            }

            $invoice->total_paid = $totalPaid;
            $invoice->total_refunded = $totalRefunded;
            $invoice->order_total = $orderTotal;

            return $invoice;
        });

        // Summary stats
        $allInvoices = Invoice::with('order.payments')->get();
        $totalInvoiced = $allInvoices->sum(fn($inv) => (float) ($inv->order?->total ?? 0));
        $totalPaid = $allInvoices->sum(fn($inv) => $inv->order?->payments()->where('status', 'completed')->where('amount', '>', 0)->sum('amount') ?? 0);
        $totalUnpaid = $totalInvoiced - $totalPaid;
        $invoiceCount = $allInvoices->count();

        return view('admin.invoices.index', compact('invoices', 'totalInvoiced', 'totalPaid', 'totalUnpaid', 'invoiceCount'));
    }

    public function download(Invoice $invoice)
    {
        $path = $this->invoiceService->getPdfPath($invoice);

        if (!$path || !file_exists($path)) {
            // Regenerate if PDF missing
            $invoice->load('order');
            $invoice = $this->invoiceService->generate($invoice->order);
            $path = $this->invoiceService->getPdfPath($invoice);
        }

        return response()->download($path, "{$invoice->invoice_no}.pdf");
    }

    public function export(Request $request)
    {
        $query = Invoice::with(['order.customer.user', 'order.payments']);

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $filename = 'invoice-register-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Invoice No', 'Date', 'Order No', 'Customer', 'Email',
                'Subtotal', 'VAT', 'Delivery Fee', 'Discount', 'Total',
                'Total Paid', 'Total Refunded', 'Balance Due', 'Status', 'Emailed',
            ]);

            $query->orderBy('created_at', 'desc')->chunk(200, function ($invoices) use ($handle) {
                foreach ($invoices as $invoice) {
                    $order = $invoice->order;
                    $totalPaid = $order?->payments()->where('status', 'completed')->where('amount', '>', 0)->sum('amount') ?? 0;
                    $totalRefunded = abs($order?->payments()->where('status', 'completed')->where('amount', '<', 0)->sum('amount') ?? 0);
                    $orderTotal = (float) ($order?->total ?? 0);
                    $balanceDue = max(0, $orderTotal - $totalPaid);

                    $status = 'unpaid';
                    if ($totalPaid >= $orderTotal && $orderTotal > 0) $status = 'paid';
                    elseif ($totalPaid > 0) $status = 'partial';
                    if ($status !== 'paid' && $order?->updated_at?->lt(now()->subDays(30))) $status = 'overdue';

                    fputcsv($handle, [
                        $invoice->invoice_no,
                        $invoice->created_at->format('Y-m-d'),
                        $order?->order_number ?? '-',
                        $order?->customer?->user?->name ?? '-',
                        $order?->customer?->user?->email ?? '-',
                        $order?->subtotal ?? 0,
                        $order?->vat ?? 0,
                        $order?->delivery_fee ?? 0,
                        $order?->discount_amount ?? 0,
                        $orderTotal,
                        $totalPaid,
                        $totalRefunded,
                        $balanceDue,
                        $status,
                        $invoice->emailed_at ? 'Yes' : 'No',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
