<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceReminder;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Send reminders for unpaid invoices older than 7 days';

    public function handle(): int
    {
        $unpaidInvoices = Invoice::whereHas('order', function ($q) {
                $q->where('status', 'DELIVERED')
                  ->whereDoesntHave('payments', fn($p) => $p->where('status', 'completed'));
            })
            ->where('created_at', '<', now()->subDays(7))
            ->with(['order.customer.user'])
            ->get();

        $siteSettings = Setting::getAll();
        $sent = 0;

        foreach ($unpaidInvoices as $invoice) {
            // Check how many reminders already sent
            $reminderCount = $invoice->reminders()->count();
            if ($reminderCount >= 3) continue; // Max 3 reminders

            // Check last reminder was at least 7 days ago
            $lastReminder = $invoice->reminders()->latest('sent_at')->first();
            if ($lastReminder && $lastReminder->sent_at->diffInDays(now()) < 7) continue;

            $email = $invoice->order->customer->user->email;
            $reminderNumber = $reminderCount + 1;
            $daysOverdue = max(0, (int) $invoice->created_at->diffInDays(now()));
            $amountDue = $invoice->order->total ?? 0;
            $urgency = $reminderNumber >= 3 ? 'high' : ($reminderNumber >= 2 ? 'medium' : 'low');

            try {
                Mail::send('emails.invoice-reminder', [
                    'invoice' => $invoice,
                    'customer' => $invoice->order->customer,
                    'reminderNumber' => $reminderNumber,
                    'siteSettings' => $siteSettings,
                    'daysOverdue' => $daysOverdue,
                    'amountDue' => $amountDue,
                    'urgency' => $urgency,
                ], function ($message) use ($email, $invoice, $urgency, $reminderNumber) {
                    $prefix = $urgency === 'high' ? 'URGENT: ' : '';
                    $message->to($email)
                        ->subject("{$prefix}Payment Reminder #{$reminderNumber} - Invoice {$invoice->invoice_no}");
                });

                InvoiceReminder::create([
                    'invoice_id' => $invoice->id,
                    'reminder_number' => $reminderNumber,
                    'sent_at' => now(),
                ]);
                $sent++;
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for {$invoice->invoice_no}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sent} invoice reminders.");
        return self::SUCCESS;
    }
}
