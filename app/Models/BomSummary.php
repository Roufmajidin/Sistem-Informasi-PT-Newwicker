<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomSummary extends Model
{
    protected $fillable = [

        'bom_id',
        'name',
        'remark',
        'qty',
        'price',
        'total',

    ];

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }
}
