<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    //
    protected $fillable = ['product_id', 'created_by', 'image', 'file_name'];

    // Relasi Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
