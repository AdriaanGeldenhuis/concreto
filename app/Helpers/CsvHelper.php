<?php

namespace App\Helpers;

class CsvHelper
{
    /**
     * Sanitize a value for safe CSV export.
     * Prevents formula injection when CSVs are opened in Excel.
     */
    public static function safe(mixed $value): string
    {
        $str = (string) ($value ?? '');

        if ($str !== '' && preg_match('/^[=+\-@\t\r]/', $str)) {
            return "'" . $str;
        }

        return $str;
    }

    /**
     * Write a UTF-8 BOM to a file handle for correct Excel encoding.
     */
    public static function writeBom($handle): void
    {
        fwrite($handle, "\xEF\xBB\xBF");
    }

    /**
     * Write a safe CSV row - sanitizes all string values.
     */
    public static function safePutCsv($handle, array $fields): void
    {
        $safe = array_map(fn($v) => is_numeric($v) ? $v : self::safe($v), $fields);
        fputcsv($handle, $safe);
    }
}
