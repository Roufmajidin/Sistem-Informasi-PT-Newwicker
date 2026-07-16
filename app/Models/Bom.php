<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    protected $table = 'bom';

    protected $fillable = [
        'name',
        'article_number',
        'order_no',
        'buyer',
            'panjang',
    'lebar',
    'tinggi',

    'carton_panjang',
    'carton_lebar',
    'carton_tinggi',

    'loadability_pcs',
    'loadability_cbm',

    'image',
    'released',
    'released_date'
    ];

    public function groups()
    {
        return $this->hasMany(BomGroup::class, 'bom_id');
    }
    public function summaries()
{
    return $this->hasMany(BomSummary::class);
}
}
