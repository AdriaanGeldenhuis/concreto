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
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');

// Cart (session-based, no auth needed to browse)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// Checkout (auth required, any role)
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'placeOrder'])->name('checkout.place');
    Route::post('/checkout/apply-promo', [CartController::class, 'applyPromo'])->name('checkout.apply-promo');
    Route::post('/checkout/remove-promo', [CartController::class, 'removePromo'])->name('checkout.remove-promo');
    Route::post('/checkout/add-address', [CartController::class, 'storeAddress'])->name('checkout.add-address');
    Route::post('/checkout/save-company', [CartController::class, 'saveCompany'])->name('checkout.save-company');
});

// Auth (rate limited)
Route::middleware(['guest', 'throttle:login'])->group(function () {
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
    Route::post('/orders/{order}/pay', [Customer\OrderController::class, 'createPaymentSession'])->name('orders.pay.create')->middleware('throttle:payment');
    Route::get('/orders/{order}/payment-success', [Customer\OrderController::class, 'paymentSuccess'])->name('orders.payment-success');
    Route::get('/orders/{order}/reorder', [Customer\OrderController::class, 'reorder'])->name('orders.reorder');
    Route::post('/orders/{order}/dispute', [Customer\OrderController::class, 'dispute'])->name('orders.dispute');

    Route::get('/invoices', [Customer\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}/download', [Customer\InvoiceController::class, 'download'])->name('invoices.download');

    Route::get('/quotes', [Customer\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/{quote}', [Customer\QuoteController::class, 'show'])->name('quotes.show');
    Route::post('/quotes/{quote}/approve', [Customer\QuoteController::class, 'approve'])->name('quotes.approve');

    Route::get('/account', [Customer\AccountController::class, 'index'])->name('account');
    Route::post('/account/company', [Customer\AccountController::class, 'updateCompany'])->name('account.update-company');

    Route::get('/addresses', [Customer\AccountController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [Customer\AddressController::class, 'store'])->name('addresses.store');
    Route::delete('/addresses/{address}', [Customer\AddressController::class, 'destroy'])->name('addresses.destroy');

    Route::get('/statement', [Customer\InvoiceController::class, 'statement'])->name('statement');

    // Wishlist
    Route::get('/wishlist', [Customer\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [Customer\WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Reviews
    Route::post('/reviews', [Customer\ReviewController::class, 'store'])->name('reviews.store');

    // Order Templates & Recurring
    Route::get('/templates', [Customer\OrderTemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates', [Customer\OrderTemplateController::class, 'store'])->name('templates.store');
    Route::post('/templates/from-order', [Customer\OrderTemplateController::class, 'saveFromOrder'])->name('templates.from-order');
    Route::delete('/templates/{template}', [Customer\OrderTemplateController::class, 'destroy'])->name('templates.destroy');
    Route::post('/recurring', [Customer\OrderTemplateController::class, 'storeRecurring'])->name('recurring.store');
    Route::post('/recurring/{recurringOrder}/cancel', [Customer\OrderTemplateController::class, 'cancelRecurring'])->name('recurring.cancel');
});

// Driver portal
Route::prefix('driver')->name('driver.')->middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/', [Driver\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/clock-in', [Driver\DashboardController::class, 'clockIn'])->name('clock-in');
    Route::post('/clock-out', [Driver\DashboardController::class, 'clockOut'])->name('clock-out');

    Route::get('/jobs', [Driver\JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{order}', [Driver\JobController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{order}/accept', [Driver\JobController::class, 'accept'])->name('jobs.accept');
    Route::post('/jobs/{order}/loaded', [Driver\JobController::class, 'loaded'])->name('jobs.loaded');
    Route::post('/jobs/{order}/transit', [Driver\JobController::class, 'transit'])->name('jobs.transit');
    Route::post('/jobs/{order}/arrived', [Driver\JobController::class, 'arrived'])->name('jobs.arrived');
    Route::get('/jobs/{order}/signature', [Driver\JobController::class, 'signatureForm'])->name('jobs.signature');
    Route::post('/jobs/{order}/signature', [Driver\JobController::class, 'storeSignature'])->name('jobs.signature.store');
    Route::post('/jobs/{order}/location', [Driver\JobController::class, 'updateLocation'])->name('jobs.location')->middleware('throttle:tracking');
});

// Admin backend
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/assign-driver', [Admin\OrderController::class, 'assignDriver'])->name('orders.assign-driver');
    Route::post('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/cancel', [Admin\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/force-status', [Admin\OrderController::class, 'forceStatus'])->name('orders.force-status');
    Route::post('/orders/{order}/resend-invoice', [Admin\OrderController::class, 'resendInvoice'])->name('orders.resend-invoice');
    Route::post('/orders/{order}/record-payment', [Admin\OrderController::class, 'recordPayment'])->name('orders.record-payment');
    Route::post('/orders/{order}/refund', [Admin\OrderController::class, 'refund'])->name('orders.refund');

    Route::resource('products', Admin\ProductController::class)->except(['show']);
    Route::resource('categories', Admin\CategoryController::class)->except(['show', 'create', 'edit']);

    Route::get('/customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [Admin\CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}', [Admin\CustomerController::class, 'update'])->name('customers.update');
    Route::put('/customers/{customer}/company', [Admin\CustomerController::class, 'updateCompany'])->name('customers.update-company');
    Route::put('/customers/{customer}/contact', [Admin\CustomerController::class, 'updateContact'])->name('customers.update-contact');

    Route::get('/quotes', [Admin\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/create', [Admin\QuoteController::class, 'create'])->name('quotes.create');
    Route::post('/quotes', [Admin\QuoteController::class, 'store'])->name('quotes.store');
    Route::get('/quotes/{quote}', [Admin\QuoteController::class, 'show'])->name('quotes.show');
    Route::post('/quotes/{quote}/send', [Admin\QuoteController::class, 'send'])->name('quotes.send');
    Route::get('/quotes/{quote}/pdf', [Admin\QuoteController::class, 'downloadPdf'])->name('quotes.pdf');
    Route::post('/quotes/{quote}/convert', [Admin\QuoteController::class, 'convertToOrder'])->name('quotes.convert');

    Route::get('/settings', [Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');

    Route::resource('users', Admin\UserController::class)->except(['show', 'destroy']);

    Route::get('/messages', [Admin\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{contactMessage}', [Admin\MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{contactMessage}/reply', [Admin\MessageController::class, 'reply'])->name('messages.reply');
    Route::delete('/messages/{contactMessage}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

    Route::get('/audit-logs', [Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/ops', [Admin\OpsController::class, 'index'])->name('ops.index');

    // Tracking
    Route::get('/tracking/drivers', [Admin\TrackingController::class, 'drivers'])->name('tracking.drivers');
    Route::get('/tracking/drivers/{driver}', [Admin\TrackingController::class, 'driverDetail'])->name('tracking.driver-detail');
    Route::get('/tracking/orders/{order}', [Admin\TrackingController::class, 'orderTracking'])->name('tracking.order');

    // Delivery Areas
    Route::get('/delivery-areas', [Admin\DeliveryAreaController::class, 'index'])->name('delivery-areas.index');
    Route::post('/delivery-areas', [Admin\DeliveryAreaController::class, 'store'])->name('delivery-areas.store');
    Route::put('/delivery-areas/{deliveryArea}', [Admin\DeliveryAreaController::class, 'update'])->name('delivery-areas.update');
    Route::delete('/delivery-areas/{deliveryArea}', [Admin\DeliveryAreaController::class, 'destroy'])->name('delivery-areas.destroy');

    // Reports & Export
    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [Admin\ReportController::class, 'export'])->name('reports.export');

    // Promo Codes
    Route::get('/promo-codes', [Admin\PromoCodeController::class, 'index'])->name('promo-codes.index');
    Route::post('/promo-codes', [Admin\PromoCodeController::class, 'store'])->name('promo-codes.store');
    Route::put('/promo-codes/{promoCode}', [Admin\PromoCodeController::class, 'update'])->name('promo-codes.update');
    Route::delete('/promo-codes/{promoCode}', [Admin\PromoCodeController::class, 'destroy'])->name('promo-codes.destroy');

    // Driver Management
    Route::get('/drivers', [Admin\DriverManagementController::class, 'index'])->name('drivers.index');
    Route::get('/drivers/{driver}/shifts', [Admin\DriverManagementController::class, 'shifts'])->name('drivers.shifts');
    Route::post('/drivers/{driver}/salary', [Admin\DriverManagementController::class, 'updateSalary'])->name('drivers.salary');
    Route::put('/drivers/shifts/{shift}', [Admin\DriverManagementController::class, 'editShift'])->name('drivers.shifts.edit');

    // Reviews
    Route::get('/reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/approve', [Admin\ReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [Admin\ReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('/reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Email Templates
    Route::get('/email-templates', [Admin\EmailTemplateController::class, 'index'])->name('email-templates.index');
    Route::get('/email-templates/edit', [Admin\EmailTemplateController::class, 'edit'])->name('email-templates.edit');
    Route::post('/email-templates', [Admin\EmailTemplateController::class, 'update'])->name('email-templates.update');
});

// Health check (no auth)
Route::get('/health', \App\Http\Controllers\HealthController::class)->name('health');

// Yoco Webhook (no CSRF, rate limited)
Route::post('/webhooks/yoco', [YocoWebhookController::class, 'handle'])->name('webhooks.yoco')->middleware('throttle:webhook');
