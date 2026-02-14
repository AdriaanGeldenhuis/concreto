<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Http\Request;

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

    public function send(Quote $quote)
    {
        $quote->update(['status' => 'sent']);
        AuditLog::log('sent', 'Quote', $quote->id);
        return back()->with('success', 'Quote sent to customer.');
    }
}
