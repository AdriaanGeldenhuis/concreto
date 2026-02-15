<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get or create a customer profile for the current user.
     */
    private function getOrCreateCustomer(Request $request): Customer
    {
        $user = $request->user();
        $customer = $user->customer;

        if (!$customer) {
            $customer = Customer::create([
                'user_id' => $user->id,
                'type' => 'COD',
            ]);
            // Refresh the relationship
            $user->load('customer');
        }

        return $customer;
    }
    public function index()
    {
        $cart = session('cart', []);
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $items = [];
        $subtotal = 0;
        foreach ($cart as $productId => $qty) {
            if (!isset($products[$productId])) continue;
            $product = $products[$productId];
            $lineTotal = round($product->price * $qty, 2);
            $items[] = [
                'product' => $product,
                'qty' => $qty,
                'line_total' => $lineTotal,
            ];
            $subtotal += $lineTotal;
        }

        $vat = round($subtotal * 0.15, 2);
        $total = $subtotal + $vat;

        return view('public.cart', compact('items', 'subtotal', 'vat', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|numeric|min:0.01',
        ]);

        $product = Product::where('id', $request->product_id)
            ->where('is_active', true)
            ->where('in_stock', true)
            ->firstOrFail();

        $cart = session('cart', []);
        $existing = $cart[$product->id] ?? 0;
        $cart[$product->id] = $existing + $request->qty;
        session(['cart' => $cart]);

        if ($request->wantsJson()) {
            return response()->json([
                'count' => count($cart),
                'message' => $product->name . ' added to order',
            ]);
        }

        return redirect()->back()->with('success', $product->name . ' added to order!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|numeric|min:0',
        ]);

        $cart = session('cart', []);

        if ($request->qty <= 0) {
            unset($cart[$request->product_id]);
        } else {
            $cart[$request->product_id] = $request->qty;
        }

        session(['cart' => $cart]);

        if ($request->wantsJson()) {
            return response()->json(['count' => count($cart)]);
        }

        return redirect()->route('cart.index');
    }

    public function remove(Request $request)
    {
        $cart = session('cart', []);
        unset($cart[$request->product_id]);
        session(['cart' => $cart]);

        if ($request->wantsJson()) {
            return response()->json(['count' => count($cart)]);
        }

        return redirect()->route('cart.index')->with('success', 'Item removed from order.');
    }

    public function count()
    {
        $cart = session('cart', []);
        return response()->json(['count' => count($cart)]);
    }

    public function checkout(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your order is empty.');
        }

        $customer = $this->getOrCreateCustomer($request);
        $addresses = $customer->addresses;

        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $items = [];
        $subtotal = 0;
        foreach ($cart as $productId => $qty) {
            if (!isset($products[$productId])) continue;
            $product = $products[$productId];
            $lineTotal = round($product->price * $qty, 2);
            $items[] = [
                'product' => $product,
                'qty' => $qty,
                'line_total' => $lineTotal,
            ];
            $subtotal += $lineTotal;
        }

        $vat = round($subtotal * 0.15, 2);
        $total = $subtotal + $vat;

        return view('public.checkout', compact('items', 'subtotal', 'vat', 'total', 'addresses', 'customer'));
    }

    public function placeOrder(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your order is empty.');
        }

        $request->validate([
            'delivery_address_id' => 'required|exists:addresses,id',
            'scheduled_date' => 'nullable|date|after:today',
            'scheduled_time_window' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        $customer = $this->getOrCreateCustomer($request);

        // Build items array from cart session
        $items = [];
        foreach ($cart as $productId => $qty) {
            $items[] = [
                'product_id' => $productId,
                'qty' => $qty,
            ];
        }

        $data = $request->all();
        $data['items'] = $items;

        $orderService = app(\App\Services\OrderService::class);
        $order = $orderService->createOrder($customer, $data);

        // Clear the cart
        session()->forget('cart');

        if ($order->status === 'PENDING_PAYMENT') {
            return redirect()->route('customer.orders.pay', $order)
                ->with('success', 'Order placed! Please complete payment.');
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Order placed successfully!');
    }
}
