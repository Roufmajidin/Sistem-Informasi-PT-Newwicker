<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewBuyer extends Model
{
    //
    // use HasFactory;
    protected $table = 'new_buyer';
    protected $fillable = [
        'buyer_id',
        'order_no',
        'company_name',
        'country',
        'shipment_date',
        'packing',
        'contact_person',
    ];
}
