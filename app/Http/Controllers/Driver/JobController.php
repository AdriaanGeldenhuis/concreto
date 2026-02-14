<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DriverLocation;
use App\Models\Order;
use App\Models\ProofOfDelivery;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

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
        $order->update(['status' => 'ACCEPTED']);
        AuditLog::log('accepted', 'Order', $order->id);
        return back()->with('success', 'Job accepted.');
    }

    public function loaded(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $order->update(['status' => 'LOADED']);
        AuditLog::log('loaded', 'Order', $order->id);
        return back()->with('success', 'Marked as loaded.');
    }

    public function transit(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $order->update(['status' => 'IN_TRANSIT']);
        AuditLog::log('in_transit', 'Order', $order->id);
        return back()->with('success', 'Delivery started.');
    }

    public function arrived(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $order->update(['status' => 'ARRIVED']);
        AuditLog::log('arrived', 'Order', $order->id);
        return back()->with('success', 'Marked as arrived.');
    }

    public function signatureForm(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);
        $order->update(['status' => 'DELIVERED_PENDING_SIGNATURE']);
        return view('driver.jobs.signature', compact('order'));
    }

    public function storeSignature(Request $request, Order $order)
    {
        $this->authorizeDriver($request, $order);

        $request->validate([
            'signer_name' => 'required|string|max:255',
            'signature' => 'required|string', // base64
            'photo' => 'nullable|image|max:5120',
        ]);

        // Save signature image
        $signatureData = $request->input('signature');
        $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureData);
        $signatureData = base64_decode($signatureData);
        $signaturePath = 'signatures/' . $order->id . '_' . time() . '.png';
        Storage::disk('local')->put($signaturePath, $signatureData);

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

        return redirect()->route('driver.jobs.show', $order)
            ->with('success', 'Delivery completed and invoice sent!');
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

    private function authorizeDriver(Request $request, Order $order): void
    {
        if ($order->driver_id !== $request->user()->id) {
            abort(403);
        }
    }

    private function emailInvoice(Order $order, $invoice): void
    {
        try {
            $order->load('customer.user');
            $email = $order->customer->user->email;
            $pdfPath = $this->invoiceService->getPdfPath($invoice);

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
        } catch (\Exception $e) {
            \App\Models\EmailLog::create([
                'to_email' => $order->customer->user->email ?? '',
                'subject' => "Invoice {$invoice->invoice_no}",
                'template' => 'emails.invoice',
                'related_type' => 'Invoice',
                'related_id' => $invoice->id,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
