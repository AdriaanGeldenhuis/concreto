<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use App\Helpers\CsvHelper;
use Illuminate\Support\Facades\Mail;

class AccountsReceivableController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $query = Customer::with(['user', 'company'])
            ->where('type', 'ACCOUNT');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                  ->orWhereHas('company', fn($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('trading_as', 'like', "%{$search}%"));
            });
        }

        $customers = $query->orderBy('created_at', 'desc')
            ->with(['orders' => function ($q) {
                $q->where('status', 'DELIVERED')
                    ->whereDoesntHave('payments', fn($q2) => $q2->where('status', 'completed'))
                    ->with('invoice')
                    ->orderBy('updated_at', 'asc');
            }])
            ->get();

        // Build AR data for each customer (orders already eager-loaded)
        $arData = $customers->map(function ($customer) {
            $unpaidOrders = $customer->orders;

            $outstandingBalance = $unpaidOrders->sum('total');

            $oldestUnpaidDate = $unpaidOrders->first()?->updated_at;
            $daysOutstanding = $oldestUnpaidDate ? (int) now()->diffInDays($oldestUnpaidDate) : 0;

            // Age buckets
            $current = 0;
            $over30 = 0;
            $over60 = 0;
            $over90 = 0;

            foreach ($unpaidOrders as $order) {
                $days = (int) now()->diffInDays($order->updated_at);
                if ($days > 90) {
                    $over90 += (float) $order->total;
                } elseif ($days > 60) {
                    $over60 += (float) $order->total;
                } elseif ($days > 30) {
                    $over30 += (float) $order->total;
                } else {
                    $current += (float) $order->total;
                }
            }

            return (object) [
                'customer' => $customer,
                'outstanding_balance' => $outstandingBalance,
                'credit_limit' => (float) ($customer->credit_limit ?? 0),
                'available_credit' => $customer->available_credit,
                'unpaid_count' => $unpaidOrders->count(),
                'oldest_unpaid_date' => $oldestUnpaidDate,
                'days_outstanding' => $daysOutstanding,
                'is_overdue' => $daysOutstanding > 30,
                'current' => $current,
                'over30' => $over30,
                'over60' => $over60,
                'over90' => $over90,
                'oldest_invoice' => $unpaidOrders->first()?->invoice?->invoice_no,
            ];
        });

        // Filter overdue only
        if ($request->filled('overdue') && $request->overdue === '1') {
            $arData = $arData->filter(fn($item) => $item->is_overdue);
        }

        // Filter those with balance only
        if (!$request->filled('show_zero')) {
            $arData = $arData->filter(fn($item) => $item->outstanding_balance > 0);
        }

        // Sort by outstanding balance descending
        $sortField = $request->input('sort', 'outstanding_balance');
        $arData = match ($sortField) {
            'days' => $arData->sortByDesc('days_outstanding'),
            'name' => $arData->sortBy(fn($item) => $item->customer->user->name ?? ''),
            default => $arData->sortByDesc('outstanding_balance'),
        };

        // Summary totals
        $totalOutstanding = $arData->sum('outstanding_balance');
        $totalOverdue = $arData->where('is_overdue', true)->sum('outstanding_balance');
        $overdueCount = $arData->where('is_overdue', true)->count();
        $totalCurrent = $arData->sum('current');
        $totalOver30 = $arData->sum('over30');
        $totalOver60 = $arData->sum('over60');
        $totalOver90 = $arData->sum('over90');
        $accountCount = $arData->count();

        return view('admin.accounts-receivable.index', compact(
            'arData', 'totalOutstanding', 'totalOverdue', 'overdueCount',
            'totalCurrent', 'totalOver30', 'totalOver60', 'totalOver90', 'accountCount'
        ));
    }

    public function statement(Request $request, Customer $customer)
    {
        $from = $request->input('from', now()->subMonths(3)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $path = $this->invoiceService->generateStatement($customer, $from, $to);

        AuditLog::log('generated_statement', 'Customer', $customer->id, [
            'from' => $from,
            'to' => $to,
        ]);

        return response()->download($path, "Statement-{$customer->user->name}-{$from}-to-{$to}.pdf");
    }

    public function emailStatement(Request $request, Customer $customer)
    {
        $from = $request->input('from', now()->subMonths(3)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $customer->load('user');
        $path = $this->invoiceService->generateStatement($customer, $from, $to);
        $email = $customer->user->email;

        try {
            Mail::send('emails.statement', [
                'customer' => $customer,
                'from' => $from,
                'to' => $to,
            ], function ($message) use ($email, $path, $from, $to) {
                $message->to($email)
                    ->subject("Account Statement {$from} to {$to} - Concreto")
                    ->attach($path, ['as' => "Statement-{$from}-to-{$to}.pdf"]);
            });

            AuditLog::log('emailed_statement', 'Customer', $customer->id, [
                'email' => $email,
                'from' => $from,
                'to' => $to,
            ]);

            return back()->with('success', "Statement emailed to {$email}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to email statement: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $customers = Customer::with(['user', 'company', 'orders' => function ($q) {
                $q->where('status', 'DELIVERED')
                    ->whereDoesntHave('payments', fn($q2) => $q2->where('status', 'completed'));
            }])
            ->where('type', 'ACCOUNT')
            ->get();

        $filename = 'accounts-receivable-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($customers) {
            $handle = fopen('php://output', 'w');
            CsvHelper::writeBom($handle);
            fputcsv($handle, [
                'Customer', 'Email', 'Company', 'Type', 'Credit Limit',
                'Outstanding Balance', 'Available Credit', 'Unpaid Invoices',
                'Days Outstanding', 'Current (0-30)', '31-60 Days', '61-90 Days', '90+ Days', 'Overdue',
            ]);

            foreach ($customers as $customer) {
                $unpaidOrders = $customer->orders;

                $outstanding = $unpaidOrders->sum('total');
                $oldest = $unpaidOrders->sortBy('updated_at')->first();
                $days = $oldest ? (int) now()->diffInDays($oldest->updated_at) : 0;

                $current = $over30 = $over60 = $over90 = 0;
                foreach ($unpaidOrders as $order) {
                    $d = (int) now()->diffInDays($order->updated_at);
                    if ($d > 90) $over90 += (float) $order->total;
                    elseif ($d > 60) $over60 += (float) $order->total;
                    elseif ($d > 30) $over30 += (float) $order->total;
                    else $current += (float) $order->total;
                }

                CsvHelper::safePutCsv($handle, [
                    $customer->user->name,
                    $customer->user->email,
                    $customer->company?->name ?? '-',
                    $customer->type,
                    $customer->credit_limit ?? 0,
                    $outstanding,
                    $customer->available_credit ?? '-',
                    $unpaidOrders->count(),
                    $days,
                    $current,
                    $over30,
                    $over60,
                    $over90,
                    $days > 30 ? 'Yes' : 'No',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
