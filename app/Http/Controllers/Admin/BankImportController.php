<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Services\BankImportService;
use App\Services\BankReconciliationService;
use Illuminate\Http\Request;

class BankImportController extends Controller
{
    public function __construct(
        private BankImportService $importService,
        private BankReconciliationService $reconciliationService,
    ) {}

    public function show(BankAccount $bankAccount)
    {
        return view('admin.bank-accounts.import', compact('bankAccount'));
    }

    public function preview(Request $request, BankAccount $bankAccount)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $result = $this->importService->parseCSV($file, $bankAccount);

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        // If no format detected and no saved mapping, show column mapper
        if (!$result['format'] && !$bankAccount->csv_column_map) {
            return view('admin.bank-accounts.import-preview', [
                'bankAccount' => $bankAccount,
                'result' => $result,
                'needsMapping' => true,
                'headers' => $result['headers'],
            ]);
        }

        // Store file temporarily for confirmation step
        $tempPath = $file->store('temp-imports', 'local');

        return view('admin.bank-accounts.import-preview', [
            'bankAccount' => $bankAccount,
            'result' => $result,
            'needsMapping' => false,
            'tempPath' => $tempPath,
            'originalName' => $file->getClientOriginalName(),
        ]);
    }

    public function confirm(Request $request, BankAccount $bankAccount)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'original_name' => 'required|string',
        ]);

        $tempPath = $request->input('temp_path');
        $fullPath = storage_path('app/private/' . $tempPath);

        if (!file_exists($fullPath)) {
            return redirect()->route('admin.bank-accounts.import.show', $bankAccount)
                ->with('error', 'Upload expired. Please upload the file again.');
        }

        // Re-parse the file
        $file = new \Illuminate\Http\UploadedFile($fullPath, $request->input('original_name'));
        $result = $this->importService->parseCSV($file, $bankAccount);

        if (empty($result['rows'])) {
            return redirect()->route('admin.bank-accounts.import.show', $bankAccount)
                ->with('error', 'No valid rows to import.');
        }

        // Store the actual file permanently
        $batch = $this->importService->storeFile($file, $bankAccount, auth()->user()->name);

        // Import the rows
        $importResult = $this->importService->importRows($result['rows'], $bankAccount, $batch);

        // Clean up temp file
        @unlink($fullPath);

        // Run auto-matching
        $matchResult = $this->reconciliationService->autoMatch($bankAccount->id);

        // Update batch with auto-match count
        $batch->update(['rows_auto_matched' => $matchResult['matched']]);

        return redirect()->route('admin.bank-reconciliation.index', ['account' => $bankAccount->id])
            ->with('success', sprintf(
                'Imported %d transactions (%d duplicates skipped). Auto-matched %d transactions.',
                $importResult['imported'],
                $importResult['skipped'],
                $matchResult['matched'],
            ));
    }

    public function saveMapping(Request $request, BankAccount $bankAccount)
    {
        $request->validate([
            'col_date' => 'required|integer|min:0',
            'col_description' => 'required|integer|min:0',
            'col_amount' => 'nullable|integer|min:0',
            'col_debit' => 'nullable|integer|min:0',
            'col_credit' => 'nullable|integer|min:0',
            'col_balance' => 'nullable|integer|min:0',
            'col_reference' => 'nullable|integer|min:0',
        ]);

        $map = [
            'date' => (int) $request->col_date,
            'description' => (int) $request->col_description,
        ];

        if ($request->filled('col_amount')) {
            $map['amount'] = (int) $request->col_amount;
        }
        if ($request->filled('col_debit')) {
            $map['debit'] = (int) $request->col_debit;
        }
        if ($request->filled('col_credit')) {
            $map['credit'] = (int) $request->col_credit;
        }
        if ($request->filled('col_balance')) {
            $map['balance'] = (int) $request->col_balance;
        }
        if ($request->filled('col_reference')) {
            $map['reference'] = (int) $request->col_reference;
        }

        $bankAccount->update(['csv_column_map' => $map]);

        return redirect()->route('admin.bank-accounts.import.show', $bankAccount)
            ->with('success', 'Column mapping saved. Please re-upload the CSV file to continue.');
    }
}
