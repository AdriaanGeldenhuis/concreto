<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'account_name',
        'bank_name',
        'account_number_encrypted',
        'account_number_last4',
        'branch_code',
        'account_type',
        'currency',
        'opening_balance',
        'current_balance',
        'is_primary',
        'is_active',
        'csv_column_map',
    ];

    protected function casts(): array
    {
        return [
            'account_number_encrypted' => 'encrypted',
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'csv_column_map' => 'array',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function importBatches(): HasMany
    {
        return $this->hasMany(BankImportBatch::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(BankReconciliationRule::class);
    }

    public function getDisplayAccountNumberAttribute(): string
    {
        return $this->account_number_last4 ? '****' . $this->account_number_last4 : '—';
    }

    public function getLastImportDateAttribute(): ?string
    {
        $batch = $this->importBatches()->latest()->first();
        return $batch?->created_at?->format('d M Y');
    }

    public static function ensureSinglePrimary(int $exceptId = 0): void
    {
        static::where('is_primary', true)
            ->where('id', '!=', $exceptId)
            ->update(['is_primary' => false]);
    }
}
