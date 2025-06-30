<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'keterangan',
        'latitude',
        'longitude',
        'latitude_k',
        'longitude_k',
        'foto',
        'foto_keluar',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
