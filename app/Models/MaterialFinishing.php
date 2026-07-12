<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialFinishing extends Model
{
    protected $fillable = [
        'nama',
        'jenis_propan',
        'jenis_diva',
        'jenis_warna_prima',
        'jenis_legenda'
    ];
}
