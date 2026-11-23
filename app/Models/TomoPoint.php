<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TomoPoint extends Model
{
    protected $table = 'tomo_points';
    protected $fillable = ['name', 'sort_order', 'is_active', 'harga_mutlak', 'satuan'];
    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean', 'harga_mutlak' => 'decimal:2'];
}
