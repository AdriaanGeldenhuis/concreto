<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'nullable|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $customer = $request->user()->customer;

        // Check for existing review
        $existing = ProductReview::where('product_id', $request->product_id)
            ->where('customer_id', $customer->id)
            ->where('order_id', $request->order_id)
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already reviewed this product for this order.');
        }

        ProductReview::create([
            'product_id' => $request->product_id,
            'customer_id' => $customer->id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Thank you for your review! It will be published after approval.');
    }
}
