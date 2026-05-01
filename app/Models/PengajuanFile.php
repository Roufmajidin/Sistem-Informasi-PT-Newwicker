<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanFile extends Model
{
    protected $fillable = [
        'pengajuan_id',
        'file_path',
        'type',
        'pengajuan_detail_id',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
