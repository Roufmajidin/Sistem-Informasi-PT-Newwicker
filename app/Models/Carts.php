<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carts extends Model
{
    //
    protected $table = 'cart';
    //  use HasFactory;

    protected $fillable = [
        'article_code',
        'created_at',
        'buyer_id',
        'status',
        'remark',
        'qty',
        'local_id',
        'isDeleted',
    ];
}
