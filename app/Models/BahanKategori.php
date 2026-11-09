<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BahanKategori extends Model
{
    protected $table = 'bahan_kategori';

    protected $fillable = ['name'];

    public function subBahans(): HasMany
    {
        return $this->hasMany(SubBahan::class, 'bahan_kategori_id');
    }

    public function kategoriPengajuans(): HasMany
    {
        return $this->hasMany(KategoriBahan::class, 'kategori_id');
    }
    public function pengajuanHargaPekerjaans()
{
    return $this->hasMany(
        PengajuanHargaPekerjaan::class,
        'bahan_kategori_id'
    );
}
}
