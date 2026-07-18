<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomGroupSubPrice extends Model
{
    protected $table = 'bom_group_sub_prices';

    protected $fillable = [
        'group_id',
        'name',
        'price',
    ];

    public function group()
    {
        return $this->belongsTo(BomGroup::class, 'group_id');
    }
}
