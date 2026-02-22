<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\ProofOfDelivery;
use App\Services\InvoiceService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private NotificationService $notificationService,
    ) {}

    public function index(Request $request)
    {
        $orders = Order::where('driver_id', $request->user()->id)
            ->whereNotIn('status', ['DRAFT', 'PENDING_PAYMENT', 'CANCELLED', 'REFUNDED'])
            ->orderByRaw("CASE WHEN status = 'DELIVERED' THEN 1 ELSE 0 END")
            ->orderBy('scheduled_date')
            ->paginate(20);

        return view('driver.jobs.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $order->load(['customer.user', 'items.product', 'deliveryAddress', 'proofOfDelivery']);
        return view('driver.jobs.show', compact('order'));
    }

    public function accept(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $this->transitionStatus($order, 'ACCEPTED', 'accepted');
        return back()->with('success', 'Job accepted.');
    }

    public function loaded(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $this->transitionStatus($order, 'LOADED', 'loaded');

        try {
            $this->notificationService->orderLoaded($order->fresh());
        } catch (\Exception $e) {
            // Notification failure should not block the operation
        }

        return back()->with('success', 'Marked as loaded.');
    }

    public function transit(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $this->transitionStatus($order, 'IN_TRANSIT', 'in_transit');

        try {
            $this->notificationService->orderEnRoute($order->fresh());
        } catch (\Exception $e) {
            // Notification failure should not block the operation
        }

        return back()->with('success', 'Delivery started.');
    }

    public function arrived(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $this->transitionStatus($order, 'ARRIVED', 'arrived');
        return back()->with('success', 'Marked as arrived.');
    }

    public function signatureForm(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);

        if (!in_array($order->status, ['ARRIVED', 'IN_TRANSIT', 'DELIVERED_PENDING_SIGNATURE'])) {
            return back()->with('error', 'Order must be arrived before capturing signature.');
        }

        return view('driver.jobs.signature', compact('order'));
    }

    public function storeSignature(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);

        if (!in_array($order->status, ['ARRIVED', 'IN_TRANSIT', 'DELIVERED_PENDING_SIGNATURE'])) {
            return back()->with('error', 'Order is not in a valid state for POD capture.');
        }

        $request->validate([
            'signer_name' => 'required|string|max:255',
            'signature' => 'required|string', // base64
            'photo' => 'nullable|image|max:10240|mimes:jpg,jpeg,png',
        ]);

        return DB::transaction(function () use ($request, $order) {
            $order = Order::lockForUpdate()->find($order->id);

            // Prevent duplicate POD
            if ($order->proofOfDelivery) {
                return redirect()->route('driver.jobs.show', $order)
                    ->with('info', 'Proof of delivery already recorded.');
            }

            // Save signature image
            $signatureData = $request->input('signature');
            $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureData);
            $decoded = base64_decode($signatureData, true);
            if ($decoded === false) {
                return back()->with('error', 'Invalid signature data.');
            }

            // Validate decoded data is actually an image
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($decoded);
            if (!in_array($mimeType, ['image/png', 'image/jpeg', 'image/gif'])) {
                return back()->with('error', 'Signature must be a valid image.');
            }

            $signaturePath = 'signatures/' . $order->id . '_' . time() . '.png';
            Storage::disk('local')->put($signaturePath, $decoded);

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('delivery-photos', 'local');
            }

            ProofOfDelivery::create([
                'order_id' => $order->id,
                'signer_name' => $request->input('signer_name'),
                'signature_path' => $signaturePath,
                'photo_path' => $photoPath,
                'gps_lat' => $request->input('gps_lat'),
                'gps_lng' => $request->input('gps_lng'),
                'signed_at' => now(),
            ]);

            $order->update(['status' => 'DELIVERED']);
            AuditLog::log('delivered', 'Order', $order->id);

            // Generate invoice and email
            $invoice = $this->invoiceService->generate($order);
            $this->emailInvoice($order, $invoice);

            // Send delivery notification
            try {
                $this->notificationService->orderDelivered($order);
            } catch (\Exception $e) {
                // Notification failure should not block the operation
            }

            return redirect()->route('driver.jobs.show', $order)
                ->with('success', 'Delivery completed and invoice sent!');
        });
    }

    public function updateLocation(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);

        // Only track when order is in active delivery statuses
        if (!in_array($order->status, ['ACCEPTED', 'LOADED', 'IN_TRANSIT', 'ARRIVED'])) {
            return response()->json(['error' => 'Tracking not active for this order status.'], 422);
        }

        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'accuracy' => 'nullable|numeric|min:0',
        ]);

        // Rate limit: moving = 1 per 10s, stationary = 1 per 60s
        $lastLocation = DriverLocation::where('order_id', $order->id)
            ->where('driver_id', $request->user()->id)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if ($lastLocation) {
            $isStationary = ($request->speed ?? 0) < 1;
            $minInterval = $isStationary ? 60 : 10;
            if ($lastLocation->recorded_at->diffInSeconds(now()) < $minInterval) {
                return response()->json(['status' => 'throttled'], 429);
            }
        }

        DriverLocation::create([
            'driver_id' => $request->user()->id,
            'order_id' => $order->id,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'accuracy' => $request->accuracy,
            'recorded_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    private function transitionStatus(Order $order, string $newStatus, string $auditAction): void
    {
        DB::transaction(function () use ($order, $newStatus, $auditAction) {
            $order = Order::lockForUpdate()->find($order->id);

            if (!$order->canTransitionTo($newStatus)) {
                abort(422, "Cannot transition from '{$order->status}' to '{$newStatus}'.");
            }

            $order->update(['status' => $newStatus]);
            AuditLog::log($auditAction, 'Order', $order->id);
        });
    }

    private function authorizeDriver(Request $request, Order $order): void
    {
        if ($order->driver_id !== $request->user()->id) {
            abort(403);
        }
    }

    private function emailInvoice(Order $order, $invoice): void
    {
        $maxAttempts = 3;
        $attempt = 0;
        $lastError = null;

        $order->load('customer.user');
        $email = $order->customer->user->email;
        $pdfPath = $this->invoiceService->getPdfPath($invoice);

        while ($attempt < $maxAttempts) {
            $attempt++;
            try {
                Mail::send('emails.invoice', ['order' => $order, 'invoice' => $invoice], function ($message) use ($email, $invoice, $pdfPath) {
                    $message->to($email)
                        ->subject("Invoice {$invoice->invoice_no} - Concreto")
                        ->attach($pdfPath, ['as' => "{$invoice->invoice_no}.pdf"]);
                });

                $invoice->update(['emailed_at' => now()]);

                \App\Models\EmailLog::create([
                    'to_email' => $email,
                    'subject' => "Invoice {$invoice->invoice_no}",
                    'template' => 'emails.invoice',
                    'related_type' => 'Invoice',
                    'related_id' => $invoice->id,
                    'status' => 'sent',
                ]);
                return;
            } catch (\Exception $e) {
                $lastError = $e;
                if ($attempt < $maxAttempts) {
                    usleep(pow(2, $attempt - 1) * 1000000); // 1s, 2s backoff
                }
            }
        }

        // All attempts failed
        \App\Models\EmailLog::create([
            'to_email' => $email ?? ($order->customer->user->email ?? ''),
            'subject' => "Invoice {$invoice->invoice_no}",
            'template' => 'emails.invoice',
            'related_type' => 'Invoice',
            'related_id' => $invoice->id,
            'status' => 'failed',
            'error_message' => $lastError?->getMessage(),
        ]);
    }
}
