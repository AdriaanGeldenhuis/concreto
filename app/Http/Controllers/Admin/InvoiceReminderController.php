<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\InvoiceReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvoiceReminderController extends Controller
{
    public function index(Request $request)
    {
        $query = InvoiceReminder::with(['invoice.order.customer.user'])
            ->orderBy('sent_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('invoice', function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('order.customer.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('sent_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('sent_at', '<=', $request->to);
        }

        $reminders = $query->paginate(30);

        // Overdue invoices that haven't had a recent reminder
        $overdueInvoices = Invoice::with(['order.customer.user'])
            ->whereHas('order', function ($q) {
                $q->where('status', 'DELIVERED')
                  ->whereDoesntHave('payments', fn($q2) => $q2->where('status', 'completed'));
            })
            ->whereDoesntHave('reminders', function ($q) {
                $q->where('sent_at', '>=', now()->subDays(7));
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Stats
        $totalReminders = InvoiceReminder::count();
        $thisMonth = InvoiceReminder::whereMonth('sent_at', now()->month)->whereYear('sent_at', now()->year)->count();
        $overdueCount = $overdueInvoices->count();

        return view('admin.invoice-reminders.index', compact(
            'reminders', 'overdueInvoices', 'totalReminders', 'thisMonth', 'overdueCount'
        ));
    }

    public function send(Invoice $invoice)
    {
        $invoice->load('order.customer.user');
        $customer = $invoice->order->customer;
        $email = $customer->user->email;

        $reminderNumber = $invoice->reminders()->count() + 1;

        try {
            Mail::send('emails.invoice-reminder', [
                'invoice' => $invoice,
                'customer' => $customer,
                'reminderNumber' => $reminderNumber,
            ], function ($message) use ($email, $invoice, $reminderNumber) {
                $message->to($email)
                    ->subject("Payment Reminder #{$reminderNumber} - Invoice {$invoice->invoice_no}");
            });

            InvoiceReminder::create([
                'invoice_id' => $invoice->id,
                'reminder_number' => $reminderNumber,
                'sent_at' => now(),
            ]);

            AuditLog::log('sent_invoice_reminder', 'Invoice', $invoice->id, [
                'email' => $email,
                'reminder_number' => $reminderNumber,
            ]);

            return back()->with('success', "Reminder #{$reminderNumber} sent to {$email} for invoice {$invoice->invoice_no}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send reminder: ' . $e->getMessage());
        }
    }

    public function sendBulk(Request $request)
    {
        $invoiceIds = $request->input('invoice_ids', []);

        if (empty($invoiceIds)) {
            return back()->with('error', 'No invoices selected.');
        }

        $invoices = Invoice::with('order.customer.user')->whereIn('id', $invoiceIds)->get();
        $sent = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            $customer = $invoice->order?->customer;
            if (!$customer) {
                $failed++;
                continue;
            }

            $email = $customer->user->email;
            $reminderNumber = $invoice->reminders()->count() + 1;

            try {
                Mail::send('emails.invoice-reminder', [
                    'invoice' => $invoice,
                    'customer' => $customer,
                    'reminderNumber' => $reminderNumber,
                ], function ($message) use ($email, $invoice, $reminderNumber) {
                    $message->to($email)
                        ->subject("Payment Reminder #{$reminderNumber} - Invoice {$invoice->invoice_no}");
                });

                InvoiceReminder::create([
                    'invoice_id' => $invoice->id,
                    'reminder_number' => $reminderNumber,
                    'sent_at' => now(),
                ]);

                $sent++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        AuditLog::log('sent_bulk_invoice_reminders', 'Invoice', null, [
            'sent' => $sent,
            'failed' => $failed,
        ]);

        return back()->with('success', "Bulk reminders sent: {$sent} successful, {$failed} failed.");
    }
}
