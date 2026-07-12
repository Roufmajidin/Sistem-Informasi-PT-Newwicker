<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiStok extends Model
{
    protected $fillable = [
        'stok_id',
        'tanggal',
        'tipe',
        'qty',
        'po',
        'spk_id',
        'keterangan',
        'harga_vivi'
    ];
    public function spk()
    {
        return $this->belongsTo(Spk::class);
    }
    public function stok()
    {
        return $this->belongsTo(Stok::class);
    }
}
