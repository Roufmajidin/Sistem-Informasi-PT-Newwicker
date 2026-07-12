<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockMaterial extends Model
{
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'satuan',
        'qty',
        'harga_qty',
        'jumlah',
        'gudang',
        'in_qty',
        'out_qty',
        'sisa',
        'no_po',
        'tanggal'
    ];
}
