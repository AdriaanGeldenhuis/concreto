<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankImportBatch extends Model
{
    protected $fillable = [
        'bank_account_id',
        'file_name',
        'file_path',
        'rows_imported',
        'rows_skipped',
        'rows_auto_matched',
        'period_from',
        'period_to',
        'imported_by',
    ];

    protected function casts(): array
    {
        return [
            'period_from' => 'date',
            'period_to' => 'date',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'import_batch_id');
    }
}
