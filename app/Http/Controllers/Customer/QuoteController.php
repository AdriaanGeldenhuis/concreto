<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\OrderService;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        $customer = $request->user()->customer;
        $quotes = Quote::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('customer.quotes.index', compact('quotes'));
    }

    public function show(Request $request, Quote $quote)
    {
        if ($quote->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        $quote->load('items.product');
        return view('customer.quotes.show', compact('quote'));
    }

    public function approve(Request $request, Quote $quote)
    {
        if ($quote->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        if ($quote->status !== 'sent') {
            return back()->with('error', 'This quote cannot be approved.');
        }

        $customer = $request->user()->customer;

        // Convert quote to order
        $items = $quote->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'qty' => $item->qty,
        ])->toArray();

        $order = $this->orderService->createOrder($customer, [
            'delivery_address_id' => $customer->default_address_id,
            'items' => $items,
        ]);

        $quote->update(['status' => 'converted']);

        if ($order->status === 'PENDING_PAYMENT') {
            return redirect()->route('customer.orders.pay', $order);
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Quote approved and order created!');
    }
}
