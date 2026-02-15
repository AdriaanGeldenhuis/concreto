<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generate(Order $order): Invoice
    {
        return DB::transaction(function () use ($order) {
            $order->load(['customer.user', 'items.product', 'deliveryAddress', 'proofOfDelivery']);

            // Don't regenerate invoice number if one already exists
            $invoice = Invoice::where('order_id', $order->id)->lockForUpdate()->first();
            if ($invoice) {
                // Regenerate PDF only
            } else {
                $invoice = Invoice::create([
                    'order_id' => $order->id,
                    'invoice_no' => Invoice::generateInvoiceNumber(),
                ]);
            }

            $settings = Setting::getAll();

            $pdf = Pdf::loadView('pdf.invoice', [
                'order' => $order,
                'invoice' => $invoice,
                'settings' => $settings,
            ]);

            $filename = "invoices/{$invoice->invoice_no}.pdf";
            Storage::disk('local')->put($filename, $pdf->output());

            $invoice->update(['pdf_path' => $filename]);

            return $invoice;
        });
    }

    public function generateStatement(Customer $customer, string $from, string $to): string
    {
        $customer->load('user');
        $orders = Order::where('customer_id', $customer->id)
            ->where('status', 'DELIVERED')
            ->whereBetween('updated_at', [$from, $to])
            ->with(['items.product', 'invoice'])
            ->orderBy('updated_at')
            ->get();

        $settings = Setting::getAll();

        $totalAmount = $orders->sum('total');

        $pdf = Pdf::loadView('pdf.statement', [
            'customer' => $customer,
            'orders' => $orders,
            'from' => $from,
            'to' => $to,
            'totalAmount' => $totalAmount,
            'settings' => $settings,
        ]);

        $filename = "statements/STMT-{$customer->id}-" . now()->format('Ymd-His') . ".pdf";
        Storage::disk('local')->put($filename, $pdf->output());

        return Storage::disk('local')->path($filename);
    }

    public function getPdfPath(Invoice $invoice): ?string
    {
        if (!$invoice->pdf_path) {
            return null;
        }
        return Storage::disk('local')->path($invoice->pdf_path);
    }
}
