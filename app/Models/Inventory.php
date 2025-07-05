<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    //
        protected $table = 'inventory';
       protected $fillable = [
        'jenis',
        'deskripsi',
        'merk',
        'karyawan_id',
        'keterangan',
        'foto',
        'catatan',
    ];
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
