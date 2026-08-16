<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kredit extends Model
{
    protected $table = 'kredit';

    protected $fillable = [
        'spk_id',
        'payment_requests_id',
        'nominal',
        'ket',
        'payment_request_saved_id',
        'ket_extra',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION SPK
    |--------------------------------------------------------------------------
    */

    public function spk()
    {
        return $this->belongsTo(
            Spk::class,
            'spk_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION PAYMENT REQUEST
    |--------------------------------------------------------------------------
    */

    public function paymentRequest()
    {
        return $this->belongsTo(
            PaymentRequest::class,
            'payment_requests_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION PAYMENT REQUEST SAVED
    |--------------------------------------------------------------------------
    */

    public function paymentRequestSaved()
    {
        return $this->belongsTo(
            PaymentRequestSaved::class,
            'payment_request_saved_id'
        );
    }
}