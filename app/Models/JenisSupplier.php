<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSupplier extends Model
{
    //
     protected $fillable = [
        'name',
        'updated_by'
    ];

    protected $casts = [
        'updated_by' => 'array'
    ];
}
