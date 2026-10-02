<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loberon extends Model
{
    use HasFactory;

    protected $table = 'loberons';

    protected $fillable = [
        'buyer',
        'article_code',
        'color_id',
        'size_id',
        'ean_code',
        'article_description',
        'hts_code',
        'bulky_goods_class',
        'net_wt_crt',
        'gross_wt_crt',
        'total_number_cartons',
        'carton_l',
        'carton_w',
        'carton_h',
        'volume_carton',
        'qty',
        'price_per_article',
    ];

    protected $casts = [
        'net_wt_crt' => 'decimal:4',
        'gross_wt_crt' => 'decimal:4',
        'total_number_cartons' => 'decimal:4',

        'carton_l' => 'decimal:4',
        'carton_w' => 'decimal:4',
        'carton_h' => 'decimal:4',

        'volume_carton' => 'decimal:6',

        'qty' => 'decimal:4',
        'price_per_article' => 'decimal:4',
    ];
}