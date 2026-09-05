<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanDivisi extends Model
{
    protected $table = 'pengajuan_divisi';

 protected $fillable = [
    'pengajuan_id',
    'id_stock',
    'divisi_id',
    'nama_barang',
    'po_no',
    'supplier',
    'description',
    'keterangan',
    'qty',
    'unit',
    'price',
    'added_to_warehouse',
];

    protected $casts = [
        'qty' => 'decimal:2',
        'price' => 'decimal:2',
        'added_to_warehouse' => 'boolean',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(
            Pengajuan::class,
            'pengajuan_id'
        );
    }

    public function stok()
    {
        return $this->belongsTo(
            Stok::class,
            'id_stock'
        );
    }

    public function divisi()
    {
        return $this->belongsTo(
            Divisi::class,
            'divisi_id'
        );
    }
}