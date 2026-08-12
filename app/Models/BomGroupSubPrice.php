<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomGroupSubPrice extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'price',
        'sort',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function group()
    {
        return $this->belongsTo(BomProdGroup::class, 'group_id');
    }
}
