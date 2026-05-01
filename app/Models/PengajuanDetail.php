<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanDetail extends Model
{
    protected $fillable = [
        'pengajuan_id',
        'no',
        'date',
        'no_po',
        'no_inv',
        'type_biaya',
        'nama_barang',
        'qty',
        'harga_satuan',
        'total_harga'
    ];

    public function pengajuan(){
        return $this->belongsTo(Pengajuan::class);
    }
}
