<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
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

    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:5000',
        ]);

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
}
