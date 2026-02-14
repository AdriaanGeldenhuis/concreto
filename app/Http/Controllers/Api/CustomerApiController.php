<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use App\Services\OrderService;
use App\Services\YocoService;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private YocoService $yocoService,
        private InvoiceService $invoiceService,
    ) {}

    public function listOrders(Request $request)
    {
        $customer = $request->user()->customer;
        $orders = Order::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($orders);
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'delivery_address_id' => 'required|exists:addresses,id',
            'scheduled_date' => 'nullable|date|after:today',
            'scheduled_time_window' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
        ]);

        $customer = $request->user()->customer;
        $order = $this->orderService->createOrder($customer, $request->all());

        return response()->json($order, 201);
    }

    public function showOrder(Request $request, Order $order)
    {
        if ($order->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        $order->load(['items.product', 'deliveryAddress', 'driver', 'proofOfDelivery', 'invoice']);
        return response()->json($order);
    }

    public function createPaymentSession(Request $request, Order $order)
    {
        if ($order->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        $checkout = $this->yocoService->createCheckout([
            'amount' => $order->total,
            'success_url' => $request->input('success_url', url('/customer/orders/' . $order->id)),
            'cancel_url' => $request->input('cancel_url', url('/customer/orders/' . $order->id)),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);

        $order->payments()->create([
            'customer_id' => $order->customer_id,
            'provider' => 'yoco',
            'checkout_id' => $checkout['id'] ?? null,
            'amount' => $order->total,
            'status' => 'pending',
            'metadata' => $checkout,
        ]);

        return response()->json(['checkout_url' => $checkout['redirectUrl']]);
    }

    public function listInvoices(Request $request)
    {
        $customer = $request->user()->customer;
        $invoices = Invoice::whereHas('order', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($invoices);
    }

    public function downloadInvoice(Request $request, Invoice $invoice)
    {
        if ($invoice->order->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        $path = $this->invoiceService->getPdfPath($invoice);
        if (!$path || !file_exists($path)) {
            $this->invoiceService->generate($invoice->order);
            $path = $this->invoiceService->getPdfPath($invoice->fresh());
        }

        return response()->download($path, "{$invoice->invoice_no}.pdf");
    }
}
