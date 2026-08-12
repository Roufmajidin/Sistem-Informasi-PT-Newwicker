<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpkLama extends Model
{
    protected $table = 'spk_lama';

    protected $fillable = [
        'name_sub',
        'po',
        'no_spk',
        'no_inv',
        'pemotongan_bahan',
        'tanggal_potong',
    ];

    protected $casts = [
        'pemotongan_bahan' => 'decimal:2',
        'tanggal_potong' => 'date',
    ];
}