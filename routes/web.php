<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Customer;
use App\Http\Controllers\Driver;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Webhook\YocoWebhookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Serve uploaded files via /media/ to bypass server blocking /storage/
Route::get('/media/{path}', function (string $path) {
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    $file = Storage::disk('public')->path($path);
    $mime = mime_content_type($file) ?: 'application/octet-stream';
    return response()->file($file, ['Content-Type' => $mime]);
})->where('path', '.*')->name('media.serve');

// Public pages
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/products', [PublicController::class, 'products'])->name('products');
Route::get('/products/{product}', [PublicController::class, 'productDetail'])->name('products.show');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'submitContact'])->name('contact.submit');
Route::get('/terms', [PublicController::class, 'terms'])->name('terms');
Route::get('/privacy', [PublicController::class, 'privacy'])->name('privacy');
Route::get('/request-quote', [PublicController::class, 'requestQuote'])->name('request-quote');
Route::post('/request-quote', [PublicController::class, 'submitQuoteRequest'])->name('request-quote.submit');

// Cart (session-based, no auth needed to browse)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// Checkout (auth required)
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'placeOrder'])->name('checkout.place');
});

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Customer portal
Route::prefix('customer')->name('customer.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/', [Customer\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/orders', [Customer\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [Customer\OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [Customer\OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [Customer\OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/pay', [Customer\OrderController::class, 'pay'])->name('orders.pay');
    Route::post('/orders/{order}/pay', [Customer\OrderController::class, 'createPaymentSession'])->name('orders.pay.create');
    Route::get('/orders/{order}/payment-success', [Customer\OrderController::class, 'paymentSuccess'])->name('orders.payment-success');

    Route::get('/invoices', [Customer\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}/download', [Customer\InvoiceController::class, 'download'])->name('invoices.download');

    Route::get('/quotes', [Customer\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/{quote}', [Customer\QuoteController::class, 'show'])->name('quotes.show');
    Route::post('/quotes/{quote}/approve', [Customer\QuoteController::class, 'approve'])->name('quotes.approve');

    Route::get('/addresses', [Customer\AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [Customer\AddressController::class, 'store'])->name('addresses.store');
    Route::delete('/addresses/{address}', [Customer\AddressController::class, 'destroy'])->name('addresses.destroy');
});

// Driver portal
Route::prefix('driver')->name('driver.')->middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/', [Driver\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/jobs', [Driver\JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{order}', [Driver\JobController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{order}/accept', [Driver\JobController::class, 'accept'])->name('jobs.accept');
    Route::post('/jobs/{order}/loaded', [Driver\JobController::class, 'loaded'])->name('jobs.loaded');
    Route::post('/jobs/{order}/transit', [Driver\JobController::class, 'transit'])->name('jobs.transit');
    Route::post('/jobs/{order}/arrived', [Driver\JobController::class, 'arrived'])->name('jobs.arrived');
    Route::get('/jobs/{order}/signature', [Driver\JobController::class, 'signatureForm'])->name('jobs.signature');
    Route::post('/jobs/{order}/signature', [Driver\JobController::class, 'storeSignature'])->name('jobs.signature.store');
    Route::post('/jobs/{order}/location', [Driver\JobController::class, 'updateLocation'])->name('jobs.location');
});

// Admin backend
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/assign-driver', [Admin\OrderController::class, 'assignDriver'])->name('orders.assign-driver');
    Route::post('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/cancel', [Admin\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/resend-invoice', [Admin\OrderController::class, 'resendInvoice'])->name('orders.resend-invoice');

    Route::resource('products', Admin\ProductController::class)->except(['show']);
    Route::resource('categories', Admin\CategoryController::class)->except(['show', 'create', 'edit']);

    Route::get('/customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [Admin\CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}', [Admin\CustomerController::class, 'update'])->name('customers.update');

    Route::get('/quotes', [Admin\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/create', [Admin\QuoteController::class, 'create'])->name('quotes.create');
    Route::post('/quotes', [Admin\QuoteController::class, 'store'])->name('quotes.store');
    Route::post('/quotes/{quote}/send', [Admin\QuoteController::class, 'send'])->name('quotes.send');

    Route::get('/settings', [Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');

    Route::resource('users', Admin\UserController::class)->except(['show', 'destroy']);

    Route::get('/messages', [Admin\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{contactMessage}', [Admin\MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{contactMessage}/reply', [Admin\MessageController::class, 'reply'])->name('messages.reply');
    Route::delete('/messages/{contactMessage}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

    Route::get('/audit-logs', [Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
});

// Yoco Webhook (no CSRF)
Route::post('/webhooks/yoco', [YocoWebhookController::class, 'handle'])->name('webhooks.yoco');
