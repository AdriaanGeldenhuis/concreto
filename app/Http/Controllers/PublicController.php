<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\EmailLog;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PublicController extends Controller
{
    public function home()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $featuredProducts = Product::where('is_active', true)->where('in_stock', true)->take(6)->get();
        return view('public.home', compact('categories', 'featuredProducts'));
    }

    public function products(Request $request)
    {
        $query = Product::where('is_active', true);

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // In stock only
        if ($request->boolean('in_stock_only')) {
            $query->where('in_stock', true);
        }

        // Sort
        $sort = $request->input('sort', 'name');
        $query->orderBy(match ($sort) {
            'price_low' => 'price',
            'price_high' => 'price',
            'newest' => 'created_at',
            default => 'name',
        }, match ($sort) {
            'price_high', 'newest' => 'desc',
            default => 'asc',
        });

        $products = $query->with('category')->paginate(24)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('public.products', compact('products', 'categories'));
    }

    public function productDetail(Product $product)
    {
        $product->load(['approvedReviews.customer.user', 'bulkPricingTiers', 'category']);

        // Related products from same category
        $relatedProducts = Product::where('is_active', true)
            ->where('in_stock', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->take(4)
            ->get();

        $avgRating = $product->approvedReviews->avg('rating');
        $reviewCount = $product->approvedReviews->count();

        return view('public.product-detail', compact('product', 'relatedProducts', 'avgRating', 'reviewCount'));
    }

    public function sitemap()
    {
        $products = Product::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        return response()->view('public.sitemap', compact('products', 'categories'))
            ->header('Content-Type', 'text/xml');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function submitContact(Request $request)
    {
        // Honeypot spam check
        if ($request->filled('website')) {
            return back()->with('success', 'Thank you! Your message has been sent.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // Save to database
        ContactMessage::create([
            'type' => 'contact',
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'message' => ($request->subject ? '[' . $request->subject . '] ' : '') . $request->message,
        ]);

        // Also try to send email
        $adminEmail = Setting::get('contact_email', 'orders@concreto.co.za');
        try {
            Mail::raw(
                "New contact form submission:\n\nName: {$request->name}\nEmail: {$request->email}\nPhone: {$request->phone}\n\nMessage:\n{$request->message}",
                function ($mail) use ($adminEmail, $request) {
                    $mail->to($adminEmail)
                        ->subject('Contact Form: ' . $request->name)
                        ->replyTo($request->email, $request->name);
                }
            );

            EmailLog::create([
                'to_email' => $adminEmail,
                'subject' => 'Contact Form: ' . $request->name,
                'template' => 'contact_form',
                'status' => 'sent',
            ]);
        } catch (\Exception $e) {
            EmailLog::create([
                'to_email' => $adminEmail,
                'subject' => 'Contact Form: ' . $request->name,
                'template' => 'contact_form',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Thank you! Your message has been sent. We\'ll get back to you soon.');
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

    public function submitQuoteRequest(Request $request)
    {
        // Honeypot spam check
        if ($request->filled('website')) {
            return redirect()->route('request-quote')->with('success', 'Thank you!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
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

        // Also try to send email notification
        $adminEmail = Setting::get('contact_email', 'orders@concreto.co.za');
        try {
            Mail::raw(
                "New quote request:\n\nName: {$request->name}\nEmail: {$request->email}\nPhone: {$request->phone}\n\nProducts needed:\n{$request->products_needed}\n\nDelivery address: {$request->delivery_address}",
                function ($mail) use ($adminEmail, $request) {
                    $mail->to($adminEmail)
                        ->subject('Quote Request: ' . $request->name)
                        ->replyTo($request->email, $request->name);
                }
            );
        } catch (\Exception $e) {
            // Silently fail - the message is saved in DB
        }

        return redirect()->route('request-quote')->with('success', 'Thank you! Your quote request has been submitted. We\'ll get back to you within 24 hours.');
    }
}
