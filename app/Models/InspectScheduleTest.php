<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectScheduleTest extends Model
{
    protected $table = 'inspect_schedule_test';

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
        'is_service',
        'nw_service',
        'is_reinspect',
    ];

    protected $casts = [
        'is_service' => 'boolean',
        'nw_service' => 'boolean',
        'is_reinspect' => 'boolean',
        'tanggal_inspect' => 'date',
    ];

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
        return $this->belongsTo(Spk::class, 'spk_id');
    }

    public function qcReports()
    {
        return $this->hasMany(
            QcReportTest::class,
            'inspect_schedule_id'
        );
    }

    public function reportPhotos()
    {
        return $this->hasMany(
            ReportPhotoTest::class,
            'inspect_schedule_id'
        );
    }
}