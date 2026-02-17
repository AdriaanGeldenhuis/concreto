<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_account_id',
        'transaction_date',
        'description',
        'reference',
        'amount',
        'running_balance',
        'type',
        'category',
        'hash',
        'import_batch_id',
        'reconciliation_status',
        'matched_at',
        'matched_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'matched_at' => 'datetime',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(BankImportBatch::class, 'import_batch_id');
    }

    public function reconciliationMatch(): HasOne
    {
        return $this->hasOne(BankReconciliationMatch::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'bank_transaction_id');
    }

    // Scopes
    public function scopeUnmatched(Builder $query): Builder
    {
        return $query->where('reconciliation_status', 'unmatched');
    }

    public function scopeMatched(Builder $query): Builder
    {
        return $query->whereIn('reconciliation_status', ['matched', 'manually_reconciled']);
    }

    public function scopeExcluded(Builder $query): Builder
    {
        return $query->where('reconciliation_status', 'excluded');
    }

    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('type', 'debit');
    }

    public function scopeForAccount(Builder $query, int $accountId): Builder
    {
        return $query->where('bank_account_id', $accountId);
    }

    public function scopeInDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->where('transaction_date', '>=', $from);
        }
        if ($to) {
            $query->where('transaction_date', '<=', $to);
        }
        return $query;
    }

    public static function generateHash(string $date, string $description, string $amount, ?string $balance): string
    {
        return hash('sha256', $date . '|' . $description . '|' . $amount . '|' . ($balance ?? ''));
    }
}
