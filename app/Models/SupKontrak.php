<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupKontrak extends Model
{
    protected $table = 'sup_kontrak';

    protected $fillable = [
        'article_code',
        'supplier_id',
        'kategori',
        'description',

        'harga_kontrak',
        'remark',
        'update_remark',
    ];

    protected $casts = [
        'harga_kontrak' => 'decimal:2',
        'update_remark' => 'array',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Jika nanti kategori_id memiliki model sendiri
    |--------------------------------------------------------------------------
    |
    | public function kategori()
    | {
    |     return $this->belongsTo(KategoriSupplier::class, 'kategori_id');
    | }
    |
    */
     public function detailPo()
    {
        return $this->belongsTo(
            DetailPo::class,
            'detail_po_id'
        );
    }

}