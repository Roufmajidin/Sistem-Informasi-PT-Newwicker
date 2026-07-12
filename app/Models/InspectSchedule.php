<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectSchedule extends Model
{

    protected $table = 'inspect_schedule';

    protected $fillable = [
        'po_id',
        'detail_po_id',
        'batch',
        'jumlah_inspect',
        'tanggal_inspect',
        'user_id',
        'kategori_id',
        'rejected',
        'passed',
        'spk_id',
    ];

    /* ===============================
       RELATIONS (OPSIONAL TAPI DISARANKAN)
    =============================== */

    public function po()
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

    public function detailPo()
    {
        return $this->belongsTo(DetailPo::class, 'detail_po_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
    public function spk()
    {
        return $this->belongsTo(Spk::class);
    }
    public function qcReports()
    {
        return $this->hasMany(
            QcReport::class,
            'inspect_schedule_id'
        );
    }

    public function reportPhotos()
    {
        return $this->hasMany(
            ReportPhoto::class,
            'inspect_schedule_id'
        );
    }
}
