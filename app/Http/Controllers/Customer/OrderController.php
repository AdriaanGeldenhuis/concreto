<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\YocoService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private YocoService $yocoService,
    ) {}

    public function index(Request $request)
    {
        $customer = $request->user()->customer;
        $orders = Order::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('customer.orders.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $customer = $request->user()->customer;
        $products = Product::where('is_active', true)->where('in_stock', true)->orderBy('name')->get();
        $addresses = $customer->addresses;

        return view('customer.orders.create', compact('products', 'addresses', 'customer'));
    }

    public function store(Request $request)
    {
        $customer = $request->user()->customer;

        $request->validate([
            'delivery_address_id' => 'required|exists:addresses,id',
            'scheduled_date' => 'nullable|date|after:today',
            'scheduled_time_window' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
        ]);

        // Verify address belongs to this customer
        $address = \App\Models\Address::find($request->delivery_address_id);
        if (!$address || $address->customer_id !== $customer->id) {
            abort(403, 'Invalid delivery address.');
        }

        try {
            $order = $this->orderService->createOrder($customer, $request->all());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($order->status === 'PENDING_PAYMENT') {
            return redirect()->route('customer.orders.pay', $order);
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Order placed successfully!');
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeCustomerOrder($request, $order);
        $order->load(['items.product', 'deliveryAddress', 'driver', 'proofOfDelivery', 'invoice']);
        return view('customer.orders.show', compact('order'));
    }

    public function pay(Request $request, Order $order)
    {
        $this->authorizeCustomerOrder($request, $order);

        if ($order->status !== 'PENDING_PAYMENT') {
            return redirect()->route('customer.orders.show', $order)
                ->with('info', 'This order does not require payment.');
        }

        return view('customer.orders.pay', compact('order'));
    }

    public function createPaymentSession(Request $request, Order $order)
    {
        $this->authorizeCustomerOrder($request, $order);

        if ($order->status !== 'PENDING_PAYMENT') {
            return redirect()->route('customer.orders.show', $order)
                ->with('info', 'This order does not require payment.');
        }

        // Reuse existing pending payment session if available
        $existingPayment = $order->payments()->where('status', 'pending')->latest()->first();
        if ($existingPayment && $existingPayment->metadata && isset($existingPayment->metadata['redirectUrl'])) {
            return redirect($existingPayment->metadata['redirectUrl']);
        }

        $checkout = $this->yocoService->createCheckout([
            'amount' => $order->total,
            'success_url' => route('customer.orders.payment-success', $order),
            'cancel_url' => route('customer.orders.pay', $order),
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

        return redirect($checkout['redirectUrl']);
    }

    public function paymentSuccess(Request $request, Order $order)
    {
        $this->authorizeCustomerOrder($request, $order);

        // Webhook will confirm the payment, but show success page
        return view('customer.orders.payment-success', compact('order'));
    }

    public function reorder(Request $request, Order $order)
    {
        $this->authorizeCustomerOrder($request, $order);

        $customer = $request->user()->customer;
        $products = Product::where('is_active', true)->where('in_stock', true)->orderBy('name')->get();
        $addresses = $customer->addresses;

        // Pre-fill from previous order
        $prefill = [
            'address_id' => $order->delivery_address_id,
            'items' => $order->items->map(fn($item) => [
                'product_id' => $item->product_id,
                'qty' => $item->qty,
            ])->toArray(),
            'notes' => $order->notes,
        ];

        return view('customer.orders.create', compact('products', 'addresses', 'customer', 'prefill'));
    }

    public function dispute(Request $request, Order $order)
    {
        $this->authorizeCustomerOrder($request, $order);

        // Only allow dispute within 24 hours of delivery
        if ($order->status !== 'DELIVERED') {
            return back()->with('error', 'You can only dispute delivered orders.');
        }

        $deliveredAt = $order->proofOfDelivery?->signed_at ?? $order->updated_at;
        if ($deliveredAt->diffInHours(now()) > 24) {
            return back()->with('error', 'Disputes must be raised within 24 hours of delivery.');
        }

        $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        // Log the dispute as an audit entry
        \App\Models\AuditLog::log(
            'dispute_raised',
            'Order',
            $order->id,
            [
                'reason' => $request->input('reason'),
                'customer' => $request->user()->name,
                'order_number' => $order->order_number,
            ]
        );

        // Update order notes
        $order->update([
            'notes' => ($order->notes ? $order->notes . "\n\n" : '') . "[DISPUTE " . now()->format('d/m/Y H:i') . "] " . $request->input('reason'),
        ]);

        return back()->with('success', 'Your dispute has been submitted. Our team will review it shortly.');
    }

    private function authorizeCustomerOrder(Request $request, Order $order): void
    {
        if ($order->customer_id !== $request->user()->customer->id) {
            abort(403);
        }
    }
}
