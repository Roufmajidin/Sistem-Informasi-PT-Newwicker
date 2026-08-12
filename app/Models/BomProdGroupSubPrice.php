<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomProdGroupSubPrice extends Model
{
    protected $table = 'bom_group_sub_prices';

    protected $guarded = [];
    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function group()
    {
        return $this->belongsTo(BomGroupProd::class, 'group_id');
    }
}
