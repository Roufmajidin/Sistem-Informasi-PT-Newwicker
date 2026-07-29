<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportIplItem extends Model
{
    protected $fillable = [

        'export_ipl_id',

        'po_id',

        'detail_po_id',

        'po_no',

        'hs_code',

        'article_nr',

        'description',

        'photo',

        'box_dimension',

        'qty_pcs',

        'qty_box',

        'cbm',

        'total_cbm',

        'unit_price',

        'total_price',

        'net_weight',

        'gross_weight',

        'remark',
    ];

    protected $casts = [

        'qty_pcs' => 'integer',

        'qty_box' => 'integer',

        'cbm' => 'decimal:3',

        'total_cbm' => 'decimal:3',

        'unit_price' => 'decimal:2',

        'total_price' => 'decimal:2',

        'net_weight' => 'decimal:2',

        'gross_weight' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function exportIpl()
    {
        return $this->belongsTo(ExportIpl::class);
    }

    public function po()
    {
        return $this->belongsTo(Po::class);
    }

    public function detailPo()
    {
        return $this->belongsTo(DetailPo::class);
    }
}
