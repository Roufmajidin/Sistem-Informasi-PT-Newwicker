<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLoan extends Model
{
    protected $fillable = [

        'nama_karyawan',
        'jabatan',
        'divisi_id',
        'nominal_pengajuan',
        'alasan_pengajuan',
        'cara_pengembalian',
        'nominal_potongan_gaji',
        'periode_pembayaran',
        'pelunasan_terakhir',
        'status',
        'approver',
        'user_id',
    ];

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    // user pengaju
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // user approver
    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver');
    }
}
