<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    protected $fillable = [

        'spk_id',
        'request_no',
        'no_spk',
        'no_po',
        'supplier',
        'kategori',
        'request_date',
        'need_date',
        'status',
        'total_amount',
        'spk_snapshot',
        'checked_types',
        'created_by',
        'payment_id',

    ];

    protected $casts = [

        'spk_snapshot' => 'array',

        'checked_types' => 'array',

        'request_date' => 'date',

        'need_date' => 'date',
    ];
    protected $table = 'payment_requests';

    // =========================
    // RELATION
    // =========================

    // public function items()
    // {
    //     return $this->hasMany(
    //         PaymentRequestItem::class
    //     );
    // }

    public function spk()
    {
        return $this->belongsTo(
            Spk::class
        );
    }

    // =========================
    // HELPER
    // =========================

    public function getTotalAmountAttribute()
    {
        return $this->items->sum('amount');
    }
}
