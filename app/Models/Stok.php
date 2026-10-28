<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Stok extends Model
{
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'jenis',
        'satuan',
        'harga',
        'stok_awal',
        'opname',
        'is_opname',
        'opname_at'
    ];
    public function transaksi()
    {
        return $this->hasMany(TransaksiStok::class);
    }
    public function getStokAkhirAttribute()
    {
        $totalIn = $this->transaksi()
            ->where('tipe', 'in')
            ->sum('qty');
        $totalOut = $this->transaksi()
            ->where('tipe', 'out')
            ->sum('qty');
        return $this->stok_awal + $totalIn - $totalOut;
    }
}
