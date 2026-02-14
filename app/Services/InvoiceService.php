<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generate(Order $order): Invoice
    {
        $order->load(['customer.user', 'items.product', 'deliveryAddress', 'proofOfDelivery']);

        $invoice = Invoice::updateOrCreate(
            ['order_id' => $order->id],
            ['invoice_no' => Invoice::generateInvoiceNumber()]
        );

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
    }

    public function getPdfPath(Invoice $invoice): ?string
    {
        if (!$invoice->pdf_path) {
            return null;
        }
        return Storage::disk('local')->path($invoice->pdf_path);
    }
}
