<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incoming extends Model
{
    protected $table = 'incoming';

    protected $fillable = [
        'po_id',
        'detail_po_id',
        'spk_id',
        'jumlah_incoming',
        'passed',
        'rejected',
        'remark',
        'tanggal_incoming',
        'user_id',
    ];

    protected $casts = [
        'tanggal_incoming' => 'datetime',
    ];

    public function po()
    {
        return $this->belongsTo(Po::class);
    }

    public function detailPo()
    {
        return $this->belongsTo(DetailPo::class);
    }

    public function spk()
    {
        return $this->belongsTo(Spk::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}