<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'type',
        'name',
        'email',
        'phone',
        'message',
        'products_needed',
        'delivery_address',
        'status',
        'admin_notes',
    ];
}
