<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private InvoiceService $invoiceService,
    ) {}

    public function index(Request $request)
    {
        $query = Order::with(['customer.user', 'driver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer.user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        $statuses = Order::STATUSES;

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load(['customer.user', 'items.product', 'deliveryAddress', 'driver', 'payments', 'invoice', 'proofOfDelivery', 'driverLocations']);
        $drivers = User::where('role', 'driver')->where('is_active', true)->get();
        return view('admin.orders.show', compact('order', 'drivers'));
    }

    public function assignDriver(Request $request, Order $order)
    {
        $request->validate(['driver_id' => 'required|exists:users,id']);

        $driver = User::findOrFail($request->driver_id);
        if ($driver->role !== 'driver') {
            return back()->with('error', 'Selected user is not a driver.');
        }

        $this->orderService->assignDriver($order, $driver->id);

        return back()->with('success', 'Driver assigned.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Order::STATUSES),
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->orderService->updateStatus($order, $request->status, $request->reason);
            return back()->with('success', 'Status updated.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function forceStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Order::STATUSES),
            'reason' => 'required|string|max:1000',
        ]);

        $this->orderService->forceStatus($order, $request->status, $request->reason);
        return back()->with('success', 'Status force-updated with audit trail.');
    }

    public function cancel(Request $request, Order $order)
    {
        try {
            $this->orderService->cancelOrder($order, $request->input('reason'));
            return back()->with('success', 'Order cancelled.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function resendInvoice(Order $order)
    {
        if (!$order->invoice) {
            $invoice = $this->invoiceService->generate($order);
        } else {
            $invoice = $order->invoice;
        }

        $order->load('customer.user');
        $email = $order->customer->user->email;
        $pdfPath = $this->invoiceService->getPdfPath($invoice);

        try {
            Mail::send('emails.invoice', ['order' => $order, 'invoice' => $invoice], function ($message) use ($email, $invoice, $pdfPath) {
                $message->to($email)
                    ->subject("Invoice {$invoice->invoice_no} - Concreto")
                    ->attach($pdfPath, ['as' => "{$invoice->invoice_no}.pdf"]);
            });

            $invoice->update(['emailed_at' => now()]);
            AuditLog::log('resent_invoice', 'Invoice', $invoice->id);

            return back()->with('success', 'Invoice re-sent.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send invoice: ' . $e->getMessage());
        }
    }
}
