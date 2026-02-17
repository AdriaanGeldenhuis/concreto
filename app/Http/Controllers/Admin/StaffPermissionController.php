<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class StaffPermissionController extends Controller
{
    /**
     * All available admin route groups for permission management.
     */
    private const PERMISSION_GROUPS = [
        'Dashboard' => [
            'admin.dashboard' => 'View Dashboard',
        ],
        'Orders' => [
            'admin.orders.index' => 'View Orders',
            'admin.orders.show' => 'View Order Detail',
            'admin.orders.create' => 'Create Orders',
            'admin.orders.store' => 'Store Orders',
            'admin.orders.assign-driver' => 'Assign Driver',
            'admin.orders.update-status' => 'Update Order Status',
            'admin.orders.cancel' => 'Cancel Orders',
            'admin.orders.force-status' => 'Force Status Change',
            'admin.orders.record-payment' => 'Record Payments',
            'admin.orders.resend-invoice' => 'Resend Invoice',
            'admin.orders.refund' => 'Issue Refunds',
        ],
        'Quotes' => [
            'admin.quotes.index' => 'View Quotes',
            'admin.quotes.create' => 'Create Quotes',
            'admin.quotes.store' => 'Store Quotes',
            'admin.quotes.show' => 'View Quote Detail',
            'admin.quotes.send' => 'Send Quotes',
            'admin.quotes.pdf' => 'Download Quote PDF',
            'admin.quotes.convert' => 'Convert to Order',
        ],
        'Products' => [
            'admin.products.index' => 'View Products',
            'admin.products.create' => 'Create Products',
            'admin.products.store' => 'Store Products',
            'admin.products.edit' => 'Edit Products',
            'admin.products.update' => 'Update Products',
            'admin.products.destroy' => 'Delete Products',
        ],
        'Categories' => [
            'admin.categories.index' => 'View Categories',
            'admin.categories.store' => 'Create Categories',
            'admin.categories.update' => 'Update Categories',
            'admin.categories.destroy' => 'Delete Categories',
        ],
        'Customers' => [
            'admin.customers.index' => 'View Customers',
            'admin.customers.show' => 'View Customer Detail',
            'admin.customers.update' => 'Update Customer Settings',
            'admin.customers.update-contact' => 'Update Contact Info',
            'admin.customers.update-company' => 'Update Company Info',
        ],
        'Tracking & Ops' => [
            'admin.tracking.drivers' => 'Track Drivers',
            'admin.tracking.driver-detail' => 'Driver Detail',
            'admin.tracking.order' => 'Track Orders',
            'admin.ops.index' => 'Operations Board',
        ],
        'Delivery Areas' => [
            'admin.delivery-areas.index' => 'View Delivery Areas',
            'admin.delivery-areas.store' => 'Create Delivery Areas',
            'admin.delivery-areas.update' => 'Update Delivery Areas',
            'admin.delivery-areas.destroy' => 'Delete Delivery Areas',
        ],
        'Driver Management' => [
            'admin.drivers.index' => 'View Drivers',
            'admin.drivers.shifts' => 'View Shifts',
            'admin.drivers.salary' => 'Manage Salary',
            'admin.drivers.shifts.edit' => 'Edit Shifts',
        ],
        'Messages' => [
            'admin.messages.index' => 'View Messages',
            'admin.messages.show' => 'View Message Detail',
            'admin.messages.reply' => 'Reply to Messages',
            'admin.messages.destroy' => 'Delete Messages',
        ],
        'Reviews' => [
            'admin.reviews.index' => 'View Reviews',
            'admin.reviews.approve' => 'Approve Reviews',
            'admin.reviews.reject' => 'Reject Reviews',
            'admin.reviews.destroy' => 'Delete Reviews',
        ],
        'Promo Codes' => [
            'admin.promo-codes.index' => 'View Promo Codes',
            'admin.promo-codes.store' => 'Create Promo Codes',
            'admin.promo-codes.update' => 'Update Promo Codes',
            'admin.promo-codes.destroy' => 'Delete Promo Codes',
        ],
        'Finance' => [
            'admin.accounts-receivable.index' => 'View Accounts Receivable',
            'admin.accounts-receivable.export' => 'Export AR Data',
            'admin.accounts-receivable.statement' => 'Generate Statements',
            'admin.accounts-receivable.email-statement' => 'Email Statements',
            'admin.invoices.index' => 'View Invoices',
            'admin.invoices.export' => 'Export Invoices',
            'admin.invoices.download' => 'Download Invoices',
            'admin.payment-register.index' => 'View Payment Register',
            'admin.payment-register.export' => 'Export Payments',
            'admin.vat-report.index' => 'View VAT Report',
            'admin.vat-report.export' => 'Export VAT Report',
            'admin.profit-loss.index' => 'View Profit & Loss',
            'admin.profit-loss.export' => 'Export P&L',
            'admin.reports.index' => 'View Reports',
            'admin.reports.export' => 'Export Reports',
        ],
        'Banking' => [
            'admin.bank-accounts.index' => 'View Bank Accounts',
            'admin.bank-accounts.create' => 'Add Bank Accounts',
            'admin.bank-accounts.store' => 'Store Bank Accounts',
            'admin.bank-accounts.edit' => 'Edit Bank Accounts',
            'admin.bank-accounts.update' => 'Update Bank Accounts',
            'admin.bank-accounts.destroy' => 'Delete Bank Accounts',
            'admin.bank-accounts.import.show' => 'Import Statements',
            'admin.bank-accounts.import.preview' => 'Preview Imports',
            'admin.bank-accounts.import.confirm' => 'Confirm Imports',
            'admin.bank-reconciliation.index' => 'View Reconciliation',
            'admin.bank-reconciliation.match' => 'Match Transactions',
            'admin.bank-reconciliation.unmatch' => 'Unmatch Transactions',
            'admin.bank-reconciliation.auto-match' => 'Run Auto-match',
            'admin.bank-reconciliation.statement' => 'View Recon Statement',
            'admin.bank-reconciliation.statement.export' => 'Export Recon Statement',
        ],
        'Email Templates' => [
            'admin.email-templates.index' => 'View Email Templates',
            'admin.email-templates.edit' => 'Edit Email Templates',
            'admin.email-templates.update' => 'Update Email Templates',
        ],
        'System' => [
            'admin.settings.index' => 'View Settings',
            'admin.settings.update' => 'Update Settings',
            'admin.audit-logs.index' => 'View Audit Logs',
            'admin.notification-logs.index' => 'View Notification Logs',
            'admin.notification-logs.export' => 'Export Notification Logs',
            'admin.invoice-reminders.index' => 'View Invoice Reminders',
            'admin.invoice-reminders.send' => 'Send Reminders',
            'admin.invoice-reminders.send-bulk' => 'Send Bulk Reminders',
            'admin.recurring-orders.index' => 'View Recurring Orders',
            'admin.recurring-orders.show' => 'View Recurring Order Detail',
            'admin.recurring-orders.pause' => 'Pause Recurring Orders',
            'admin.recurring-orders.resume' => 'Resume Recurring Orders',
            'admin.order-templates.index' => 'View Order Templates',
            'admin.order-templates.show' => 'View Template Detail',
            'admin.order-templates.destroy' => 'Delete Templates',
            'admin.staff-permissions.index' => 'Manage Staff Permissions',
            'admin.staff-permissions.update' => 'Update Staff Permissions',
        ],
    ];

    public function index()
    {
        $staffUsers = User::where('role', 'staff')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Load custom permissions from settings (JSON stored per user)
        $staffUsers->each(function ($user) {
            $user->custom_permissions = json_decode($user->staff_permissions ?? '[]', true) ?: [];
        });

        $permissionGroups = self::PERMISSION_GROUPS;

        // Default permissions (the hardcoded STAFF_ALLOWED_ROUTES)
        $defaultPermissions = [
            'admin.dashboard',
            'admin.orders.index', 'admin.orders.show', 'admin.orders.assign-driver',
            'admin.orders.update-status', 'admin.orders.record-payment', 'admin.orders.resend-invoice',
            'admin.quotes.index', 'admin.quotes.create', 'admin.quotes.store', 'admin.quotes.show',
            'admin.quotes.send', 'admin.quotes.pdf', 'admin.quotes.convert',
            'admin.products.index', 'admin.categories.index',
            'admin.customers.index', 'admin.customers.show',
            'admin.customers.update-contact', 'admin.customers.update-company',
            'admin.tracking.drivers', 'admin.tracking.driver-detail', 'admin.tracking.order',
            'admin.ops.index', 'admin.delivery-areas.index',
            'admin.messages.index', 'admin.messages.show', 'admin.messages.reply',
            'admin.reviews.index', 'admin.reviews.approve', 'admin.reviews.reject',
            'admin.reports.index',
            'admin.accounts-receivable.index', 'admin.invoices.index', 'admin.invoices.download',
            'admin.payment-register.index', 'admin.vat-report.index', 'admin.profit-loss.index',
            'admin.bank-reconciliation.index', 'admin.bank-reconciliation.statement',
        ];

        return view('admin.staff-permissions.index', compact(
            'staffUsers', 'permissionGroups', 'defaultPermissions'
        ));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== 'staff') {
            return back()->with('error', 'Can only manage permissions for staff users.');
        }

        $permissions = $request->input('permissions', []);

        $user->staff_permissions = json_encode(array_values($permissions));
        $user->save();

        AuditLog::log('updated_staff_permissions', 'User', $user->id, [
            'permissions_count' => count($permissions),
            'permissions' => $permissions,
        ]);

        return back()->with('success', "Permissions updated for {$user->name}.");
    }
}
