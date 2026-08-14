<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpahBorongan extends Model
{
    protected $table = 'upah_borongan';

    protected $fillable = [
        'article',
        'description',
        'jenis',
        'harga',
        'update_remark',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'update_remark' => 'array',
    ];
}