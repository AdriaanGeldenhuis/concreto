<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $featuredProducts = Product::where('is_active', true)->where('in_stock', true)->take(6)->get();
        return view('public.home', compact('categories', 'featuredProducts'));
    }

    public function products()
    {
        $categories = Category::where('is_active', true)->with(['products' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }])->orderBy('sort_order')->get();

        return view('public.products', compact('categories'));
    }

    public function productDetail(Product $product)
    {
        return view('public.product-detail', compact('product'));
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function terms()
    {
        return view('public.terms');
    }

    public function privacy()
    {
        return view('public.privacy');
    }

    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:5000',
        ]);

        ContactMessage::create([
            'type' => 'contact',
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'message' => $request->message,
        ]);

        return redirect()->route('contact')->with('success', 'Thank you! Your message has been sent. We\'ll get back to you soon.');
    }

    public function requestQuote()
    {
        $products = Product::where('is_active', true)->where('in_stock', true)->orderBy('name')->get();
        return view('public.request-quote', compact('products'));
    }

    public function submitQuoteRequest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'products_needed' => 'required|string|max:5000',
            'delivery_address' => 'required|string|max:500',
        ]);

        ContactMessage::create([
            'type' => 'quote_request',
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'products_needed' => $request->products_needed,
            'delivery_address' => $request->delivery_address,
        ]);

        return redirect()->route('request-quote')->with('success', 'Thank you! Your quote request has been submitted. We\'ll get back to you within 24 hours.');
    }
}
