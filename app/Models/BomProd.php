<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomProd extends Model
{
    protected $table = 'bom_prod';

    protected $guarded = [];
    public function groups()
    {
        return $this->hasMany(BomProdGroup::class, 'bom_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function summaries()
    {
        return $this->hasMany(BomProdSummary::class, 'bom_id');
    }
    public function timbangan()
    {
        return $this->hasMany(BomTimbangan::class, 'bom_id')->where('bom_type', 'production')->orderBy('sort_order')->orderBy('id');
    }
}
