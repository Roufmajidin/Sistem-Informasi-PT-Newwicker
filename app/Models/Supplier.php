<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    //
     protected $fillable = [
        'name',
        'alamat',
        'jenis_supplier_id',
        'updated_by'
    ];

    protected $casts = [
        'updated_by' => 'array'
    ];

    public function jenis()
    {
        return $this->belongsTo(JenisSupplier::class, 'jenis_supplier_id');
    }
}
