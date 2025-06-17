<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    //
    protected $fillable = [
        'order_no',
        'company_name',
        'country',
        'shipment_date',
        'packing',
        'contact_person',
    ];
}
