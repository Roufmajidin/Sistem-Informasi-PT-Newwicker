<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalMessage extends Model
{
    protected $table = 'approval_messages';
    protected $fillable = [
        'pengajuan_id',
        'user_id',
        'approval_step_id',
        'message',
    ];

    // 🔗 relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔗 relasi ke pengajuan
    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }

    // 🔗 relasi ke step approval
    public function step()
    {
        return $this->belongsTo(PengajuanApprovalStep::class, 'approval_step_id');
    }
}
