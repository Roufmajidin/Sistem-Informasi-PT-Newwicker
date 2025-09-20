<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPameran extends Model
{
    protected $table    = 'product_pameranss'; // sesuai migration
    protected $fillable = [
        'exhibition_id', 'nr', 'photo', 'article_code', 'name', 'categories', 'sub_categories', 'remark',
        'item_w', 'item_d', 'item_h', 'packing_w', 'packing_d', 'packing_h',
        'set2', 'set3', 'set4', 'set5', 'composition', 'finishing', 'qty', 'cbm',
        'loadability_20', 'loadability_40', 'loadability_40hc',
        'rangka', 'anyam', 'finishing_powder_coating', 'accessories_final', 'electricity', 'packingbox',
        'glass', 'cushion', 'total',
        'fob_cost_20', 'fob_cost_40', 'fob_cost_40hc',
        'total_production_cost_20', 'total_production_cost_40', 'total_production_cost_40hc',
        'cogusd_rate_14500', 'price_cushion', 'price_item', 'fob_jakarta_in_usd','value_in_usd',
    ];

}
