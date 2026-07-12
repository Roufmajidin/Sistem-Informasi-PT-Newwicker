<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequestSaved extends Model
{
    protected $table =
        'payment_request_saveds';

    protected $fillable = [

        'request_no',

        'request_date',

        'need_date',

        'payment_request_ids',

        'grand_total',

        'status',

        'created_by',
    ];

    protected $casts = [

        'payment_request_ids' => 'array',

        'request_date' => 'date',

        'need_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | PAYMENT REQUESTS
    |--------------------------------------------------------------------------
    */

    public function paymentRequests()
    {
        return PaymentRequest::whereIn(
            'id',
            $this->payment_request_ids ?? []
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    public function getTotalItemsAttribute()
    {
        return count(
            $this->payment_request_ids ?? []
        );
    }

}
