<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequestApproval extends Model
{
    protected $fillable = [
        'payment_request_saved_id',
        'user_id',
        'step',
        'role',
        'status',
        'note',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentRequestSaved()
    {
        return $this->belongsTo(
            PaymentRequestSaved::class
        );
    }
}