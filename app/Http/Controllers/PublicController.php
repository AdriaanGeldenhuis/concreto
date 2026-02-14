<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;

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

    public function requestQuote()
    {
        $products = Product::where('is_active', true)->where('in_stock', true)->orderBy('name')->get();
        return view('public.request-quote', compact('products'));
    }
}
