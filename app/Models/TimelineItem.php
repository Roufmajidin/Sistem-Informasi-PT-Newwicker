<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimelineItem extends Model
{
    use HasFactory;

    protected $table = 'timeline_items';

    /**
     * Kolom yang boleh diisi (mass assignment)
     */
    protected $fillable = [
        'user_id',
        'action',        // in | out | service | back | edit | delete
        'ref_table',
        'ref_id',
        'detail_po_id',
        'po_id',
        'spk_id',
        'sup',
        'qty',
        'payload',
    ];

    /**
     * Cast tipe data
     */
    protected $casts = [
        'qty'     => 'integer',
        'payload' => 'array',
    ];

    /* ===================== RELATIONS ===================== */

    /**
     * Admin / User yang melakukan aksi
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Detail PO
     */
    public function detailPo()
    {
        return $this->belongsTo(DetailPo::class, 'detail_po_id');
    }

    /**
     * Relasi ke PO
     */
    public function po()
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

    /**
     * Relasi ke SPK
     */
    public function spk()
    {
        return $this->belongsTo(Spk::class, 'spk_id');
    }

    /* ===================== SCOPES ===================== */

    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeSub($query, $sub)
    {
        return $query->where('sup', $sub);
    }

    public function scopeByDetailPo($query, $detailPoId)
    {
        return $query->where('detail_po_id', $detailPoId);
    }

    /* ===================== HELPERS ===================== */

    /**
     * Helper cepat untuk logging
     */
    public static function log(array $data): self
    {
        return self::create([
            'user_id'       => auth()->id(),
            'action'        => $data['action'],
            'ref_table'     => $data['ref_table'] ?? null,
            'ref_id'        => $data['ref_id'] ?? null,
            'detail_po_id'  => $data['detail_po_id'] ?? null,
            'po_id'         => $data['po_id'] ?? null,
            'spk_id'        => $data['spk_id'] ?? null,
            'sup'           => $data['sup'] ?? null,
            'qty'           => $data['qty'] ?? 0,
            'payload'       => $data['payload'] ?? null,
        ]);
    }
}
