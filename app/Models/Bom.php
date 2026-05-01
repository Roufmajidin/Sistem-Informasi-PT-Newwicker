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
        'buyer'
    ];

    public function groups()
    {
        return $this->hasMany(BomGroup::class, 'bom_id');
    }
}
