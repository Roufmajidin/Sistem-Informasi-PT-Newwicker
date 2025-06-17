<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'content',
        'buyer_id',
        'image_path'
    ];

    protected $casts = [
        'content' => 'array', // otomatis decode/encode JSON
    ];
}
