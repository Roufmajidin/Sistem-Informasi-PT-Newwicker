<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomItem extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'qty',
        'unit',
        'notes',
        'parent_id',
        'level',
        'material_id',
        'material_type',
        'harga'
    ];

    public function group()
    {
        return $this->belongsTo(BomGroup::class, 'group_id');
    }

    public function parent()
    {
        return $this->belongsTo(BomItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BomItem::class, 'parent_id');
    }
}
