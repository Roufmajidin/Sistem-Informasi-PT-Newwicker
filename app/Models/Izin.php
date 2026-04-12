<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    protected $fillable = [
        'user_id',
        'type_id',
        'mulai_tanggal',
        'sampai_tanggal',
        'alasan',
        'tanggal',
        'file',
        'status',
    ];

    public function type()
    {
        return $this->belongsTo(IzinType::class, 'type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
