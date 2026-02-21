<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionTimeline extends Model
{
    use HasFactory;

    protected $table = 'production_timeline';

    /**
     * Kolom yang boleh diisi (mass assignment)
     */
    protected $fillable = [
        'po_id',
        'spk_id',
        'detail_po_id',
        'qty',
        'data',
        'date',
        'type',
        'remark',
        'is_service',
    ];
    // data array null

    protected $casts = [
        'data' => 'array',
    ];


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



}
