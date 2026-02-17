<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliationRule extends Model
{
    protected $fillable = [
        'bank_account_id',
        'name',
        'match_field',
        'match_type',
        'match_value',
        'category',
        'auto_action',
        'target_customer_id',
        'is_active',
        'times_applied',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function targetCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'target_customer_id');
    }

    public function matches(string $value): bool
    {
        return match ($this->match_type) {
            'contains' => str_contains(strtoupper($value), strtoupper($this->match_value)),
            'starts_with' => str_starts_with(strtoupper($value), strtoupper($this->match_value)),
            'exact' => strtoupper($value) === strtoupper($this->match_value),
            'regex' => (bool) preg_match('/' . $this->match_value . '/i', $value),
            default => false,
        };
    }
}
