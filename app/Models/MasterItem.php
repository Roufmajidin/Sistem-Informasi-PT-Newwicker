<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterItem extends Model
{
    protected $table = 'master_items';

    protected $fillable = [
        'article_code',
        'article_nr',

        'description',
        'sub_category',
        'composition',
        'finishing',

        'item_d',
        'item_h',
        'item_w',

        'pack_d',
        'pack_h',
        'pack_w',

        'cbm',
        'total_cbm',

        'value_in_usd',
        'fob_jakarta_in_usd',

        'photo',
        'remark',

        'source_detail_po_id',
    ];

    protected $casts = [
        'cbm' => 'decimal:5',
        'total_cbm' => 'decimal:5',

        'value_in_usd' => 'decimal:2',
        'fob_jakarta_in_usd' => 'decimal:2',
    ];
}