<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryComment extends Model
{
    protected $fillable = [
        'inventory_id',
        'user_id',
        'message'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
