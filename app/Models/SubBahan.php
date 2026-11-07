<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubBahan extends Model
{
    protected $table = 'sub_bahan';

    protected $fillable = ['bahan_kategori_id', 'name'];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(BahanKategori::class, 'bahan_kategori_id');
    }

    public function kategoriPengajuans(): HasMany
    {
        return $this->hasMany(KategoriBahan::class, 'sub_bahan_id');
    }

    public function detailPengajuans(): HasMany
    {
        return $this->hasMany(PengajuanHargaDetail::class, 'sub_bahan_id');
    }
}
