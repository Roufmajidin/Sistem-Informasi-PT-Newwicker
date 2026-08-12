<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvLama extends Model
{
    protected $table = 'inv_lama';

    protected $fillable = [
        'nomor_invoice',
        'tanggal_invoice',
        'detail_bahan',
    ];

    protected $casts = [
        'tanggal_invoice' => 'date',
        'detail_bahan' => 'array',
    ];
}