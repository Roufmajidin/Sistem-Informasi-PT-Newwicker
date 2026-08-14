<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upah extends Model
{
    protected $table = 'upah';

    protected $fillable = [
        'article',
        'description',
        'tanggal',
        'pekerjaan',
        'person',
        'qty',
        'harga',
        'total',
        'no_po',
        'no_spk',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'qty' => 'decimal:2',
        'harga' => 'decimal:2',
        'total' => 'decimal:2',
        'updated_by' => 'array',
    ];
}