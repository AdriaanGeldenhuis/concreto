<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\Vendor;
use App\Helpers\CsvHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierInvoice::with('vendor');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('from')) {
            $query->where('invoice_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('invoice_date', '<=', $request->to);
        }

        $invoices = $query->orderBy('due_date', 'asc')->paginate(25)->withQueryString();

        // Summary stats
        $totalUnpaid = SupplierInvoice::whereIn('status', ['unpaid', 'partial'])->sum(DB::raw('total - amount_paid'));
        $totalOverdue = SupplierInvoice::whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', now()->format('Y-m-d'))
            ->sum(DB::raw('total - amount_paid'));
        $overdueCount = SupplierInvoice::whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', now()->format('Y-m-d'))
            ->count();
        $thisMonthDue = SupplierInvoice::whereIn('status', ['unpaid', 'partial'])
            ->whereBetween('due_date', [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')])
            ->sum(DB::raw('total - amount_paid'));

        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('admin.supplier-invoices.index', compact(
            'invoices', 'totalUnpaid', 'totalOverdue', 'overdueCount', 'thisMonthDue', 'vendors'
        ));
    }

    public function create()
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $vatRate = (float) (Setting::get('vat_rate', '15'));
        return view('admin.supplier-invoices.create', compact('vendors', 'vatRate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'invoice_number' => 'required|string|max:100',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'amount' => 'required|numeric|min:0.01',
            'vat_amount' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
        ]);

        $amount = (float) $request->amount;
        $vatAmount = (float) ($request->vat_amount ?? 0);

        $invoice = SupplierInvoice::create([
            'vendor_id' => $request->vendor_id,
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'amount' => $amount,
            'vat_amount' => $vatAmount,
            'total' => $amount + $vatAmount,
            'reference' => $request->reference,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        AuditLog::log('created_supplier_invoice', 'SupplierInvoice', $invoice->id, [
            'vendor' => $invoice->vendor->name,
            'total' => $invoice->total,
        ]);

        return redirect()->route('admin.supplier-invoices.index')
            ->with('success', "Supplier invoice #{$invoice->invoice_number} created.");
    }

    public function show(SupplierInvoice $supplierInvoice)
    {
        $supplierInvoice->load(['vendor', 'payments.createdBy']);
        return view('admin.supplier-invoices.show', compact('supplierInvoice'));
    }

    public function edit(SupplierInvoice $supplierInvoice)
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $vatRate = (float) (Setting::get('vat_rate', '15'));
        return view('admin.supplier-invoices.edit', compact('supplierInvoice', 'vendors', 'vatRate'));
    }

    public function update(Request $request, SupplierInvoice $supplierInvoice)
    {
        if ($supplierInvoice->status === 'paid') {
            return back()->with('error', 'Cannot edit a fully paid invoice.');
        }

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'invoice_number' => 'required|string|max:100',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'amount' => 'required|numeric|min:0.01',
            'vat_amount' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
        ]);

        $amount = (float) $request->amount;
        $vatAmount = (float) ($request->vat_amount ?? 0);

        $supplierInvoice->update([
            'vendor_id' => $request->vendor_id,
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'amount' => $amount,
            'vat_amount' => $vatAmount,
            'total' => $amount + $vatAmount,
            'reference' => $request->reference,
            'notes' => $request->notes,
        ]);

        AuditLog::log('updated_supplier_invoice', 'SupplierInvoice', $supplierInvoice->id);

        return redirect()->route('admin.supplier-invoices.show', $supplierInvoice)
            ->with('success', 'Supplier invoice updated.');
    }

    public function recordPayment(Request $request, SupplierInvoice $supplierInvoice)
    {
        if ($supplierInvoice->status === 'paid') {
            return back()->with('error', 'Invoice is already fully paid.');
        }

        $balanceDue = $supplierInvoice->balance_due;

        $request->validate([
            'amount' => "required|numeric|min:0.01|max:{$balanceDue}",
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:150',
            'method' => 'required|in:eft,cash,card,cheque,other',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $supplierInvoice) {
            SupplierPayment::create([
                'supplier_invoice_id' => $supplierInvoice->id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'reference' => $request->reference,
                'method' => $request->method,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            $supplierInvoice->recordPayment((float) $request->amount);
        });

        AuditLog::log('recorded_supplier_payment', 'SupplierInvoice', $supplierInvoice->id, [
            'amount' => $request->amount,
            'method' => $request->method,
        ]);

        return back()->with('success', "Payment of R" . number_format($request->amount, 2) . " recorded.");
    }

    public function destroy(SupplierInvoice $supplierInvoice)
    {
        if ($supplierInvoice->payments()->exists()) {
            return back()->with('error', 'Cannot delete invoice with recorded payments.');
        }

        AuditLog::log('deleted_supplier_invoice', 'SupplierInvoice', $supplierInvoice->id, [
            'invoice_number' => $supplierInvoice->invoice_number,
            'vendor' => $supplierInvoice->vendor->name,
        ]);

        $supplierInvoice->delete();

        return redirect()->route('admin.supplier-invoices.index')
            ->with('success', 'Supplier invoice deleted.');
    }

    public function aging(Request $request)
    {
        $vendors = Vendor::with(['supplierInvoices' => function ($q) {
            $q->whereIn('status', ['unpaid', 'partial'])->orderBy('due_date');
        }])->whereHas('supplierInvoices', function ($q) {
            $q->whereIn('status', ['unpaid', 'partial']);
        })->get();

        $apData = $vendors->map(function ($vendor) {
            $invoices = $vendor->supplierInvoices;
            $current = $over30 = $over60 = $over90 = 0;

            foreach ($invoices as $inv) {
                $balance = (float) $inv->total - (float) $inv->amount_paid;
                $days = (int) now()->diffInDays($inv->due_date, false);
                // days > 0 means future (not yet due), days < 0 means overdue
                if ($days >= 0) {
                    $current += $balance;
                } elseif ($days >= -30) {
                    $current += $balance; // 0-30 days overdue still "current"
                } elseif ($days >= -60) {
                    $over30 += $balance;
                } elseif ($days >= -90) {
                    $over60 += $balance;
                } else {
                    $over90 += $balance;
                }
            }

            $totalOwed = $invoices->sum(fn($inv) => (float) $inv->total - (float) $inv->amount_paid);
            $oldestDue = $invoices->sortBy('due_date')->first()?->due_date;
            $daysOverdue = $oldestDue && $oldestDue->isPast() ? (int) now()->diffInDays($oldestDue) : 0;

            return (object) [
                'vendor' => $vendor,
                'total_owed' => $totalOwed,
                'invoice_count' => $invoices->count(),
                'oldest_due' => $oldestDue,
                'days_overdue' => $daysOverdue,
                'current' => $current,
                'over30' => $over30,
                'over60' => $over60,
                'over90' => $over90,
            ];
        })->sortByDesc('total_owed');

        $totalOwed = $apData->sum('total_owed');
        $totalCurrent = $apData->sum('current');
        $totalOver30 = $apData->sum('over30');
        $totalOver60 = $apData->sum('over60');
        $totalOver90 = $apData->sum('over90');
        $totalOverdue = $totalOver30 + $totalOver60 + $totalOver90;

        return view('admin.supplier-invoices.aging', compact(
            'apData', 'totalOwed', 'totalCurrent', 'totalOver30', 'totalOver60', 'totalOver90', 'totalOverdue'
        ));
    }

    public function export(Request $request)
    {
        $query = SupplierInvoice::with('vendor');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->where('invoice_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('invoice_date', '<=', $request->to);
        }

        $filename = 'supplier-invoices-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            CsvHelper::writeBom($handle);
            fputcsv($handle, [
                'Vendor', 'Invoice #', 'Date', 'Due Date', 'Amount (excl VAT)',
                'VAT', 'Total', 'Paid', 'Balance Due', 'Status', 'Reference',
            ]);

            $query->orderBy('invoice_date', 'desc')->chunk(200, function ($invoices) use ($handle) {
                foreach ($invoices as $inv) {
                    CsvHelper::safePutCsv($handle, [
                        $inv->vendor->name,
                        $inv->invoice_number,
                        $inv->invoice_date->format('Y-m-d'),
                        $inv->due_date->format('Y-m-d'),
                        $inv->amount,
                        $inv->vat_amount,
                        $inv->total,
                        $inv->amount_paid,
                        $inv->balance_due,
                        $inv->status,
                        $inv->reference ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
