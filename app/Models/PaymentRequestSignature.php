<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequestSignature
extends Model
{
    protected $fillable = [

        'payment_request_id',

        'role',

        'user_id',

        'status',

        'signed_at',

        'note',
    ];

    protected $casts = [

        'signed_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(
            PaymentRequest::class,
            'payment_request_id'
        );
    }
}
