<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\OrderTemplate;
use App\Models\RecurringOrder;
use Illuminate\Http\Request;

class OrderTemplateController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customer;
        $templates = OrderTemplate::where('customer_id', $customer->id)
            ->with('deliveryAddress')
            ->orderBy('name')
            ->get();
        $recurringOrders = RecurringOrder::where('customer_id', $customer->id)
            ->with('deliveryAddress')
            ->get();

        return view('customer.templates.index', compact('templates', 'recurringOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'delivery_address_id' => 'nullable|exists:addresses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        OrderTemplate::create([
            'customer_id' => $request->user()->customer->id,
            'name' => $request->name,
            'delivery_address_id' => $request->delivery_address_id,
            'items' => $request->items,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Order template saved!');
    }

    public function saveFromOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'name' => 'required|string|max:255',
        ]);

        $customer = $request->user()->customer;
        $order = $customer->orders()->with('items')->findOrFail($request->order_id);

        $items = $order->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'qty' => (float) $item->qty,
        ])->toArray();

        OrderTemplate::create([
            'customer_id' => $customer->id,
            'name' => $request->name,
            'delivery_address_id' => $order->delivery_address_id,
            'items' => $items,
            'notes' => $order->notes,
        ]);

        return back()->with('success', 'Order saved as template!');
    }

    public function destroy(Request $request, OrderTemplate $template)
    {
        if ($template->customer_id !== $request->user()->customer->id) abort(403);
        $template->delete();
        return back()->with('success', 'Template deleted.');
    }

    public function storeRecurring(Request $request)
    {
        $request->validate([
            'order_template_id' => 'nullable|exists:order_templates,id',
            'frequency' => 'required|in:weekly,biweekly,monthly',
            'next_run_date' => 'required|date|after:today',
            'delivery_address_id' => 'required|exists:addresses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
        ]);

        RecurringOrder::create([
            'customer_id' => $request->user()->customer->id,
            'order_template_id' => $request->order_template_id,
            'frequency' => $request->frequency,
            'next_run_date' => $request->next_run_date,
            'delivery_address_id' => $request->delivery_address_id,
            'items' => $request->items,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Recurring order scheduled!');
    }

    public function cancelRecurring(Request $request, RecurringOrder $recurringOrder)
    {
        if ($recurringOrder->customer_id !== $request->user()->customer->id) abort(403);
        $recurringOrder->update(['is_active' => false]);
        return back()->with('success', 'Recurring order cancelled.');
    }
}
