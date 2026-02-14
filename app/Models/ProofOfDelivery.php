<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProofOfDelivery extends Model
{
    protected $table = 'proof_of_deliveries';

    protected $fillable = [
        'order_id',
        'signer_name',
        'signature_path',
        'photo_path',
        'gps_lat',
        'gps_lng',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'gps_lat' => 'decimal:7',
            'gps_lng' => 'decimal:7',
            'signed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
