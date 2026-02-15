<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'trading_as',
        'registration_number',
        'vat_number',
        'email',
        'phone',
        'contact_person',
        'address_line1',
        'address_line2',
        'city',
        'province',
        'postal_code',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->trading_as ?: $this->name;
    }

    public function getFullAddressAttribute(): string
    {
        return collect([$this->address_line1, $this->address_line2, $this->city, $this->province, $this->postal_code])
            ->filter()
            ->implode(', ');
    }
}
