<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportPhotoTest extends Model
{
    protected $table = 'report_photo_test';

    protected $fillable = [
        'qc_report_id',
        'keterangan',
        'inspect_schedule_id',
        'path',
    ];

    public function qcReport()
    {
        return $this->belongsTo(
            QcReportTest::class,
            'qc_report_id'
        );
    }

    public function inspectSchedule()
    {
        return $this->belongsTo(
            InspectScheduleTest::class,
            'inspect_schedule_id'
        );
    }
}