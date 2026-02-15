<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\ProofOfDelivery;
use App\Services\InvoiceService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DriverApiController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private NotificationService $notificationService,
    ) {}

    public function listOrders(Request $request)
    {
        $orders = Order::where('driver_id', $request->user()->id)
            ->whereNotIn('status', ['DRAFT', 'PENDING_PAYMENT', 'CANCELLED', 'REFUNDED'])
            ->with(['customer.user', 'deliveryAddress', 'items.product'])
            ->orderBy('scheduled_date')
            ->get();

        return response()->json($orders);
    }

    public function acceptOrder(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $this->transitionStatus($order, 'ACCEPTED', 'accepted');
        return response()->json(['status' => 'accepted']);
    }

    public function loadedOrder(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $this->transitionStatus($order, 'LOADED', 'loaded');

        try {
            $this->notificationService->orderLoaded($order->fresh());
        } catch (\Exception $e) {
            // Notification failure should not block the operation
        }

        return response()->json(['status' => 'loaded']);
    }

    public function transitOrder(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $this->transitionStatus($order, 'IN_TRANSIT', 'in_transit');

        try {
            $this->notificationService->orderEnRoute($order->fresh());
        } catch (\Exception $e) {
            // Notification failure should not block the operation
        }

        return response()->json(['status' => 'in_transit']);
    }

    public function arrivedOrder(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $this->transitionStatus($order, 'ARRIVED', 'arrived');
        return response()->json(['status' => 'arrived']);
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
            'created_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function storeSignature(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);

        if (!in_array($order->status, ['ARRIVED', 'IN_TRANSIT', 'DELIVERED_PENDING_SIGNATURE'])) {
            return response()->json(['error' => 'Order is not in a valid state for POD capture.'], 422);
        }

        $request->validate([
            'signer_name' => 'required|string|max:255',
            'signature' => 'required|string',
            'photo' => 'nullable|image|max:10240|mimes:jpg,jpeg,png',
            'gps_lat' => 'nullable|numeric|between:-90,90',
            'gps_lng' => 'nullable|numeric|between:-180,180',
        ]);

        return DB::transaction(function () use ($request, $order) {
            $order = Order::lockForUpdate()->find($order->id);

            // Prevent duplicate POD
            if ($order->proofOfDelivery) {
                return response()->json([
                    'status' => 'already_delivered',
                    'message' => 'Proof of delivery already recorded.',
                ], 409);
            }

            $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $request->input('signature'));
            $decoded = base64_decode($signatureData, true);
            if ($decoded === false) {
                return response()->json(['error' => 'Invalid signature data.'], 422);
            }

            // Validate decoded signature is actually a PNG image
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($decoded);
            if (!in_array($mimeType, ['image/png', 'image/jpeg', 'image/gif'])) {
                return response()->json(['error' => 'Signature must be a valid image.'], 422);
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

            $invoice = $this->invoiceService->generate($order);

            // Notify customer of delivery
            try {
                $this->notificationService->orderDelivered($order);
            } catch (\Exception $e) {
                // Notification failure should not block the operation
            }

            return response()->json([
                'status' => 'delivered',
                'invoice_no' => $invoice->invoice_no,
            ]);
        });
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
}
