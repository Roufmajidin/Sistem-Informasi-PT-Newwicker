<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanHargaDetail extends Model
{
    protected $table = 'pengajuan_harga_detail';

    protected $fillable = [
        'pengajuan_harga_id', 'kategori_bahan_id', 'sub_bahan_id',
        'nama_bahan', 'qty', 'unit', 'harga', 'name_sub',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'harga' => 'decimal:2',
        ];
    }

    public function pengajuanHarga(): BelongsTo
    {
        return $this->belongsTo(PengajuanHarga::class, 'pengajuan_harga_id');
    }

    public function kategoriBahan(): BelongsTo
    {
        return $this->belongsTo(KategoriBahan::class, 'kategori_bahan_id');
    }

    public function subBahan(): BelongsTo
    {
        return $this->belongsTo(SubBahan::class, 'sub_bahan_id');
    }

    // Display-only convenience. Use SQL DECIMAL arithmetic for financial totals.
    public function getTotalAttribute(): float
    {
        return round((float) $this->qty * (float) $this->harga, 2);
    }
}
