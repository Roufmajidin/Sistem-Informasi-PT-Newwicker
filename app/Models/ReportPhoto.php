<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportPhoto extends Model
{

    protected $table = 'report_photo';

    protected $fillable = [
        'qc_report_id',
        'keterangan',
        'inspect_schedule_id',

        'path',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function qcReport()
    {
        return $this->belongsTo(QcReport::class, 'qc_report_id');
    }
    public function inspectSchedule()
    {
        return $this->belongsTo(
            InspectSchedule::class,
            'inspect_schedule_id'
        );
    }
}
