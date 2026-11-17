<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ar2026 extends Model
{
    protected $table = 'ars_2026';
    protected $guarded = ['id'];
    protected $casts = [
        'shipment_date' => 'date:Y-m-d',
        'deposit_date' => 'date:Y-m-d',
        'settlement_date' => 'date:Y-m-d',
    ];
}
