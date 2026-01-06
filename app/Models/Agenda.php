<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    //

    protected $table = 'agendas';

    protected $fillable = [
        'id',
        'jenis_agenda',
        'dibuat_oleh',
        'tanggal',
        'status',
        'catatan',
        'remark_rouf',
        'kode_agenda',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
