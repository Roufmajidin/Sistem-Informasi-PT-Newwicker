<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $fillable = [
        'type_pengajuan',
        'file',
        'user_id',
        'status',
        'keterangan',
        'approved_date',
        'remark',
        'divisi_id',
    ];

    public function meta()
    {
        return $this->hasOne(PengajuanMeta::class);
    }

    public function details()
    {
        return $this->hasMany(PengajuanDetail::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvals()
    {
        return $this->hasMany(PengajuanApprovalStep::class);
    }
    public function approvalSteps()
    {
        return $this->hasMany(PengajuanApprovalStep::class)
            ->orderBy('step_order');
    }
    public function files()
    {
    return $this->hasMany(PengajuanFile::class, 'pengajuan_id');
    }
}
