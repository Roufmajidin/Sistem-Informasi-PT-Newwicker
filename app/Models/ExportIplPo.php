<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportIplPo extends Model
{
    protected $fillable = [
        'export_ipl_id',

        'po_id',

        'po_no',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function exportIpl()
    {
        return $this->belongsTo(ExportIpl::class);
    }

    public function po()
    {
        return $this->belongsTo(Po::class);
    }
}
