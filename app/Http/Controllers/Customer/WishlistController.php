<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = Wishlist::where('customer_id', $request->user()->customer->id)
            ->with('product.category')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('customer.wishlist.index', compact('items'));
    }

    public function toggle(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $customer = $request->user()->customer;
        $existing = Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Removed from favourites.';
            $added = false;
        } else {
            Wishlist::create([
                'customer_id' => $customer->id,
                'product_id' => $request->product_id,
            ]);
            $message = 'Added to favourites!';
            $added = true;
        }

        if ($request->wantsJson()) {
            return response()->json(['added' => $added, 'message' => $message]);
        }
        return back()->with('success', $message);
    }
}
