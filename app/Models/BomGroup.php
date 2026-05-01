<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomGroup extends Model
{
    protected $fillable = [
        'bom_id',
        'name'
    ];

    public function bom()
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function items()
    {
        return $this->hasMany(BomItem::class, 'group_id');
    }
}
