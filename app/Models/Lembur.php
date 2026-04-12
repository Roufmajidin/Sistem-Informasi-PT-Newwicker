<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'latitude',
        'longitude',
        'latitude_k',
        'longitude_k',
        'foto',
        'foto_keluar',
        'keterangan',
        'validate'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
