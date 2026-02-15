<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
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

        // Handle promo code
        $discount = 0;
        $promoCode = null;
        $promoError = null;
        $promoCodeInput = session('promo_code', $request->input('promo_code'));

        if ($promoCodeInput) {
            $promo = PromoCode::where('code', strtoupper($promoCodeInput))->first();
            if ($promo && $promo->isValid($subtotal, $customer->id)) {
                $discount = $promo->calculateDiscount($subtotal);
                $promoCode = $promo;
                session(['promo_code' => $promo->code]);
            } else {
                $promoError = $promo ? 'This promo code is not valid for your order.' : 'Invalid promo code.';
                session()->forget('promo_code');
            }
        }

        $taxableAmount = max(0, $subtotal - $discount);
        $vat = round($taxableAmount * 0.15, 2);
        $total = $taxableAmount + $vat;

        return view('public.checkout', compact('items', 'subtotal', 'vat', 'total', 'addresses', 'customer', 'discount', 'promoCode', 'promoError'));
    }

    public function applyPromo(Request $request)
    {
        $request->validate(['promo_code' => 'required|string|max:50']);
        session(['promo_code' => strtoupper($request->promo_code)]);
        return redirect()->route('checkout');
    }

    public function removePromo()
    {
        session()->forget('promo_code');
        return redirect()->route('checkout');
    }

    public function storeAddress(Request $request)
    {
        $request->validate([
            'label' => 'nullable|string|max:50',
            'line1' => 'required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
        ]);

        $customer = $this->getOrCreateCustomer($request);
        $address = $customer->addresses()->create($request->only(['label', 'line1', 'line2', 'city', 'province', 'postal_code']));

        if (!$customer->default_address_id) {
            $customer->update(['default_address_id' => $address->id]);
        }

        return redirect()->route('checkout')->with('success', 'Address added.');
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

        // Verify address belongs to this customer
        $address = \App\Models\Address::find($request->delivery_address_id);
        if (!$address || $address->customer_id !== $customer->id) {
            return redirect()->route('checkout')->with('error', 'Invalid delivery address. Please select one of your addresses.');
        }

        // Build items array from cart session (skip invalid products)
        $items = [];
        $subtotal = 0;
        foreach ($cart as $productId => $qty) {
            $product = Product::find($productId);
            if (!$product) continue;
            $subtotal += round($product->price * $qty, 2);
            $items[] = [
                'product_id' => $productId,
                'qty' => $qty,
            ];
        }

        if (empty($items)) {
            return redirect()->route('cart.index')->with('error', 'No valid products in your order.');
        }

        $data = $request->all();
        $data['items'] = $items;

        // Apply promo code if stored in session
        $promoCodeStr = session('promo_code');
        if ($promoCodeStr) {
            $promo = PromoCode::where('code', $promoCodeStr)->first();
            if ($promo && $promo->isValid($subtotal, $customer->id)) {
                $data['promo_code_id'] = $promo->id;
                $data['discount_amount'] = $promo->calculateDiscount($subtotal);
            }
        }

        try {
            $orderService = app(\App\Services\OrderService::class);
            $order = $orderService->createOrder($customer, $data);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Order creation failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('checkout')->with('error', 'Something went wrong placing your order. Please try again.');
        }

        // Record promo code usage
        if (!empty($data['promo_code_id'])) {
            $promo = PromoCode::find($data['promo_code_id']);
            if ($promo) {
                PromoCodeUsage::create([
                    'promo_code_id' => $promo->id,
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                    'discount_applied' => $data['discount_amount'],
                ]);
                $promo->increment('times_used');
            }
        }

        // Clear the cart and promo code
        session()->forget(['cart', 'promo_code']);

        // Redirect based on user role
        $user = $request->user();

        if ($user->role === 'customer') {
            if ($order->status === 'PENDING_PAYMENT') {
                return redirect()->route('customer.orders.pay', $order)
                    ->with('success', 'Order placed! Please complete payment.');
            }
            return redirect()->route('customer.orders.show', $order)
                ->with('success', 'Order placed successfully!');
        }

        // Admin/staff users → redirect to admin order view
        if (in_array($user->role, ['admin', 'staff'])) {
            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Order #' . $order->order_number . ' placed successfully!');
        }

        // Fallback
        return redirect()->route('cart.index')
            ->with('success', 'Order #' . $order->order_number . ' placed successfully!');
    }
}
