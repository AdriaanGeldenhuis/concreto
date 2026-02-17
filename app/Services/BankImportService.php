<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankImportBatch;
use App\Models\BankTransaction;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class BankImportService
{
    /**
     * Known bank CSV formats with their column header signatures.
     */
    private const BANK_FORMATS = [
        'fnb' => [
            'name' => 'FNB',
            'headers' => ['Date', 'Description', 'Amount', 'Balance', 'Accrued Charges'],
            'date_format' => 'd/m/Y',
            'columns' => ['date' => 0, 'description' => 1, 'amount' => 2, 'balance' => 3],
        ],
        'absa' => [
            'name' => 'ABSA',
            'headers' => ['Date', 'Reference', 'Description', 'Amount', 'Balance'],
            'date_format' => 'Ymd|d-M-y',
            'columns' => ['date' => 0, 'reference' => 1, 'description' => 2, 'amount' => 3, 'balance' => 4],
        ],
        'nedbank' => [
            'name' => 'Nedbank',
            'headers' => ['Date', 'Transaction Description', '*Star Rating', 'Amount', 'Balance'],
            'date_format' => 'Y-m-d',
            'columns' => ['date' => 0, 'description' => 1, 'amount' => 3, 'balance' => 4],
        ],
        'standard_bank' => [
            'name' => 'Standard Bank',
            'headers' => ['Date', 'Description', 'Amount', 'Balance'],
            'date_format' => 'd M Y',
            'columns' => ['date' => 0, 'description' => 1, 'amount' => 2, 'balance' => 3],
        ],
        'capitec' => [
            'name' => 'Capitec',
            'headers' => ['Date', 'Transaction', 'Description', 'Debit', 'Credit', 'Balance'],
            'date_format' => 'Y/m/d',
            'columns' => ['date' => 0, 'description' => 2, 'debit' => 3, 'credit' => 4, 'balance' => 5],
        ],
    ];

    /**
     * Detect the bank format from CSV headers.
     */
    public function detectBankFormat(array $headers): ?string
    {
        $normalised = array_map(fn($h) => strtolower(trim($h)), $headers);

        foreach (self::BANK_FORMATS as $key => $format) {
            $formatHeaders = array_map('strtolower', $format['headers']);
            $matchCount = count(array_intersect($normalised, $formatHeaders));
            // Need at least 3 matching headers or all format headers match
            if ($matchCount >= min(3, count($formatHeaders))) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Get the format definition for a detected bank.
     */
    public function getFormatDefinition(string $formatKey): ?array
    {
        return self::BANK_FORMATS[$formatKey] ?? null;
    }

    /**
     * Get all known bank format names for UI display.
     */
    public function getBankFormats(): array
    {
        return collect(self::BANK_FORMATS)->map(fn($f) => $f['name'])->toArray();
    }

    /**
     * Parse a CSV file and return preview rows.
     */
    public function parseCSV(UploadedFile $file, BankAccount $account): array
    {
        $content = file_get_contents($file->getRealPath());
        $lines = array_filter(explode("\n", str_replace("\r\n", "\n", $content)));

        if (count($lines) < 2) {
            return ['error' => 'CSV file has no data rows.', 'rows' => [], 'format' => null, 'headers' => []];
        }

        $headers = str_getcsv(array_shift($lines));
        $detectedFormat = $this->detectBankFormat($headers);
        $columnMap = null;

        if ($detectedFormat) {
            $columnMap = self::BANK_FORMATS[$detectedFormat]['columns'];
        } elseif ($account->csv_column_map) {
            $columnMap = $account->csv_column_map;
            $detectedFormat = 'custom';
        }

        $rows = [];
        $duplicates = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $cols = str_getcsv($line);
            if (count($cols) < 3) continue;

            if ($columnMap) {
                $parsed = $this->parseRow($cols, $columnMap, $detectedFormat);
            } else {
                // Can't parse without mapping
                $parsed = null;
            }

            if ($parsed) {
                $hash = BankTransaction::generateHash(
                    $parsed['date'],
                    $parsed['description'],
                    (string) $parsed['amount'],
                    $parsed['balance'] ?? null,
                );

                $parsed['hash'] = $hash;
                $parsed['is_duplicate'] = BankTransaction::where('hash', $hash)->exists();

                if ($parsed['is_duplicate']) {
                    $duplicates++;
                }

                $rows[] = $parsed;
            }
        }

        return [
            'rows' => $rows,
            'format' => $detectedFormat,
            'format_name' => $detectedFormat ? (self::BANK_FORMATS[$detectedFormat]['name'] ?? 'Custom') : null,
            'headers' => $headers,
            'total' => count($rows),
            'duplicates' => $duplicates,
            'new_rows' => count($rows) - $duplicates,
        ];
    }

    /**
     * Parse a single CSV row using column mapping.
     */
    private function parseRow(array $cols, array $map, string $format): ?array
    {
        $dateStr = trim($cols[$map['date']] ?? '');
        $description = trim($cols[$map['description']] ?? '');

        if (empty($dateStr) || empty($description)) {
            return null;
        }

        // Parse amount
        $amount = 0;
        if (isset($map['debit']) && isset($map['credit'])) {
            // Capitec-style: separate debit/credit columns
            $debit = $this->parseAmount($cols[$map['debit']] ?? '');
            $credit = $this->parseAmount($cols[$map['credit']] ?? '');
            $amount = $credit > 0 ? $credit : -$debit;
        } else {
            $amount = $this->parseAmount($cols[$map['amount']] ?? '');
        }

        $balance = isset($map['balance']) ? $this->parseAmount($cols[$map['balance']] ?? '') : null;
        $reference = isset($map['reference']) ? trim($cols[$map['reference']] ?? '') : null;
        $date = $this->parseDate($dateStr, $format);

        if (!$date) {
            return null;
        }

        // Extract reference from description if not in separate column
        if (empty($reference)) {
            $reference = $this->extractReference($description);
        }

        return [
            'date' => $date,
            'description' => $description,
            'reference' => $reference ?: null,
            'amount' => round($amount, 2),
            'balance' => $balance !== null ? round($balance, 2) : null,
            'type' => $amount >= 0 ? 'credit' : 'debit',
        ];
    }

    /**
     * Parse an amount string to a float.
     */
    private function parseAmount(string $value): float
    {
        $value = trim($value);
        if (empty($value) || $value === '-') {
            return 0;
        }

        // Remove currency symbols and spaces
        $value = preg_replace('/[R$€£\s]/', '', $value);
        // Handle comma as thousands separator
        $value = str_replace(',', '', $value);
        // Handle parentheses as negative
        if (preg_match('/^\((.+)\)$/', $value, $m)) {
            return -(float) $m[1];
        }

        return (float) $value;
    }

    /**
     * Parse a date string based on bank format.
     */
    private function parseDate(string $dateStr, string $format): ?string
    {
        $dateStr = trim($dateStr);
        if (empty($dateStr)) return null;

        $formatDef = self::BANK_FORMATS[$format] ?? null;

        if ($formatDef) {
            $formats = explode('|', $formatDef['date_format']);
            foreach ($formats as $fmt) {
                try {
                    $date = Carbon::createFromFormat($fmt, $dateStr);
                    if ($date) return $date->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        // Fallback: try common formats
        $fallbackFormats = ['Y-m-d', 'd/m/Y', 'Y/m/d', 'd-m-Y', 'Ymd', 'd M Y', 'd-M-y'];
        foreach ($fallbackFormats as $fmt) {
            try {
                $date = Carbon::createFromFormat($fmt, $dateStr);
                if ($date) return $date->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        // Last resort: try Carbon's natural parsing
        try {
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract a reference number from bank description.
     */
    public function extractReference(string $description): ?string
    {
        $patterns = [
            // Invoice numbers: INV-202602-000043
            '/\b(INV-\d{6}-\d+)\b/i',
            // Order numbers: CON-20260217-0001
            '/\b(CON-\d{8}-\d+)\b/i',
            // Generic REF followed by alphanumeric
            '/\bREF[:\s]*([A-Z0-9\-]+)/i',
            // Reference at end after REF keyword
            '/\bREFERENCE[:\s]*([A-Z0-9\-]+)/i',
            // Payment reference patterns
            '/\bPMT[:\s]*([A-Z0-9\-]+)/i',
            // Yoco reference
            '/\bYOCO[:\s]*([A-Z0-9\-]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Store a CSV file and create the import batch.
     */
    public function storeFile(UploadedFile $file, BankAccount $account, string $importedBy): BankImportBatch
    {
        $path = $file->store('bank-imports', 'local');

        return BankImportBatch::create([
            'bank_account_id' => $account->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'imported_by' => $importedBy,
        ]);
    }

    /**
     * Import parsed rows into the database.
     */
    public function importRows(array $rows, BankAccount $account, BankImportBatch $batch): array
    {
        $imported = 0;
        $skipped = 0;
        $minDate = null;
        $maxDate = null;

        foreach ($rows as $row) {
            if (!empty($row['is_duplicate'])) {
                $skipped++;
                continue;
            }

            $hash = $row['hash'] ?? BankTransaction::generateHash(
                $row['date'],
                $row['description'],
                (string) $row['amount'],
                $row['balance'] ?? null,
            );

            // Double-check for duplicate (race condition protection)
            if (BankTransaction::where('hash', $hash)->exists()) {
                $skipped++;
                continue;
            }

            BankTransaction::create([
                'bank_account_id' => $account->id,
                'transaction_date' => $row['date'],
                'description' => $row['description'],
                'reference' => $row['reference'],
                'amount' => $row['amount'],
                'running_balance' => $row['balance'],
                'type' => $row['type'],
                'hash' => $hash,
                'import_batch_id' => $batch->id,
            ]);

            $imported++;

            // Track date range
            $date = $row['date'];
            if (!$minDate || $date < $minDate) $minDate = $date;
            if (!$maxDate || $date > $maxDate) $maxDate = $date;
        }

        // Update batch stats
        $batch->update([
            'rows_imported' => $imported,
            'rows_skipped' => $skipped,
            'period_from' => $minDate,
            'period_to' => $maxDate,
        ]);

        // Update account balance from latest transaction
        $latestTransaction = $account->transactions()
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestTransaction && $latestTransaction->running_balance !== null) {
            $account->update(['current_balance' => $latestTransaction->running_balance]);
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
        ];
    }

    /**
     * Parse CSV with custom column mapping.
     */
    public function parseCSVWithMapping(UploadedFile $file, BankAccount $account, array $columnMap): array
    {
        $content = file_get_contents($file->getRealPath());
        $lines = array_filter(explode("\n", str_replace("\r\n", "\n", $content)));

        if (count($lines) < 2) {
            return ['error' => 'CSV file has no data rows.', 'rows' => [], 'headers' => []];
        }

        $headers = str_getcsv(array_shift($lines));
        $rows = [];
        $duplicates = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $cols = str_getcsv($line);
            $parsed = $this->parseRow($cols, $columnMap, 'custom');

            if ($parsed) {
                $hash = BankTransaction::generateHash(
                    $parsed['date'],
                    $parsed['description'],
                    (string) $parsed['amount'],
                    $parsed['balance'] ?? null,
                );

                $parsed['hash'] = $hash;
                $parsed['is_duplicate'] = BankTransaction::where('hash', $hash)->exists();

                if ($parsed['is_duplicate']) {
                    $duplicates++;
                }

                $rows[] = $parsed;
            }
        }

        return [
            'rows' => $rows,
            'format' => 'custom',
            'format_name' => 'Custom Mapping',
            'headers' => $headers,
            'total' => count($rows),
            'duplicates' => $duplicates,
            'new_rows' => count($rows) - $duplicates,
        ];
    }
}
