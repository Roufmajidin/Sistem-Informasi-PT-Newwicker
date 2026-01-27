<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class QcReport extends Model
{

    protected $table = 'qc_report';

    protected $fillable = [
        'check_point_id',
        'remark',
        'po_id',
        'size',
        'detail_po_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function photos()
    {
        return $this->hasMany(ReportPhoto::class, 'qc_report_id');
    }

    // optional – kalau nanti ada tabel checkpoint
    public function checkpoint()
    {
        return $this->belongsTo(Checkpoint::class, 'check_point_id');
    }

    // optional
    public function po()
    {
        return $this->belongsTo(Po::class, 'po_id');
    }

    public function detailPo()
    {
        return $this->belongsTo(DetailPo::class, 'detail_po_id');
    }
}
