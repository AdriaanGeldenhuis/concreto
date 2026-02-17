<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffPermissionMiddleware
{
    /**
     * Routes that staff members CAN access.
     * Admin users bypass this check entirely.
     * Everything not listed here is admin-only.
     */
    private const STAFF_ALLOWED_ROUTES = [
        // Dashboard
        'admin.dashboard',

        // Orders (view and manage, but not create)
        'admin.orders.index',
        'admin.orders.show',
        'admin.orders.assign-driver',
        'admin.orders.update-status',
        'admin.orders.record-payment',
        'admin.orders.resend-invoice',

        // Quotes (view and manage)
        'admin.quotes.index',
        'admin.quotes.create',
        'admin.quotes.store',
        'admin.quotes.show',
        'admin.quotes.send',
        'admin.quotes.pdf',
        'admin.quotes.convert',

        // Products (view only)
        'admin.products.index',

        // Categories (view only)
        'admin.categories.index',

        // Customers (view and update contact/company, not account settings)
        'admin.customers.index',
        'admin.customers.show',
        'admin.customers.update-contact',
        'admin.customers.update-company',

        // Tracking & Ops
        'admin.tracking.drivers',
        'admin.tracking.driver-detail',
        'admin.tracking.order',
        'admin.ops.index',

        // Delivery Areas (view only)
        'admin.delivery-areas.index',

        // Messages
        'admin.messages.index',
        'admin.messages.show',
        'admin.messages.reply',

        // Reviews
        'admin.reviews.index',
        'admin.reviews.approve',
        'admin.reviews.reject',

        // Reports (view only, no export)
        'admin.reports.index',

        // Finance (view only)
        'admin.accounts-receivable.index',
        'admin.invoices.index',
        'admin.invoices.download',
        'admin.payment-register.index',
        'admin.vat-report.index',
        'admin.profit-loss.index',

        // Bank Reconciliation (view only for staff)
        'admin.bank-reconciliation.index',
        'admin.bank-reconciliation.statement',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin has full access
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Staff needs permission check
        if ($user->role === 'staff') {
            $routeName = $request->route()?->getName();

            if ($routeName && !in_array($routeName, self::STAFF_ALLOWED_ROUTES, true)) {
                abort(403, 'You do not have permission to access this area. Contact your administrator.');
            }
        }

        return $next($request);
    }
}
