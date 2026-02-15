<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $customer = $request->user()->customer;
        $invoices = Invoice::whereHas('order', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })->orderBy('created_at', 'desc')->paginate(15);

        return view('customer.invoices.index', compact('invoices'));
    }

    public function download(Request $request, Invoice $invoice)
    {
        if ($invoice->order->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        $path = $this->invoiceService->getPdfPath($invoice);
        if (!$path || !file_exists($path)) {
            // Regenerate
            $this->invoiceService->generate($invoice->order);
            $path = $this->invoiceService->getPdfPath($invoice->fresh());
        }

        return response()->download($path, "{$invoice->invoice_no}.pdf");
    }

    public function statement(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $customer = $request->user()->customer;
        $path = $this->invoiceService->generateStatement(
            $customer,
            $request->from,
            $request->to,
        );

        $filename = "Statement-{$request->from}-to-{$request->to}.pdf";
        return response()->download($path, $filename);
    }
}
