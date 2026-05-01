<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanApprovalStep extends Model
{
    protected $fillable = [
        'pengajuan_id',
        'step_order',
        'step_name',
        'user_name',
        'status',
        'approved_at',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
    public function messages()
    {
        return $this->hasMany(ApprovalMessage::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
