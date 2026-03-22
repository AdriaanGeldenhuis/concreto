<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Setting;
use App\Helpers\CsvHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $query = CreditNote::with(['customer.user', 'invoice', 'order']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('credit_note_no', 'like', "%{$search}%")
                  ->orWhereHas('invoice', fn($q2) => $q2->where('invoice_no', 'like', "%{$search}%"))
                  ->orWhereHas('customer.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $creditNotes = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        $totalCreditNotes = CreditNote::sum('total');
        $thisMonthTotal = CreditNote::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        return view('admin.credit-notes.index', compact('creditNotes', 'totalCreditNotes', 'thisMonthTotal'));
    }

    public function create(Request $request)
    {
        $invoice = null;
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::with(['order.customer.user', 'order.items.product'])->findOrFail($request->invoice_id);
        }

        $vatRate = (float) (Setting::get('vat_rate', '15'));
        return view('admin.credit-notes.create', compact('invoice', 'vatRate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::with('order.customer')->findOrFail($request->invoice_id);
        $order = $invoice->order;

        $vatRate = (float) (Setting::get('vat_rate', '15'));
        $amount = (float) $request->amount;
        $vatAmount = round($amount * $vatRate / 100, 2);
        $total = $amount + $vatAmount;

        // Validate credit note doesn't exceed invoice total
        $existingCredits = CreditNote::where('invoice_id', $invoice->id)->sum('total');
        if (($existingCredits + $total) > (float) $order->total) {
            return back()->withInput()->with('error', 'Credit note total would exceed invoice total.');
        }

        $creditNote = CreditNote::create([
            'credit_note_no' => CreditNote::generateCreditNoteNumber(),
            'invoice_id' => $invoice->id,
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'amount' => $amount,
            'vat_amount' => $vatAmount,
            'total' => $total,
            'reason' => $request->reason,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        // Generate PDF
        $this->generatePdf($creditNote);

        AuditLog::log('created_credit_note', 'CreditNote', $creditNote->id, [
            'invoice' => $invoice->invoice_no,
            'total' => $total,
            'reason' => $request->reason,
        ]);

        return redirect()->route('admin.credit-notes.index')
            ->with('success', "Credit note {$creditNote->credit_note_no} created for R" . number_format($total, 2));
    }

    public function show(CreditNote $creditNote)
    {
        $creditNote->load(['customer.user', 'customer.company', 'invoice', 'order.items.product', 'createdBy']);
        return view('admin.credit-notes.show', compact('creditNote'));
    }

    public function download(CreditNote $creditNote)
    {
        if (!$creditNote->pdf_path || !Storage::disk('local')->exists($creditNote->pdf_path)) {
            $this->generatePdf($creditNote);
        }

        return response()->download(
            Storage::disk('local')->path($creditNote->pdf_path),
            "Credit-Note-{$creditNote->credit_note_no}.pdf"
        );
    }

    public function export(Request $request)
    {
        $query = CreditNote::with(['customer.user', 'invoice']);

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $filename = 'credit-notes-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            CsvHelper::writeBom($handle);
            fputcsv($handle, ['Credit Note #', 'Date', 'Invoice #', 'Customer', 'Amount (excl VAT)', 'VAT', 'Total', 'Reason']);

            $query->orderBy('created_at', 'desc')->chunk(200, function ($notes) use ($handle) {
                foreach ($notes as $cn) {
                    CsvHelper::safePutCsv($handle, [
                        $cn->credit_note_no,
                        $cn->created_at->format('Y-m-d'),
                        $cn->invoice?->invoice_no ?? '-',
                        $cn->customer?->user?->name ?? '-',
                        $cn->amount,
                        $cn->vat_amount,
                        $cn->total,
                        $cn->reason,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    private function generatePdf(CreditNote $creditNote): void
    {
        $creditNote->load(['customer.user', 'customer.company', 'invoice', 'order.items.product']);
        $settings = Setting::getAll();

        $pdf = Pdf::loadView('pdf.credit-note', [
            'creditNote' => $creditNote,
            'settings' => $settings,
        ]);

        $filename = "credit-notes/{$creditNote->credit_note_no}.pdf";
        Storage::disk('local')->put($filename, $pdf->output());
        $creditNote->update(['pdf_path' => $filename]);
    }
}
