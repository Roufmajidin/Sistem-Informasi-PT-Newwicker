<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IzinType extends Model
{
    protected $fillable = [
        'name',
        'code'
    ];

    public function izins()
    {
        return $this->hasMany(Izin::class, 'type_id');
    }
}
