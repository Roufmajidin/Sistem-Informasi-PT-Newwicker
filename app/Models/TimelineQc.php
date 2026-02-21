<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelineQc extends Model
{
    protected $fillable = [
        'po_id',
        'detail_po_id',
        'kategori_id',
        'inspect_schedule_id',
        'user_id',
        'qty',
        'tanggal',
        'is_lanjutan',
    ];

    /* ===============================
       RELATION
    =============================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function schedule()
    {
        return $this->belongsTo(InspectSchedule::class, 'inspect_schedule_id');
    }
    public function detailPo()
{
    return $this->belongsTo(DetailPo::class, 'detail_po_id');
}
}
