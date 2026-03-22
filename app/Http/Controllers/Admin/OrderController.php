<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\NotificationService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Helpers\CsvHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private InvoiceService $invoiceService,
        private NotificationService $notificationService,
    ) {}

    public function create()
    {
        $customers = Customer::with(['user', 'addresses'])->orderBy('created_at', 'desc')->get();
        $products = Product::where('is_active', true)->where('in_stock', true)->orderBy('name')->get();
        $drivers = User::where('role', 'driver')->where('is_active', true)->orderBy('name')->get();

        return view('admin.orders.create', compact('customers', 'products', 'drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_address_id' => 'required|exists:addresses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:2000',
            'scheduled_date' => 'nullable|date',
            'scheduled_time_window' => 'nullable|string|max:50',
            'driver_id' => 'nullable|exists:users,id',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        try {
            $order = $this->orderService->createOrder($customer, [
                'delivery_address_id' => $request->delivery_address_id,
                'items' => $request->items,
                'notes' => $request->notes,
                'scheduled_date' => $request->scheduled_date,
                'scheduled_time_window' => $request->scheduled_time_window,
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->filled('driver_id')) {
            try {
                $this->orderService->assignDriver($order, $request->driver_id);
            } catch (\Exception $e) {
                // Order still created, driver assignment failed
            }
        }

        AuditLog::log('admin_created_order', 'Order', $order->id, [
            'customer_id' => $customer->id,
            'total' => $order->total,
            'created_by' => auth()->user()->name,
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', "Order {$order->order_number} created successfully.");
    }

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

        // Advanced filters
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'paid') {
                $query->whereHas('payments', fn($q) => $q->where('status', 'completed'));
            } elseif ($request->payment_status === 'unpaid') {
                $query->whereDoesntHave('payments', fn($q) => $q->where('status', 'completed'));
            }
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('min_total')) {
            $query->where('total', '>=', $request->min_total);
        }
        if ($request->filled('max_total')) {
            $query->where('total', '<=', $request->max_total);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        $statuses = Order::STATUSES;
        $drivers = User::where('role', 'driver')->where('is_active', true)->orderBy('name')->get();

        return view('admin.orders.index', compact('orders', 'statuses', 'drivers'));
    }

    public function show(Order $order)
    {
        $order->load(['customer.user', 'items.product', 'deliveryAddress', 'driver', 'payments', 'invoice', 'proofOfDelivery', 'driverLocations', 'promoCode']);
        $drivers = User::where('role', 'driver')->where('is_active', true)->get();
        $auditLogs = AuditLog::where('entity', 'Order')
            ->where('entity_id', $order->id)
            ->with('actor')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.orders.show', compact('order', 'drivers', 'auditLogs'));
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

    public function recordPayment(Request $request, Order $order)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'provider' => 'required|in:eft,cash,card_manual,other',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment = DB::transaction(function () use ($request, $order) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'provider' => $request->provider,
                'reference' => $request->reference,
                'amount' => $request->amount,
                'status' => 'completed',
                'notes' => $request->notes,
                'recorded_by' => auth()->user()->name,
            ]);

            AuditLog::log('manual_payment_recorded', 'Payment', $payment->id, [
                'order_id' => $order->id,
                'amount' => $request->amount,
                'provider' => $request->provider,
            ]);

            if ($order->status === 'PENDING_PAYMENT') {
                $totalPaid = $order->payments()->where('status', 'completed')->sum('amount');
                if ($totalPaid >= (float) $order->total) {
                    $order->update(['status' => 'PLACED']);
                    AuditLog::log('payment_completed', 'Order', $order->id, [
                        'total_paid' => $totalPaid,
                        'provider' => $request->provider,
                    ]);
                }
            }

            return $payment;
        });

        // Notify vendors outside transaction (non-critical)
        if ($order->fresh()->status === 'PLACED') {
            try {
                $this->notificationService->orderPlacedForProcessing($order);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send vendor notification on manual payment', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Payment of R' . number_format($request->amount, 2) . ' recorded.');
    }

    public function refund(Request $request, Order $order)
    {
        $totalPaid = $order->payments()->where('status', 'completed')->where('amount', '>', 0)->sum('amount');
        $totalRefunded = abs((float) $order->payments()->where('status', 'completed')->where('amount', '<', 0)->sum('amount'));
        $maxRefundable = max(0, $totalPaid - $totalRefunded);

        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', "max:{$maxRefundable}"],
            'reason' => 'required|string|max:1000',
        ], [
            'amount.max' => "Refund cannot exceed R" . number_format($maxRefundable, 2) . " (total paid minus previous refunds).",
        ]);

        DB::transaction(function () use ($request, $order) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'provider' => 'refund',
                'reference' => 'REFUND-' . now()->format('YmdHis'),
                'amount' => -abs($request->amount),
                'status' => 'completed',
                'notes' => 'Refund: ' . $request->reason,
                'recorded_by' => auth()->user()->name,
            ]);

            AuditLog::log('refund_processed', 'Payment', $payment->id, [
                'order_id' => $order->id,
                'amount' => $request->amount,
                'reason' => $request->reason,
            ]);

            if ($request->boolean('mark_refunded') && $order->canTransitionTo('REFUNDED')) {
                $order->update(['status' => 'REFUNDED']);
            } elseif ($request->boolean('mark_refunded')) {
                $this->orderService->forceStatus($order, 'REFUNDED', 'Refund processed: ' . $request->reason);
            }
        });

        return back()->with('success', 'Refund of R' . number_format($request->amount, 2) . ' processed.');
    }

    public function bulkAssignDriver(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'driver_id' => 'required|exists:users,id',
        ]);

        $driver = User::findOrFail($request->driver_id);
        if ($driver->role !== 'driver') {
            return back()->with('error', 'Selected user is not a driver.');
        }

        $assigned = 0;
        $failed = 0;

        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);
            try {
                $this->orderService->assignDriver($order, $driver->id);
                $assigned++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        AuditLog::log('bulk_assign_driver', 'Order', null, [
            'driver_id' => $driver->id,
            'assigned' => $assigned,
            'failed' => $failed,
        ]);

        return back()->with('success', "Bulk assign: {$assigned} orders assigned to {$driver->name}" . ($failed > 0 ? ", {$failed} failed" : '') . '.');
    }

    public function export(Request $request)
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
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('min_total')) {
            $query->where('total', '>=', $request->min_total);
        }
        if ($request->filled('max_total')) {
            $query->where('total', '<=', $request->max_total);
        }

        $filename = 'orders-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            CsvHelper::writeBom($handle);
            fputcsv($handle, ['Order #', 'Customer', 'Email', 'Status', 'Subtotal', 'Delivery Fee', 'VAT', 'Discount', 'Total', 'Driver', 'Scheduled Date', 'Created']);

            $query->orderBy('created_at', 'desc')->chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    CsvHelper::safePutCsv($handle, [
                        $order->order_number,
                        $order->customer?->user?->name ?? '-',
                        $order->customer?->user?->email ?? '-',
                        $order->status,
                        number_format($order->subtotal, 2),
                        number_format($order->delivery_fee, 2),
                        number_format($order->vat, 2),
                        number_format($order->discount_amount ?? 0, 2),
                        number_format($order->total, 2),
                        $order->driver?->name ?? '-',
                        $order->scheduled_date?->format('Y-m-d') ?? '-',
                        $order->created_at->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:' . implode(',', Order::STATUSES),
            'reason' => 'nullable|string|max:1000',
        ]);

        $updated = 0;
        $failed = 0;

        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);
            try {
                $this->orderService->updateStatus($order, $request->status, $request->reason);
                $updated++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        AuditLog::log('bulk_status_update', 'Order', null, [
            'status' => $request->status,
            'updated' => $updated,
            'failed' => $failed,
        ]);

        return back()->with('success', "Bulk update: {$updated} orders updated to {$request->status}" . ($failed > 0 ? ", {$failed} couldn't transition" : '') . '.');
    }
}
