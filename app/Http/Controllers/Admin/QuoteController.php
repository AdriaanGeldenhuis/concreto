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
    public function index(Request $request)
    {
        $query = Quote::with('customer.user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                  ->orWhereHas('customer.user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $quotes = $query->orderBy('created_at', 'desc')->paginate(20);
        $statuses = Quote::STATUSES;

        return view('admin.quotes.index', compact('quotes', 'statuses'));
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
            'quote_number' => Quote::generateQuoteNumber(),
            'status' => 'draft',
            'expires_at' => $request->expires_at,
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $item) {
            $lineTotal = round($item['qty'] * $item['unit_price'], 2);
            QuoteItem::create([
                'quote_id' => $quote->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'line_total' => $lineTotal,
            ]);
        }

        $quote->calculateTotals();
        AuditLog::log('created', 'Quote', $quote->id);

        return redirect()->route('admin.quotes.show', $quote)->with('success', "Quote {$quote->quote_number} created.");
    }

    public function show(Quote $quote)
    {
        $quote->load(['customer.user', 'customer.company', 'items.product']);

        $auditLogs = AuditLog::where('entity', 'Quote')
            ->where('entity_id', $quote->id)
            ->with('actor')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.quotes.show', compact('quote', 'auditLogs'));
    }

    public function send(Quote $quote)
    {
        if ($quote->isExpired()) {
            return back()->with('error', 'Cannot send an expired quote. Update the expiry date first.');
        }

        $quote->load(['customer.user', 'customer.company', 'items.product']);
        $pdfPath = $this->generatePdf($quote);
        $email = $quote->customer->user->email;
        $settings = Setting::getAll();

        try {
            Mail::send('emails.quote', [
                'quote' => $quote,
                'settings' => $settings,
            ], function ($message) use ($email, $quote, $pdfPath) {
                $message->to($email)
                    ->subject("Quote {$quote->quote_number} - Concreto")
                    ->attach($pdfPath, ['as' => "{$quote->quote_number}.pdf"]);
            });

            $quote->update(['status' => 'sent']);
            AuditLog::log('sent', 'Quote', $quote->id, ['email' => $email]);
            return back()->with('success', 'Quote emailed to customer.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send quote: ' . $e->getMessage());
        }
    }

    public function downloadPdf(Quote $quote)
    {
        $quote->load(['customer.user', 'customer.company', 'items.product']);
        $pdfPath = $this->generatePdf($quote);
        return response()->download($pdfPath, "{$quote->quote_number}.pdf");
    }

    public function convertToOrder(Quote $quote)
    {
        if (!in_array($quote->status, ['sent', 'approved'])) {
            return back()->with('error', 'Only sent or approved quotes can be converted to orders.');
        }

        if ($quote->isExpired()) {
            return back()->with('error', 'Cannot convert an expired quote to an order.');
        }

        $quote->load(['customer', 'items']);
        $customer = $quote->customer;

        // Preserve quote's custom unit prices
        $items = $quote->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'qty' => $item->qty,
            'unit_price' => $item->unit_price,
        ])->toArray();

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder($customer, [
            'delivery_address_id' => $customer->default_address_id,
            'items' => $items,
            'notes' => "Converted from Quote {$quote->quote_number}",
        ]);

        $quote->update(['status' => 'converted']);
        AuditLog::log('converted_to_order', 'Quote', $quote->id, ['order_id' => $order->id]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', "Quote converted to order #{$order->order_number}");
    }

    public function export(Request $request)
    {
        $query = Quote::with('customer.user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                  ->orWhereHas('customer.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $filename = 'quotes-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Quote #', 'Customer', 'Email', 'Status', 'Subtotal', 'VAT', 'Total', 'Expires', 'Created']);

            $query->orderBy('created_at', 'desc')->chunk(200, function ($quotes) use ($handle) {
                foreach ($quotes as $quote) {
                    fputcsv($handle, [
                        $quote->quote_number ?? 'Q-' . $quote->id,
                        $quote->customer?->user?->name ?? '-',
                        $quote->customer?->user?->email ?? '-',
                        $quote->status,
                        number_format($quote->subtotal, 2),
                        number_format($quote->vat, 2),
                        number_format($quote->total, 2),
                        $quote->expires_at?->format('Y-m-d') ?? '-',
                        $quote->created_at->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function edit(Quote $quote)
    {
        if (!in_array($quote->status, ['draft', 'sent'])) {
            return back()->with('error', 'Only draft or sent quotes can be edited.');
        }

        $quote->load(['customer.user', 'items.product']);
        $customers = Customer::with('user')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.quotes.form', compact('quote', 'customers', 'products'));
    }

    public function update(Request $request, Quote $quote)
    {
        if (!in_array($quote->status, ['draft', 'sent'])) {
            return back()->with('error', 'Only draft or sent quotes can be edited.');
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'expires_at' => 'nullable|date|after:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $quote->update([
            'customer_id' => $request->customer_id,
            'expires_at' => $request->expires_at,
            'notes' => $request->notes,
        ]);

        // Replace all items
        $quote->items()->delete();
        foreach ($request->items as $item) {
            $lineTotal = round($item['qty'] * $item['unit_price'], 2);
            QuoteItem::create([
                'quote_id' => $quote->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'line_total' => $lineTotal,
            ]);
        }

        $quote->calculateTotals();
        AuditLog::log('updated', 'Quote', $quote->id);

        return redirect()->route('admin.quotes.show', $quote)->with('success', 'Quote updated.');
    }

    private function generatePdf(Quote $quote): string
    {
        $settings = Setting::getAll();

        $pdf = Pdf::loadView('pdf.quote', [
            'quote' => $quote,
            'settings' => $settings,
            'vat' => (float) $quote->vat,
            'grandTotal' => (float) $quote->total,
        ]);

        $quoteNum = $quote->quote_number ?? 'Q-' . $quote->id;
        $filename = "quotes/{$quoteNum}.pdf";
        Storage::disk('local')->put($filename, $pdf->output());
        return Storage::disk('local')->path($filename);
    }
}
