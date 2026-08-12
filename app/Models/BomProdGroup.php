<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class BomProdGroup extends Model
{
    protected $table = 'bom_groups_prod';

    protected $guarded = [];

    public function bom()
    {
        return $this->belongsTo(BomProd::class, 'bom_id');
    }

    public function items()
    {
        return $this->hasMany(BomProdItem::class, 'group_id');
    }

    public function subPrices()
    {
        return $this->hasMany(BomProdGroupSubPrice::class, 'group_id');
    }
}
