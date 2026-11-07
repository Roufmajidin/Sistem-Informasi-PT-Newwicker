<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBahan extends Model
{
    protected $table = 'kategori_bahan';

    protected $fillable = [
        'pengajuan_harga_id', 'kategori_id', 'sub_bahan_id',
        'nama_bahan', 'qty', 'unit', 'harga', 'pic_user_id', 'remark',
    ];

    // Approval fields are intentionally not mass assignable.
    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'harga' => 'decimal:2',
            'approved_total' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function pengajuanHarga(): BelongsTo
    {
        return $this->belongsTo(PengajuanHarga::class, 'pengajuan_harga_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(BahanKategori::class, 'kategori_id');
    }

    public function subBahan(): BelongsTo
    {
        return $this->belongsTo(SubBahan::class, 'sub_bahan_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PengajuanHargaDetail::class, 'kategori_bahan_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
