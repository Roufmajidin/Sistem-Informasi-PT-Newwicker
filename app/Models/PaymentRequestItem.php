<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequestItem extends Model
{
    protected $fillable = [

        'payment_request_id',

        'payment_type',

        'amount',

        'payment_date',

        'note',

        'note_tambahan',

        'is_selected',

        'status'
    ];

    protected $casts = [

        'payment_date' => 'date',

        'is_selected' => 'boolean'
    ];

    // =========================
    // RELATION
    // =========================

    public function request()
    {
        return $this->belongsTo(
            PaymentRequest::class,
            'payment_request_id'
        );
    }

    // =========================
    // HELPER
    // =========================

    public function getFormattedAmountAttribute()
    {
        return number_format(
            $this->amount,
            0,
            ',',
            '.'
        );
    }
}
