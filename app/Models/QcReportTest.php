<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcReportTest extends Model
{
    protected $table = 'qc_report_test';

    protected $fillable = [
        'check_point_id',
        'remark',
        'po_id',
        'size',
        'inspect_schedule_id',
        'detail_po_id',
    ];

    public function inspectSchedule()
    {
        return $this->belongsTo(
            InspectScheduleTest::class,
            'inspect_schedule_id'
        );
    }

    public function photos()
    {
        return $this->hasMany(
            ReportPhotoTest::class,
            'qc_report_id'
        );
    }

    public function checkpoint()
    {
        return $this->belongsTo(
            Checkpoint::class,
            'check_point_id'
        );
    }

    public function po()
    {
        return $this->belongsTo(
            Po::class,
            'po_id'
        );
    }

    public function detailPo()
    {
        return $this->belongsTo(
            DetailPo::class,
            'detail_po_id'
        );
    }
}