<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'to_email',
        'subject',
        'template',
        'related_type',
        'related_id',
        'status',
        'error_message',
    ];
}
