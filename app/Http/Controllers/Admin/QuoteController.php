<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Setting;
use App\Services\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class QuoteController extends Controller
{
    public function index()
    {
        $quotes = Quote::with('customer.user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.quotes.index', compact('quotes'));
    }

    public function create()
    {
        $customers = Customer::with('user')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('admin.quotes.form', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'expires_at' => 'nullable|date|after:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $quote = Quote::create([
            'customer_id' => $request->customer_id,
            'status' => 'draft',
            'expires_at' => $request->expires_at,
            'notes' => $request->notes,
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $lineTotal = round($item['qty'] * $item['unit_price'], 2);
            QuoteItem::create([
                'quote_id' => $quote->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'line_total' => $lineTotal,
            ]);
            $total += $lineTotal;
        }

        $quote->update(['total' => $total]);
        AuditLog::log('created', 'Quote', $quote->id);

        return redirect()->route('admin.quotes.index')->with('success', 'Quote created.');
    }

    public function show(Quote $quote)
    {
        $quote->load(['customer.user', 'items.product']);
        return view('admin.quotes.show', compact('quote'));
    }

    public function send(Quote $quote)
    {
        $quote->load(['customer.user', 'items.product']);
        $pdfPath = $this->generatePdf($quote);
        $email = $quote->customer->user->email;

        try {
            Mail::send('emails.quote', ['quote' => $quote], function ($message) use ($email, $quote, $pdfPath) {
                $message->to($email)
                    ->subject("Quote #Q-{$quote->id} - Concreto")
                    ->attach($pdfPath, ['as' => "Quote-Q-{$quote->id}.pdf"]);
            });

            $quote->update(['status' => 'sent']);
            AuditLog::log('sent', 'Quote', $quote->id);
            return back()->with('success', 'Quote emailed to customer.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send quote: ' . $e->getMessage());
        }
    }

    public function downloadPdf(Quote $quote)
    {
        $quote->load(['customer.user', 'items.product']);
        $pdfPath = $this->generatePdf($quote);
        return response()->download($pdfPath, "Quote-Q-{$quote->id}.pdf");
    }

    public function convertToOrder(Quote $quote)
    {
        if (!in_array($quote->status, ['sent', 'approved'])) {
            return back()->with('error', 'Only sent or approved quotes can be converted to orders.');
        }

        $quote->load(['customer', 'items']);
        $customer = $quote->customer;

        $items = $quote->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'qty' => $item->qty,
        ])->toArray();

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder($customer, [
            'delivery_address_id' => $customer->default_address_id,
            'items' => $items,
            'notes' => 'Converted from Quote #Q-' . $quote->id,
        ]);

        $quote->update(['status' => 'converted']);
        AuditLog::log('converted_to_order', 'Quote', $quote->id, ['order_id' => $order->id]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Quote converted to order #' . $order->order_number);
    }

    private function generatePdf(Quote $quote): string
    {
        $settings = Setting::getAll();
        $vat = round((float) $quote->total * 0.15, 2);
        $grandTotal = round((float) $quote->total + $vat, 2);

        $pdf = Pdf::loadView('pdf.quote', [
            'quote' => $quote,
            'settings' => $settings,
            'vat' => $vat,
            'grandTotal' => $grandTotal,
        ]);

        $filename = "quotes/Quote-Q-{$quote->id}.pdf";
        Storage::disk('local')->put($filename, $pdf->output());
        return Storage::disk('local')->path($filename);
    }
}
