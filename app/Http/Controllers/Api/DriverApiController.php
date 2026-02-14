<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\ProofOfDelivery;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DriverApiController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

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
        $order->update(['status' => 'ACCEPTED']);
        AuditLog::log('accepted', 'Order', $order->id);
        return response()->json(['status' => 'accepted']);
    }

    public function loadedOrder(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $order->update(['status' => 'LOADED']);
        AuditLog::log('loaded', 'Order', $order->id);
        return response()->json(['status' => 'loaded']);
    }

    public function transitOrder(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $order->update(['status' => 'IN_TRANSIT']);
        AuditLog::log('in_transit', 'Order', $order->id);
        return response()->json(['status' => 'in_transit']);
    }

    public function arrivedOrder(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $order->update(['status' => 'ARRIVED']);
        AuditLog::log('arrived', 'Order', $order->id);
        return response()->json(['status' => 'arrived']);
    }

    public function updateLocation(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);

        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
        ]);

        DriverLocation::create([
            'driver_id' => $request->user()->id,
            'order_id' => $order->id,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'recorded_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function storeSignature(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);

        $request->validate([
            'signer_name' => 'required|string|max:255',
            'signature' => 'required|string',
            'gps_lat' => 'nullable|numeric',
            'gps_lng' => 'nullable|numeric',
        ]);

        $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $request->input('signature'));
        $signatureData = base64_decode($signatureData);
        $signaturePath = 'signatures/' . $order->id . '_' . time() . '.png';
        Storage::disk('local')->put($signaturePath, $signatureData);

        ProofOfDelivery::create([
            'order_id' => $order->id,
            'signer_name' => $request->input('signer_name'),
            'signature_path' => $signaturePath,
            'gps_lat' => $request->input('gps_lat'),
            'gps_lng' => $request->input('gps_lng'),
            'signed_at' => now(),
        ]);

        $order->update(['status' => 'DELIVERED']);
        AuditLog::log('delivered', 'Order', $order->id);

        $invoice = $this->invoiceService->generate($order);

        return response()->json([
            'status' => 'delivered',
            'invoice_no' => $invoice->invoice_no,
        ]);
    }

    private function authorizeDriver(Request $request, Order $order): void
    {
        if ($order->driver_id !== $request->user()->id) {
            abort(403);
        }
    }
}
