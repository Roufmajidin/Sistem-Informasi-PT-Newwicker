<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanHargaPekerjaan extends Model
{
    protected $table = 'pengajuan_harga_pekerjaans';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'po_id',
        'detail_po_id',
        'po_code',
        'article_code',
        'bahan_kategori_id',
        'closed',
        'sub_name',
        'price_history',

        // Remark umum
        'remark',

        // Harga PPIC
        'ppic_price',
        'ppic_remark',

        // Harga Director
        'director_price',

        // Harga pengajuan Subkontraktor
        'pengajuan_sub_price',
        'sub_remark',

        // Harga keputusan Director untuk Sub
        'director_sub_price',
        'director_remark',

        // User yang membuat
        'created_by',
    ];

    protected $casts = [
        'price_history' => 'array',
        'closed' => 'boolean',
        'id' => 'integer',
        'po_id' => 'integer',
        'detail_po_id' => 'integer',
        'bahan_kategori_id' => 'integer',
        'created_by' => 'integer',
        'ppic_date' => 'datetime',
        'directur_date' => 'datetime',
        'sub_date' => 'datetime',
        'ppic_price' => 'decimal:2',
        'director_price' => 'decimal:2',
        'pengajuan_sub_price' => 'decimal:2',
        'director_sub_price' => 'decimal:2',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke tabel PO
     */
    public function po(): BelongsTo
    {
        return $this->belongsTo(
            Po::class,
            'po_id',
            'id'
        );
    }

    /**
     * Relasi ke detail item PO
     */
    public function detailPo(): BelongsTo
    {
        return $this->belongsTo(
            DetailPo::class,
            'detail_po_id',
            'id'
        );
    }

    /**
     * Relasi ke master kategori pekerjaan
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(
            BahanKategori::class,
            'bahan_kategori_id',
            'id'
        );
    }

    /**
     * Relasi ke user pembuat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
            'id'
        );
    }
}
