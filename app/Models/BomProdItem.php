<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomProdItem extends Model
{
    protected $table = 'bom_items_prod';

    protected $guarded = [];

    public function group()
    {
        return $this->belongsTo(BomProdGroup::class, 'group_id');
    }

    public function parent()
    {
        return $this->belongsTo(BomProdItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BomProdItem::class, 'parent_id');
    }
}
