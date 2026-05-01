<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanMeta extends Model
{
    protected $table = 'pengajuan_meta';

    protected $fillable = [
        'pengajuan_id',
        'tanggal',
        'nomor',
        'type_pembayaran',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
